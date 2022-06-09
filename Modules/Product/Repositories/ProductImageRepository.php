<?php

namespace Modules\Product\Repositories;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\ProductImage;
use Modules\Core\Repositories\BaseRepository;

class ProductImageRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ProductImage();
        $this->model_key = "catalog.product.images";
        $this->small_image_dimensions = config('product_image.image_dimensions.product_small_image');
        $this->thumbnail_image_dimensions = config('product_image.image_dimensions.product_thumbnail_image');
        $this->rules = [
            "product_id" => "required|exists:products,id",
            "image.*" => "required|mimes:bmp,jpeg,jpg,png",
            "position" => "sometimes|numeric"
        ];
    }

    public function createImage($file): array
    {
        DB::beginTransaction();

        try
        {
            // Store File
            $key = Str::random(6);
            $file_name = $this->generateFileName($file);
            $data['path'] = $file->storeAs("images/products/{$key}", $file_name);


            // Store small_image and thumbnail variations
            foreach (["small_image_dimensions" => "small_image", "thumbnail_image_dimensions" => "thumbnail_image"] as $type => $folder) {
                foreach ($this->{$type} as $dimension) {
                    $width = $dimension["width"];
                    $height = $dimension["height"];
                    $path = "images/products/{$key}/{$folder}";
                    if(!Storage::has($path)) Storage::makeDirectory($path, 0777, true, true);

                    $image = Image::make($file)
                        ->fit($width, $height, function($constraint) {
                            $constraint->upsize();
                        })
                        ->encode('jpg', 80);
                    $data[$folder] = Storage::put("$path/{$file_name}", $image) ? 1 : 0;
                }
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }
        DB::commit();
        return $data;
    }


    public function deleteThumbnail($path): bool
    {
        DB::beginTransaction();

        try
        {
            $path_array = explode("/", $path);
            unset($path_array[count($path_array) - 1]);

            $delete_folder = implode("/", $path_array);
            $file = substr($path, strrpos($path, '/' )+1);
            foreach ([ "small_image","thumbnail"] as $subfolder) {
                $folder = $delete_folder.'/'.$subfolder.'/'.$file;
                Storage::disk("public")->delete($folder);
            }
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return true;
    }

    public function changeMainImage($id): bool
    {
        DB::beginTransaction();

        try
        {
            $currentImage = $this->model->findOrFail($id);
            $this->model->where('product_id', $currentImage->product_id)->update(['main_image' => 0]);
            $currentImage->update(['main_image' => !$currentImage->main_image]);
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return true;
    }
}
