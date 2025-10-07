<?php

namespace Webkul\Product\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Product\Models\Product;

class RecalculateConstructorPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'constructor:recalculate-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate prices for all constructor products';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting constructor price recalculation...');

        $constructorProducts = Product::where('type', 'constructor')->get();

        $this->info("Found {$constructorProducts->count()} constructor products");

        foreach ($constructorProducts as $product) {
            $this->info("Processing product: {$product->name} (ID: {$product->id})");
            
            // Load the product with its constructor data
            $product->load('constructor.groups.products');
            
            // Get the product type instance
            $typeInstance = $product->getTypeInstance();
            
            // Calculate the price
            $calculatedPrice = $typeInstance->getMinimalPrice();
            
            $this->info("  Calculated price: {$calculatedPrice}");
            
            // Update the product price
            $product->update(['price' => $calculatedPrice]);
            
            $this->info("  Updated product price to: {$product->fresh()->price}");
        }

        $this->info('Constructor price recalculation completed!');

        return 0;
    }
}
