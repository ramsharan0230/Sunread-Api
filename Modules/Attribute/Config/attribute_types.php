<?php

$types = [
	"text" => "string",
	"textarea" => "text",
	"texteditor" => "text",
	"price" => "decimal",
	"boolean" => "boolean",
	"number" => "integer",
	"select" => "string",
	"multiselect" => "text",
	"datetime" => "timestamp",
	"date" => "timestamp",
	"image" => "text",
	"file" => "text",
	"checkbox" => "text",
	"multiimages" => "text",
	"builder" => "text",
	"categoryselect" => "text",
	"productimages" => "text"
];

return array_map(function($data) {
	$data = ucfirst($data);
	return "Modules\Product\Entities\ProductAttribute{$data}";
}, $types);
