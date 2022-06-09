<?php

namespace Modules\Review\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Review\Entities\Review;
use Modules\Review\Repositories\ReviewRepository;
use Modules\Review\Transformers\ReviewResource;

class ReviewController extends BaseController
{
    protected $repository;

    public function __construct(Review $review, ReviewRepository $reviewRepository)
    {
        $this->model = $review;
        $this->model_name = "Review";
        $this->repository = $reviewRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ReviewResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ReviewResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["customer", "review_votes", "review_replies"]);
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), $this->lang('fetch-list-success'));
    }

    public function pendingList(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["customer", "review_votes", "review_replies"], function() {
                return $this->model::whereStatus(0);
            });
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->collection($fetched), "Pending review list fetched successfully.");
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
            $fetched = $this->repository->fetch($id, ["customer", "review_votes", "review_replies"]);
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

    public function verify(int $id): JsonResponse
    {
        try
        {
            $fetched = $this->model->findOrFail($id);
            $fetched->status = 1;
            $fetched->save();
        }
        catch( Exception $exception )
        {
            return $this->handleException($exception);
        }

        return $this->successResponse($this->resource($fetched), "Review verified successfully.", 200);
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

        return $this->successResponse($this->resource($updated), $this->lang("status-updated"));
    }
}
