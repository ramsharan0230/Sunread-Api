<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\ProductFlat;
use Modules\Product\Repositories\ProductRepository;

class ProductController extends BaseController
{

    protected $model_name = 'Product';

    /**
     * ProductRepository object
     */
    protected  $productRepository;

    /**
     * Pagination limit for resource
     */
    protected $pagination_limit;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        parent::__construct();
        $this->middleware('admin');
        $this->productRepository = $productRepository;
    }

    /**
     * Returns all the products
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // validating data.
            $this->validate($request, [
                'limit' => 'sometimes|numeric',
                'page' => 'sometimes|numeric',
                'sort_by' => 'sometimes',
                'sort_order' => 'sometimes|in:asc,desc',
                'q' => 'sometimes|string|min:1'
            ]);
            $products = $this->productRepository->getAll();
            return $this->successResponse($products, trans('core::app.response.fetch-list-success', ['name' => 'Product']));

        } catch (QueryException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);

        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

    }

    /**
     * Get the particular product
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $locale = request()->get('locale')?: app()->getLocale();
            $product = $this->productRepository->findOrFail($id);
            $product_flats = $product->product_flats;
            $is_product_with_locale_present = $product_flats->contains('locale', $locale);
            if($is_product_with_locale_present)
                $product = $product_flats->where('locale',$this->locale)->first();
            else
                $product = $product_flats->first();
            return $this->successResponse($product, trans('core::app.response.fetch-success', ['name' => $this->model_name]));

        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);

        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

    }


    /**
     * store the particular resource
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {

        try {
            //validation
            $this->validate($request, [
                'type' => 'required|in:simple,configurable',
                'attribute_family_id' => 'required|exists:attribute_families,id',
                'sku' => ['required', 'unique:products,sku'],
                'slug' => ['required','unique:products,slug'],
            ]);

            //store product
            $product = $this->productRepository->store($request->all());

            return $this->successResponse($product, trans('core::app.response.create-success', ['name' => 'Product']), 201);

        } catch (ValidationException $exception) {
            return $this->errorResponse($exception->errors(), 422);

        } catch (QueryException $exception) {
            return $this->errorResponse($exception->getMessage(), 400);

        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }


    /**
     * Update the products and variats
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try{
            //fetch rules
            $rules = $this->productRepository->rules($id);

            //validate
            $this->validate($request,$rules);

            //update
            $product = $this->productRepository->update($request->all(),$id);

            return $this->successResponse($product,trans('core::app.response.update-success', ['name' => 'Product']));

        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), 404);

        } catch (ValidationException $exception) {
            return $this->errorResponse($exception->errors(), 422);

        }catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

    }

    /**
     * Delete a product item
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {

            $this->productRepository->delete($id);
            return $this->successResponseWithMessage("Product deleted success!!");

        } catch (ModelNotFoundException $exception){
            return $this->errorResponse($exception->getMessage(), 404);

        }catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

    }

}

