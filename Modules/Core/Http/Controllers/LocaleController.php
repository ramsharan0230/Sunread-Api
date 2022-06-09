<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Core\Entities\Locale;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\LocaleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Core\Transformers\LocaleResource;

class LocaleController extends BaseController
{
    public function __construct(Locale $locale)
    {
        $this->model = $locale;
        $this->model_name = "Locale";
        parent::__construct($this->model, $this->model_name);
    }

    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try
        {
            $this->validateListFiltering($request);
            $locales = $this->getFilteredList($request);
        }
        catch (QueryException $exception)
        {
            return $this->errorResponse($exception->getMessage(), 400);
        }
        catch(ValidationException $exception)
        {
            return $this->errorResponse($exception->errors(), 422);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(LocaleResource::collection($locales), $this->lang('fetch-list-success'));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try
        {
            $data = $this->validateData($request);

            Event::dispatch('core.locale.create.before');
            $locale = $this->model->create($data);
            Event::dispatch('core.locale.create.after', $locale);
        }
        catch (QueryException $exception)
        {
            return $this->errorResponse($exception->getMessage(), 400);
        }
        catch(ValidationException $exception)
        {
            return $this->errorResponse($exception->errors(), 422);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(new LocaleResource($locale), $this->lang('create-success'), 201);
    }

    /**
     * Get the particular resource
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try
        {
            $locale = $this->model->findOrFail($id);
        }
        catch (ModelNotFoundException $exception)
        {
            return $this->errorResponse($this->lang('not-found'), 404);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(new LocaleResource($locale), $this->lang('fetch-success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try
        {
            $data = $this->validateData($request, $id);
            $locale = $this->model->findOrFail($id);

            Event::dispatch('core.locale.update.before', $id);
            $locale = $locale->fill($data);
            $locale->save();
            Event::dispatch('core.locale.update.after', $locale);
        }
        catch (ModelNotFoundException $exception)
        {
            return $this->errorResponse($this->lang('not-found'), 404);
        }
        catch (QueryException $exception)
        {
            return $this->errorResponse($exception->getMessage(), 400);
        }
        catch(ValidationException $exception)
        {
            return $this->errorResponse($exception->errors(), 422);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(new LocaleResource($locale), $this->lang('update-success'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try
        {
            $locale = $this->model->findOrFail($id);
            // Cannot Delete if only one locale
            if ($this->model->count() == 1) return $this->errorResponse($this->lang('last-delete-error'));

            Event::dispatch('core.locale.delete.before', $locale);
            $locale->delete();
            Event::dispatch('core.locale.delete.after', $id);
        }
        catch (ModelNotFoundException $exception)
        {
            return $this->errorResponse($this->lang('not-found'), 404);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponseWithMessage($this->lang('delete-success'));
    }

    /**
     * Custom Validation for Store/Update
     * 
     * @param Request $request
     * @param int $id
     * @return Array
     */
    private function validateData($request, $id=null)
    {
        $id = $id ? ",{$id}" : null;

        return $this->validate($request, [
            "code" => "required|unique:locales,code{$id}",
            "name" => "required"
        ]);
    }
}
