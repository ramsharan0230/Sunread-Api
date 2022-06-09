<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Product\Entities\ProductImage;
use Modules\Product\Repositories\ProductImageRepository;
use Modules\Product\Transformers\ProductImageResource;
use Exception;

class ProductImageController extends BaseController
{
    private $productImage;
    private $repository;
    public function __construct(ProductImage $productImage, ProductImageRepository $productImageRepository)
    {
        $this->model = $productImage;
        $this->model_name = "Product Image";
        $this->repository = $productImageRepository;
        parent::__construct($this->model, $this->model_name);
    }


    public function collection(object $data): ResourceCollection
    {
        return ProductImageResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ProductImageResource($data);
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $data = $this->repository->validateData($request);
            foreach($request->file("image") as $file){
                $image = $this->repository->createImage($file);
                $data = array_merge($data,$image);
                $created = $this->repository->create($data);
            }
        }
        catch ( Exception $exception )
        {
            return $this->handleException($exception);
        }
        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id, function ($deleted) {
                if ($deleted->path){
                    Storage::delete($deleted->path);
                    $this->repository->deleteThumbnail($deleted->path);
                }
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function bulkDelete(Request $request)
    {
        try
        {
            $this->repository->bulkDelete($request);
        }
        catch ( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function changeMainImage(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->changeMainImage($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('status-change-success'));
    }
}
