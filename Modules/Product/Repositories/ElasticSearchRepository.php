<?php

namespace Modules\Product\Repositories;

class ElasticSearchRepository 
{
    protected $scope, $path, $storeORGlobal;

    public function orwhereQuery(array $filter): array
    {
        return [
            "bool"=> [
                "should"=>  $filter
            ]
        ]; 
    }

    public function whereQuery(array $filter): array
    {
        return [
            "bool"=> [
                "must"=>  $filter
            ]
        ]; 
    }

    public function queryString(array $field, string $data): array
    {
        return [
            "query_string" => [
                "query" => '*'.$data.'*',
                "fields" => $field
            ]
        ];
    }

    public function match(string $field, ?string $data): array
    {
        return [
            "match" => [
                $field => $data
            ]
        ];
    }

    public function term(string $field, ?string $data): array
    {
        return [
            "term" => [
                $field => $data
            ]
        ];
    }

    public function terms(string $field, ?array $data): array
    {
        return [
            "terms" => [
                $field => $data
            ]
        ];
    }

    public function range(string $field, $value1, $value2): array
    {
        return [
            "range"=> [
                $field=> [
                "gte"=> $value1,
                "lte"=> $value2
                ]
            ]
        ];
    }

    public function sort(string $field, ?string $order): array
    {
        return [
            [$field => ["order" => $order]]
        ];
    }

    public function aggregate(string $field): array
    {
        return [ 
            "terms" => [
                "field" => $field
            ]
        ];
    }

    public function wildcard(string $field, ?string $data): array
    {
        return [ 
            "wildcard" => [
                $field => "{$data}*"
            ]
        ];
    }

    public function multimatch(string $field, ?string $data): array
    {
        return [ 
            "multi_match" => [
                "query"=> $data,
                "type" => "bool_prefix",
                "fields" => [
                    $field,
                    "{$field}._2gram",
                    "{$field}._3gram"
                ]
            ]
        ];
    }

    public function match_phrase_prefix(string $field, ?string $data): array
    {
        return [ 
            "match_phrase_prefix" => [
                $field => [
                    "query" => $data
                ]
            ]
        ];
    }
}
