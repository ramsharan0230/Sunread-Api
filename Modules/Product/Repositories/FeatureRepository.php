<?php

namespace Modules\Product\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Modules\Core\Repositories\BaseRepository;
use Modules\Product\Entities\Feature;
use Exception;
use Illuminate\Http\Request;

class FeatureRepository extends BaseRepository
{
    private $featureTranslationRepository;

    public function __construct(Feature $feature, FeatureTranslationRepository $featureTranslationRepository)
    {
        $this->model = $feature;
        $this->model_key = "catalog.features";
        $this->rules = [
            "name" => "required",
            "description" => "nullable",
            "status" => "sometimes|boolean",
            // translation validation
            "translations" => "nullable|array",
        ];
        $this->featureTranslationRepository = $featureTranslationRepository;
    }

    public function removeImage(object $deleted): bool
    {
        try
        {
            if (!$deleted->image) {
                return true;
            }

            $this->removeFolder($deleted);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function removeFolder(object $data): bool
    {
        try
        {
            $path_array = explode("/", $data->image);
            unset($path_array[count($path_array) - 1]);

            $delete_folder = implode("/", $path_array);
            Storage::disk("public")->deleteDirectory($delete_folder);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return true;
    }

    public function show(int $id): array
    {
        try
        {
            $fetched = $this->fetch($id)->toArray();
            $fetched["image"] = $fetched["image_url"];
            $fetched["translations"] = $this->featureTranslationRepository->show($id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    public function validateFeatureData(Request $request): array
    {
        try
        {
            $validateImage = [];
            if ($request->file("image")) {
                $validateImage["image"] = "mimes:bmp,jpeg,jpg,png,webp,svg";
            }
            $data = $this->validateData($request, $validateImage);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    public function updateImage(object $request, array $data, int $id): array
    {
        try
        {
            if ($request->image) {
                unset($data["image"]);
            }
            else {
                $feature = $this->fetch($id);
                $this->removeFolder($feature);
                $data["image"] = null;
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
