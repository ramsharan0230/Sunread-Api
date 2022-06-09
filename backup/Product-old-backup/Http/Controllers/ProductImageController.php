<?php

namespace Modules\Product\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Exceptions\ProductImageDeleteException;
use Modules\Product\Exceptions\ProductImageTypeNotFound;
use Modules\Product\Services\ProductImageRepository;

class ProductImageController extends BaseController
{

    protected $pagination_limit, $productImage;

    public function __construct(ProductImageRepository $productImage)
    {
        parent::__construct();
        $this->middleware('admin');
        $this->productImage = $productImage;
    }

    public function remove($productImageId)
    {
        try {
            $productImage = ProductImage::findOrFail($productImageId);
            $isProductImageRemoved = $this->productImage->removeParticularProductImage($productImage);
            if (!$isProductImageRemoved) {
                throw new ProductImageDeleteException();
            }
            return $this->successResponseWithMessage(trans('core::app.response.delete-success', ['name' => 'Product Image']));
        } catch (ProductImageDeleteException $exception) {
            return $this->errorResponse("Image Could not be deleted", 500);

        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function upload(Request $request)
    {
        try {

            $this->validate($request, [
                'product_id' => 'required|exists:products,id',
                'images.*' => 'required|mimes:jpeg,jpg,bmp,png',
            ]);
            $product = Product::findOrFail($request->get('product_id'));
            $this->productImage->uploadProductImages($product);
            return $this->successResponseWithMessage("Image uploaded successfully");

        } catch (ValidationException $exception) {
            dd($exception);
            return $this->errorResponse($exception->getMessage(), 422);
        } catch (ProductImageDeleteException $exception) {
            return $this->errorResponse("Image Could not be deleted", 500);

        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

    }

    public function changeImageType(Request $request, $productImageId)
    {
        try {
            //check type validation
            $productImage = ProductImage::findOrFail($productImageId);
            $types = $request->get('types');

            //image type validation
            foreach ($types as $type => $item){
                if (!in_array($type, ['main_image', 'small_image','thumbnail', 'normal_image'])) {
                    throw new ProductImageTypeNotFound();
                }
            }

            //Change image type
            foreach ($types as $type => $value){
                $product = $productImage->product;
                if($value){
                    ProductImage::where('product_id', $product->id)->where('id', '!=' ,$productImage->id)->update([$type => 0]);
                    $productImage->update([$type => $value]);
                }else{
                    $otherImage = ProductImage::where('product_id' ,$product->id)->where($type,1)->first();
                    if(!$otherImage){
                        $firstProductImage = ProductImage::where('product_id', $product->id)->first();
                        $firstProductImage->update([$type => 1]);
                    }
                    $productImage->update([$type => $value]);
                }

            }

            return $this->successResponse("Image Updated success");

        } catch (ValidationException $exception) {
            return $this->errorResponse($exception->getMessage(), 422);

        } catch (ProductImageTypeNotFound $exception) {

            return $this->errorResponse($exception->getMessage(), 422);

        } catch (\Exception $exception) {
            dd($exception);
            return $this->errorResponse($exception->getMessage());
        }
    }

}

