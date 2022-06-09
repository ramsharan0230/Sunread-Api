<?php

namespace Modules\Erp\Jobs\Mapper;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\HasErpValueMapper;
use Modules\Erp\Traits\HasStorageMapper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;
use Modules\Product\Repositories\ProductImageRepository;


class ImageMigrate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HasStorageMapper;
    use HasErpValueMapper;

    protected object $product;
    protected object $images;
    public $repository;

    public function __construct(object $product, object $images)
    {
        $this->product = $product;
        $this->images = $images;
        $this->repository = new ProductImageRepository();
    }

    public function handle(): void
    {
        $this->storeStorageImages($this->product, $this->images);
    }

    public function storeStorageImages(object $product, object $images): void
    {
        if ($images->count() > 0) {
            // $this->deletePreviousImage($product);
            if ($product->type == Product::CONFIGURABLE_PRODUCT) {
                $configurable_images = [];
                foreach ($images->groupBy("color_code") as $color_images) {
                    $configurable_images[] = $color_images->first();
                }
                $images = $configurable_images;
            } else {
                $images = $images->where("color_code", $this->getAttributeColorValue($product)->code);
            }
            $images = is_array($images) ? $images : $images->toArray();
            $this->updateOrStoreImages($product, $images);
        }
    }

    public function updateOrStoreImages(object $product, array $images): void
    {
        $position = 0;
        if ($product->images()->get()->count() > 0 ) {
            $product->images()->delete();
        }
        foreach ($images as $image) {
            if (!Storage::exists($image["url"])) {
                continue;
            }
            // $generate_folder_name = Str::random(6);
            $source_path = $image["url"];
            // $destination_path = "images/product/{$generate_folder_name}/{$product->sku}/{$image['image']}";

            // $this->moveStorageImage($source_path, $destination_path);

            $data["path"] = $source_path;
            $data["position"] = $position;
            $data["product_id"] = $product->id;
            if ($position == 0) {
                $type_ids = [1,2,3];
            } else {
                $type_ids = 5;
            }

            if ($position == 0 || $position == 1) {
                $data["background_size"] = "contain";
            } else {
                $data["background_size"] = "cover";
            }

            $position++;
            $match = $data;
            unset($match["position"]);
            $product_image = $this->repository->createOrUpdate($match, $data);
            $product_image->types()->detach($product_image);
            $product_image->types()->sync($type_ids);
        }
    }

    public function deletePreviousImage(object $product): void
    {
        if ($product->images->count() > 0) {
            foreach ($product->images as $image) {
                if ($image->path && Storage::exists($image->path)) {
                    Storage::delete($image->path);
                }
            }
        }
    }

    public function deleteSourceImages(object $images): void
    {
        foreach ($images as $image){
            Storage::delete($image["url"]);
        }
    }

    public function moveStorageImage(string $source_path, string $destination_path): void
    {
        Storage::copy($source_path, $destination_path);
    }

    public function getAttributeColorValue(object $product): mixed
    {
        return $product->value([
            "scope" => "website",
            "scope_id" => $product->website->id,
            "attribute_slug" => "color"
        ]);
    }
}
