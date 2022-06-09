<?php

namespace Modules\Review\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Review\Entities\ReviewVote;
use Modules\Review\Repositories\ReviewVoteRepository;
use Modules\Review\Transformers\ReviewVoteResource;

class ReviewVoteController extends BaseController
{
    protected $repository;

    public function __construct(ReviewVote $review_vote, ReviewVoteRepository $reviewVoteRepository)
    {
        $this->model = $review_vote;
        $this->model_name = "Review Vote";
        $this->repository = $reviewVoteRepository;
        parent::__construct($this->model, $this->model_name);
    }

    public function collection(object $data): ResourceCollection
    {
        return ReviewVoteResource::collection($data);
    }

    public function resource(object $data): JsonResource
    {
        return new ReviewVoteResource($data);
    }

    public function index(Request $request): JsonResponse
    {
        try
        {
            $fetched = $this->repository->fetchAll($request, ["customer"]);
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
            $fetched = $this->repository->fetch($id, ["customer"]);
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
