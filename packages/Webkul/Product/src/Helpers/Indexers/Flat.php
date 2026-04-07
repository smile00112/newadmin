<?php

namespace Webkul\Product\Helpers\Indexers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Webkul\Product\Helpers\ProductType;
use Webkul\Product\Repositories\ProductFlatRepository;
use Webkul\Product\Repositories\ProductRepository;

class Flat extends AbstractIndexer
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * Attribute codes that can be fill during flat creation.
     *
     * @var string[]
     */
    protected $fillableAttributeCodes = [
        'sku',
        'name',
        'price',
        'weight',
        'status',
    ];

    /**
     * @var array
     */
    protected $flatColumns = [];

    /**
     * Channels
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Family Attributes
     *
     * @var array
     */
    protected $familyAttributes = [];

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductFlatRepository $productFlatRepository
    ) {
        $this->batchSize = self::BATCH_SIZE;

        // Кэшируем schema на 1 час — колонки таблицы не меняются в рантайме
        $this->flatColumns = Cache::remember('product_flat_columns', 3600, function () {
            return Schema::getColumnListing('product_flat');
        });
    }

    /**
     * Reindex all products
     *
     * @return void
     */
    public function reindexFull()
    {
        while (true) {
            $paginator = $this->productRepository
                ->with([
                    'variants',
                    'attribute_family',
                    'attribute_values',
                    'variants.attribute_family',
                    'variants.attribute_values',
                ])
                ->cursorPaginate($this->batchSize);

            $this->reindexBatch($paginator->items());

            if (! $cursor = $paginator->nextCursor()) {
                break;
            }

            request()->query->add(['cursor' => $cursor->encode()]);
        }

        request()->query->remove('cursor');
    }

    /**
     * Reindex products by batch size
     *
     * @return void
     */
    public function reindexBatch($products)
    {
        foreach ($products as $product) {
            $this->refresh($product);
        }
    }

    /**
     * Refresh product flat indices
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function refresh($product)
    {
        // Загружаем с нужными связями за 1 запрос вместо множества ленивых загрузок
        $product = $this->productRepository
            ->with(['attribute_family', 'attribute_values', 'channels'])
            ->find($product->id);

        if (! $product) {
            return;
        }

        $this->updateOrCreate($product);

        if (! ProductType::hasVariants($product->type)) {
            return;
        }

        $variants = $product->variants()
            ->with(['attribute_family', 'attribute_values', 'channels'])
            ->get();

        foreach ($variants as $variant) {
            $this->updateOrCreate($variant);
        }
    }

    /**
     * Creates product flat
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function updateOrCreate($product)
    {
        $familyAttributes = $this->getCachedFamilyAttributes($product);

        $channelIds = $product->channels->pluck('id')->toArray();

        if (empty($channelIds)) {
            $channelIds[] = core()->getDefaultChannel()->id;
        }

        // Используем предзагруженные attribute_values (уже eager loaded в refresh())
        $attributeValues = $product->relationLoaded('attribute_values')
            ? $product->attribute_values
            : $product->attribute_values()->get();

        $allChannels = $this->getCachedChannels();

        foreach ($allChannels as $channel) {
            if (in_array($channel->id, $channelIds)) {
                foreach ($channel->locales as $locale) {
                    $productFlat = $this->productFlatRepository->updateOrCreate([
                        'product_id'          => $product->id,
                        'channel'             => $channel->code,
                        'locale'              => $locale->code,
                    ], [
                        'type'                => $product->type,
                        'sku'                 => $product->sku,
                        'attribute_family_id' => $product->attribute_family_id,
                    ]);

                    $fallbackLocale = config('app.fallback_locale', 'en');
                    
                    foreach ($familyAttributes as $attribute) {
                        if (
                            ! in_array($attribute->code, $this->flatColumns)
                            || $attribute->code == 'sku'
                        ) {
                            continue;
                        }

                        $productAttributeValues = $attributeValues->where('attribute_id', $attribute->id);

                        if ($attribute->value_per_channel) {
                            if ($attribute->value_per_locale) {
                                $filteredValues = $productAttributeValues
                                    ->where('channel', $channel->code)
                                    ->where('locale', $locale->code);
                                    
                                // Fallback to default locale if no value found
                                if ($filteredValues->isEmpty() && $locale->code !== $fallbackLocale) {
                                    $filteredValues = $productAttributeValues
                                        ->where('channel', $channel->code)
                                        ->where('locale', $fallbackLocale);
                                }
                                $productAttributeValues = $filteredValues;
                            } else {
                                $productAttributeValues = $productAttributeValues->where('channel', $channel->code);
                            }
                        } else {
                            if ($attribute->value_per_locale) {
                                $filteredValues = $productAttributeValues->where('locale', $locale->code);
                                
                                // Fallback to default locale if no value found
                                if ($filteredValues->isEmpty() && $locale->code !== $fallbackLocale) {
                                    $filteredValues = $productAttributeValues->where('locale', $fallbackLocale);
                                }
                                $productAttributeValues = $filteredValues;
                            }
                        }

                        $productAttributeValue = $productAttributeValues->first();
                        
                        $newValue = $productAttributeValue[$attribute->column_name] ?? null;

                        $productFlat->{$attribute->code} = $newValue;
                    }

                    $productFlat->save();
                }
            } else {
                if (request()->route()?->getName() == 'admin.catalog.products.update') {
                    $this->productFlatRepository->deleteWhere([
                        'product_id' => $product->id,
                        'channel'    => $channel->code,
                    ]);
                }
            }
        }
    }

    /**
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return mixed
     */
    public function getCachedFamilyAttributes($product)
    {
        if (array_key_exists($product->attribute_family_id, $this->familyAttributes)) {
            return $this->familyAttributes[$product->attribute_family_id];
        }

        return $this->familyAttributes[$product->attribute_family_id] = $product->attribute_family->custom_attributes;
    }

    /**
     * Кэшируем каналы на время запроса.
     */
    protected function getCachedChannels()
    {
        if (empty($this->channels)) {
            $this->channels = core()->getAllChannels();
        }

        return $this->channels;
    }
}
