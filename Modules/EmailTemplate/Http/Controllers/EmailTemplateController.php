<?php

namespace Modules\EmailTemplate\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\EmailTemplate\Entities\EmailTemplate;
use Modules\EmailTemplate\Exceptions\DeleteSystemDefinedException;
use Modules\EmailTemplate\Repositories\EmailTemplateRepository;
use Modules\EmailTemplate\Transformers\EmailTemplateResource;
use Exception;

class EmailTemplateController extends BaseController
{
    private $repository;

    public function __construct(EmailTemplate $emailTemplate, EmailTemplateRepository $emailTemplateRepository)
    {
        $this->model = $emailTemplate;
        $this->model_name = "Email Template";
        $this->repository = $emailTemplateRepository;
        $exception_statuses = [
            DeleteSystemDefinedException::class => 401,
        ];
        parent::__construct($this->model, $this->model_name, $exception_statuses);
    }

    public function collection(object $data): ResourceCollection
    {
        return EmailTemplateResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new EmailTemplateResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function store(Request $request): JsonResponse
    {
        try
        {
            $data = $this->repository->validateData($request, callback:function ($request) {
                $this->repository->templateGroupValidation($request);
                $this->repository->templateVariableValidation($request);
                return [];
            });
            $created = $this->repository->create($data);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($created), $this->lang('create-success'), 201);
    }

    public function show(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id);
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
            $data = $this->repository->validateData($request, callback:function ($request) {
                $this->repository->templateGroupValidation($request);
                $this->repository->templateVariableValidation($request);
                return [];
            });
            $updated = $this->repository->update($data, $id);
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($updated), $this->lang('update-success'));
    }

    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->repository->delete($id, function ($deleted){
                if( $deleted->is_system_defined == 1) throw new DeleteSystemDefinedException("Cannot Delete System Defined Template.");
            });
        }
        catch (Exception $exception)
        {
            return $this->handleException($exception);
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    /**
     * fetch email template groupwise
    */
    public function templateGroup(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->getConfigGroup($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-list-success', [ "name" => "Template Group" ]));
    }

    /**
     * Fetch email template variables
    */
    public function templateVariable(Request $request): JsonResponse
    {
        try
        {
            $this->repository->templateGroupValidation($request);
            $fetched = $this->repository->getConfigVariable($request);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-list-success', [ "name" => "Template Variable" ]));
    }

    /**
     * Fetch template content only
     */
    public function getTemplateContent(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetch($id);
            $fetched = $fetched->content;
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($fetched, $this->lang('fetch-success'));
    }
}
