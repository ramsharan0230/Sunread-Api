<?php

namespace Modules\ClubHouse\Repositories;

use Exception;
use Illuminate\Support\Str;
use Modules\Core\Facades\CoreCache;
use Modules\ClubHouse\Traits\HasScope;
use Illuminate\Support\Facades\Storage;
use Modules\ClubHouse\Entities\ClubHouse;
use Modules\ClubHouse\Rules\SlugUniqueRule;
use Modules\Core\Repositories\BaseRepository;
use Modules\ClubHouse\Entities\ClubHouseValue;
use Modules\ClubHouse\Exceptions\ClubHouseNotFoundException;

class ClubHouseRepository extends BaseRepository
{
    use HasScope;

    // Properties for ClubHouseRepostiory
    protected $repository, $config_fields;
    protected bool $without_pagination = true;

    /**
     * ClubHouseRepository Class Constructor
     */
    public function __construct(ClubHouse $club_house, ClubHouseValue $club_house_value)
    {
        $this->model = $club_house;
        $this->value_model = $club_house_value;
        $this->model_key = "clubhouse";

        $this->rules = [
            // ClubHouse validation
            "website_id" => "required|exists:websites,id",
            "position" => "required|integer",
        ];

        $this->config_fields = config("clubhouse.attributes");

        $this->createModel();
    }

    /**
     * Get Attributes value from Config Data
     */
    public function getConfigData(array $data): array
    {
        $fetched = $this->config_fields;

        foreach($fetched as $key => $children){
            if(!isset($children["elements"])) continue;

            $children_data["title"] = $children["title"];
            $children_data["elements"] = [];

            foreach($children["elements"] as &$element){
                if($this->scopeFilter($data["scope"], $element["scope"])) continue;

                if(isset($data["club_house_id"])){
                    $data["attribute"] = $element["slug"];

                    $existData = $this->has($data);

                    if($data["scope"] != "website") $element["use_default_value"] = $existData ? 0 : 1;
                    $elementValue = $existData ? $this->getValues($data) : $this->getDefaultValues($data);
                    $element["value"] = $elementValue?->value ?? null;
                    if ($element["type"] == "file" && $element["value"]) $element["value"] = Storage::url($element["value"]);
                }
                unset($element["rules"]);

                $children_data["elements"][] = $element;
            }
            $fetched[$key] = $children_data;
        }
        return $fetched;
    }

    /**
     * Get ClubHouse with it's Attributes and Values
     */
    public function fetchWithAttributes(object $request, ClubHouse $club_house): array
    {
        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $club_house->website_id,
            "club_house_id" => $club_house->id
        ];

        // Accessing Clubhouse title through values
        $title_data = array_merge($data, ["attribute" => "title"]);
        $club_house->createModel();
        $title_value = $club_house->has($title_data) ? $club_house->getValues($title_data) : $club_house->getDefaultValues($title_data);

        $fetched = [
            "id" => $club_house->id,
            "website_id" => $club_house->website_id,
            "title" => $title_value?->value,
        ];

        $fetched["attributes"] = $this->getConfigData($data);
        return $fetched;
    }

    /**
     * Creates a Unique Slug for ClubHouse
     */
    public function createUniqueSlug(array $data, ?object $club_house = null)
    {
        $slug = is_null($club_house) ? Str::slug($data["items"]["title"]["value"]) : (isset($data["items"]["title"]["value"]) ? Str::slug($data["items"]["title"]["value"]) : $club_house->value([ "scope" => $data["scope"], "scope_id" => $data["scope_id"] ], "slug"));
        $original_slug = $slug;

        $count = 1;

        while ($this->checkSlug($data, $slug, $club_house)) {
            $slug = "{$original_slug}-{$count}";
            $count++;
        }
        return $slug;
    }

    public function fetchWithSlug(string $club_house_slug, $scope): object
    {
        try
        {
            $club_house_value = ClubHouseValue::whereAttribute("slug")->whereValue($club_house_slug)->firstOrFail();
            $club_house = $club_house_value->clubHouse;

            if(!$this->checkStatus($club_house, $scope)) throw new ClubHouseNotFoundException();
        }

        catch(Exception $exception)
        {
            throw $exception;
        }

        return $club_house;
    }

    public function checkStatus(object $club_house, $scope): bool
    {
        try
        {
            $data = [
                "scope" => "website",
                "scope_id" => $scope->id,
            ];
            $status_value = $club_house->value($data, "status");
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $status_value === "1" ? true : false;
    }

}

