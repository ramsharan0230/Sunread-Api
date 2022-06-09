<?php

namespace Modules\Brand\Repositories;

use Modules\Brand\Entities\Brand;
use Modules\Core\Repositories\BaseRepository;

class BrandRepository extends BaseRepository
{
	public function __construct(Brand $brand)
	{
		$this->model = $brand;
		$this->model_key = "brands";
		$this->rules = [
			"name" => "required",
			"slug" => "nullable|unique:brands,slug",
			"description" => "required",
			"image" => "required|mimes:bmp,jpeg,jpg,png,webp",
			"meta_title" => "sometimes|nullable",
			"meta_description" => "sometimes|nullable",
			"meta_keywords" => "sometimes|nullable"
		];
	}	
}
