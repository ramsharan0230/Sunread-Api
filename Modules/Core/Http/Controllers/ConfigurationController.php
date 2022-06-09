<?php

namespace Modules\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Entities\Configuration;
use Modules\Core\Jobs\ConfigurationCache;
use Modules\Core\Repositories\ConfigurationRepository;
use Modules\Core\Services\ConfigurationHelper;
use Modules\Core\Traits\Configuration as TraitsConfiguration;

class ConfigurationController extends BaseController
{
    protected $repository, $helper;
    use TraitsConfiguration;

    public function __construct(Configuration $configuration, ConfigurationRepository $configurationRepository, ConfigurationHelper $helper)
    {
        $this->model = $configuration;
        $this->model_name = "Configuration";
        $this->repository = $configurationRepository;
        $this->helper = $helper;
        $this->createModel();

        parent::__construct($this->model, $this->model_name);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getConfigData($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }
        

        return $this->successResponse($fetched, $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $rules = isset($request->absolute_path) ? $this->repository->getValidationRules($request) : ["items"=>[]];
            $scope_rules = $this->repository->scopeValidation($request);
            $rules = array_merge($scope_rules, $rules, ["absolute_path" => "required"]);
            $data = $this->repository->validateData($request, $rules);

            if(!$request->scope) $data["scope"] = "global";
            if(!$request->scope_id) $data["scope_id"] = 0;

            $created_data = $this->repository->add((object) $data);
            if(isset($created_data->data)) ConfigurationCache::dispatch($created_data->data)->onQueue("high");
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($created_data->data ?? [], $this->lang($created_data->message), $created_data->code);
    }

    public function getElementValue(Request $request)
    {
        try
        {
            $this->repository->validateData($request, $this->repository->scopeValidation($request));

            if(!$request->scope) $request->scope = "global";
            if(!$request->scope_id) $request->scope_id = 0;

            $fetched = [
                "value" => $this->repository->getSinglePathValue($request)
            ];
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
