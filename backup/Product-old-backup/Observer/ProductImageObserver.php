<?php


namespace Modules\Product\Observer;

use Modules\Product\Entities\ProductImage;

class ProductImageObserver
{

    public function deleted(ProductImage $productImage)
    {
        $image_types =
            [
                'main_image' =>$productImage->main_image,
                'thumbnail' =>$productImage->thumbnail,
                'small_image' => $productImage->small_image
            ];

        foreach ($image_types as $image_type => $value){
            // delete normal image
            if(!$value){
                continue;
            }
            $product = $productImage->product;
            $otherImageExist = ProductImage::where('product_id' ,$product->id)->where($image_type,$value)->exists();

            //if other type of image doesnt exist assign type to first one
            if(!$otherImageExist){
                if($firstImage = ProductImage::where('product_id' ,$product->id)->first()){
                    $firstImage->{$image_type} = $value;
                    $productImage->unsetEventDispatcher();
                    $firstImage->save();
                }

            }
        }

    }



    public function created(ProductImage $productImage)
    {
        $product = $productImage->product;
        $productImagesCount = ProductImage::where('product_id',$product->id)->count();
        if($productImagesCount == 1) {
            $productImage->main_image = 1;
            $productImage->small_image = 1;
            $productImage->thumbnail = 1;
            $productImage->unsetEventDispatcher();
            $productImage->save();
        }
    }



}
