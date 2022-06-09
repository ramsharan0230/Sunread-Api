<?php

namespace Modules\Review\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Review\Entities\ReviewReply;
use Modules\Review\Repositories\ReviewReplyRepository;
use Modules\Review\Transformers\ReviewReplyResource;

class ReviewReplyController extends BaseController
{
    protected $repository;

    public function __construct(ReviewReply $review_reply, ReviewReplyRepository $reviewReplyRepository)
    {
        $this->model = $review_reply;
        $this->model_name = "Review Reply";
        $this->repository = $reviewReplyRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ReviewReplyResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ReviewReplyResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $this->validateListFiltering($request);
            $fetched = $this->getFilteredList($request);
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
            $created = $this->repository->create($data);
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
            $fetched = $this->model->findOrFail($id);
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
            $updated = $this->repository->update($data, $id);
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
}
