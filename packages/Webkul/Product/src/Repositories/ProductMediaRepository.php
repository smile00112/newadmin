<?php

namespace Webkul\Product\Repositories;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;

class ProductMediaRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model()
    {
        /**
         * This repository is extended to `ProductImageRepository` and `ProductVideoRepository`
         * repository.
         *
         * And currently no model is assigned to this repo.
         */
    }

    /**
     * Get product directory.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function getProductDirectory($product): string
    {
        return 'product/'.$product->id;
    }

    /**
     * Upload.
     *
     * @param  array  $data
     * @param  \Webkul\Product\Contracts\Product  $product
     */
    public function upload($data, $product, string $uploadFileType): void
    {
        \Log::info('ProductMediaRepository upload:', [
            'uploadFileType' => $uploadFileType,
            'has_files_key' => isset($data[$uploadFileType]['files']),
            'data_keys' => isset($data[$uploadFileType]) ? array_keys($data[$uploadFileType]) : 'no uploadFileType key',
            'files_count' => isset($data[$uploadFileType]['files']) ? count($data[$uploadFileType]['files']) : 0,
        ]);
        
        /**
         * Previous model ids for filtering.
         */
        $previousIds = $this->resolveFileTypeQueryBuilder($product, $uploadFileType)->pluck('id');

        $position = 0;

        if (! empty($data[$uploadFileType]['files'])) {
            foreach ($data[$uploadFileType]['files'] as $indexOrModelId => $file) {
                // Skip empty values
                if (empty($file) && !($file instanceof UploadedFile)) {
                    // Just update position for existing file
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }

                    $this->update([
                        'position' => ++$position,
                    ], $indexOrModelId);
                    continue;
                }
                
                \Log::info('Processing file:', [
                    'indexOrModelId' => $indexOrModelId,
                    'file_type' => is_object($file) ? get_class($file) : gettype($file),
                    'is_uploaded_file' => $file instanceof UploadedFile,
                ]);
                
                if ($file instanceof UploadedFile) {
                    \Log::info('File is UploadedFile, mime: ' . $file->getMimeType());
                    
                    if (Str::contains($file->getMimeType(), 'image')) {
                        $manager = new ImageManager;

                        $image = $manager->make($file)->encode('webp');

                        $path = $this->getProductDirectory($product).'/'.Str::random(40).'.webp';

                        Storage::put($path, $image);
                        
                        \Log::info('Image saved to: ' . $path);
                    } else {
                        $path = $file->store($this->getProductDirectory($product));
                    }

                    $created = $this->create([
                        'type'       => $uploadFileType,
                        'path'       => $path,
                        'product_id' => $product->id,
                        'position'   => ++$position,
                    ]);
                } else {
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }

                    $this->update([
                        'position' => ++$position,
                    ], $indexOrModelId);
                }
            }
        }

        foreach ($previousIds as $indexOrModelId) {
            if (! $model = $this->find($indexOrModelId)) {
                continue;
            }

            Storage::delete($model->path);

            $this->delete($indexOrModelId);
        }
    }

    /**
     * Resolve file type query builder.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return mixed
     *
     * @throws \Exception
     */
    private function resolveFileTypeQueryBuilder($product, string $uploadFileType)
    {
        if ($uploadFileType === 'images') {
            return $product->images();
        } elseif ($uploadFileType === 'videos') {
            return $product->videos();
        }

        throw new Exception('Unsupported file type.');
    }
}
