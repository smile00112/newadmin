<?php

namespace Webkul\Product\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Product\Helpers\Indexers\Flat as FlatIndexer;
use Webkul\Product\Repositories\ProductRepository;

class UpdateProductFlatIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  int  $productId
     * @return void
     */
    public function __construct(protected int $productId)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProductRepository $productRepository, FlatIndexer $flatIndexer): void
    {
        $product = $productRepository->find($this->productId);

        if (! $product) {
            return;
        }

        $flatIndexer->refresh($product);
    }
}
