<?php

namespace Modules\Core\Traits;

use Exception;

trait Filterable
{
    protected int $pagination_limit = 25;
    protected bool $without_pagination = false;

    public function validateListFiltering(object $request): array
    {
        try
        {
            $rules = [
                "limit" => "sometimes|numeric",
                "page" => "sometimes|numeric",
                "sort_by" => "sometimes",
                "sort_order" => "sometimes|in:asc,desc",
                "q" => "sometimes|string|min:1",
                "without_pagination" => "sometimes|boolean",
            ];

            $messages = [
                "limit.numeric" => "Limit must be a number.",
                "page.numeric" => "Page must be a number.",
                "sort_order.in" => "Order must be 'asc' or 'desc'.",
                "q.string" => "Search query must be string.",
                "q.min" => "Search query must be at least 1 character.",
                "without_pagination.boolean" => "Without pagination must be 0 or 1.",
            ];

            $data = $request->validate($rules, $messages);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function getFilteredList(object $request, array $with = [], ?object $rows = null): object
    {
        try
        {
            $sort_by = $request->sort_by ?? "id";
            $sort_order = $request->sort_order ?? "desc";
            $limit = (int) $request->limit ?? $this->pagination_limit;

            $rows = $rows ?? $this->model::query();
            if ($with !== []) {
                $rows = $rows->with($with);
            }
            if ($request->has("q")) {
                $rows = $rows->whereLike($this->model::$SEARCHABLE, $request->q);
            }
            $rows = $rows->orderBy($sort_by, $sort_order);

            $resources = ( $this->without_pagination == true || $request->without_pagination == true )
                ? $rows->get()
                : $rows->paginate($limit)->appends($request->except("page"));
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $resources;
    }
}
