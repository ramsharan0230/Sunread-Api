<?php

namespace Modules\Tax\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Exception;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Tax\Entities\TaxRule;
use Modules\Tax\Repositories\TaxRuleRepository;
use Modules\Tax\Transformers\TaxRuleResource;

class TaxRuleController extends BaseController
{
    protected $repository;

    public function __construct(TaxRule $taxRule, TaxRuleRepository $taxRuleRepository)
    {
        $this->model = $taxRule;
        $this->repository = $taxRuleRepository;
        $this->model_name = "Tax Rule";

        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return TaxRuleResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new TaxRuleResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, [ "customer_tax_groups", "product_tax_groups", "tax_rates" ]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $created = $this->repository->create($data, function($created) use($request) {
                $created->tax_rates()->sync($request->get("tax_rates"));
                $created->product_tax_groups()->sync($request->get("product_tax_groups"));
                $created->customer_tax_groups()->sync($request->get("customer_tax_groups"));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id, [ "customer_tax_groups", "product_tax_groups", "tax_rates" ]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), $this->lang('fetch-success'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request);
            $updated = $this->repository->update($data, $id, function($updated) use($request) {
                $updated->tax_rates()->sync($request->get("tax_rates"));
                $updated->product_tax_groups()->sync($request->get("product_tax_groups"));
                $updated->customer_tax_groups()->sync($request->get("customer_tax_groups"));
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try
        {
            $updated = $this->repository->updateStatus($request, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('status-updated'));
    }
}
