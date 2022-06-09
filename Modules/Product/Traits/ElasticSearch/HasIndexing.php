<?php

namespace Modules\Product\Traits\ElasticSearch;

use Elasticsearch\ClientBuilder;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;
use Modules\Core\Traits\Cacheable;

trait HasIndexing
{
    use TestIndexing, Cacheable;
    protected $client;

    public function setIndexName(int $id): string
    {
        $prefix = config("elastic.prefix");
        return "{$prefix}{$id}";
    }

    public function connectElasticSearch(): void
    {
        $host = config("elastic.client.hosts");
        $this->client = ClientBuilder::create()->setHosts($host)->build();
    }

    public function checkIndexIfExist(array $params): bool
    {
        try
        {
            $this->connectElasticSearch();

            $exists = $this->client->indices()->exists($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $exists;
    }

    public function checkDocumentIfExist(array $params): bool
    {
        try
        {
            $this->connectElasticSearch();

            $exists = $this->client->exists($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $exists;
    }

    public function createIndexIfNotExist(array $params): void
    {
        try
        {
            $exist = $this->checkIndexIfExist($params);

            if (!$exist) {
                $createparams = array_merge($params, [
                    "body" => [
                        "settings" => [
                            "analysis" => [
                                "analyzer" => [
                                    'autocomplete' => [
                                        'tokenizer' => 'autocomplete',
                                        'filter' =>[
                                            'lowercase',
                                            'asciifolding'
                                        ],
                                    ],
                                    'autocomplete_search' => [
                                        'tokenizer' => 'lowercase',
                                    ]
                                ],
                                "tokenizer" => [
                                    "autocomplete"=> [
                                        "type"=> "edge_ngram",
                                        "min_gram"=> 2,
                                        "max_gram"=> 10,
                                        "token_chars" => [
                                            "letter",
                                            "digit"
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        "mappings" => config("mapping")
                    ]
                ]);
                $this->client->indices()->create($createparams);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function singleIndexing(object $product, object $store): void
    {
        try
        {
            $params["index"]  = $this->setIndexName($store->id);
            $this->createIndexIfNotExist($params);

            $params = array_merge($params, [
                "id" => $product->id,
                "body" => $product->documentDataStructure($store),
            ]);
            $this->client->index($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function partialIndexing(object $product, object $store, array $slug_values): void
    {
        try
        {
            $params = [
                "index" => $this->setIndexName($store->id),
                "id" => $product->id
            ];
            $exists = $this->checkDocumentIfExist($params);

            if ($exists) {
                $params = array_merge($params, [
                    "body" => [
                        "doc" => $slug_values
                    ]
                ]);
                $this->client->update($params);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function partialRemoving(object $product, object $store, array $slug_values, array $field_values): void
    {
        try
        {
            if(count($slug_values) > 0) $this->partialIndexing($product, $store, $slug_values);

            $params = [
                "index" => $this->setIndexName($store->id),
                "id" => $product->id
            ];
            $exists = $this->checkDocumentIfExist($params);

            if ($exists) {

                if (count($field_values) > 0) {
                    $fields = implode(";", $field_values);

                    $params = array_merge($params, [
                        'body'  => [
                            'script' => $fields
                        ]
                    ]);

                    $this->client->update($params);
                }
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function removeDocument(object $product, object $store): void
    {
        try
        {
            $params = [
                "index" => $this->setIndexName($store->id),
                "id" => $product["id"]
            ];
            $exists = $this->checkDocumentIfExist($params);

            if ($exists) $this->client->delete($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function removeIndex(object $store): void
    {
        try
        {
            $params = [
                "index" => $this->setIndexName($store["id"])
            ];
            $exists = $this->checkIndexIfExist($params);

            if ($exists) $this->client->indices()->delete($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function bulkIndexing(object $products): void
    {
        try
        {
            foreach($products as $product)
            {
                $product->load("categories", "product_attributes", "catalog_inventories");
                $stores = Website::find($product->website_id)->channels->map(function ($channel) {
                    return $channel->stores;
                })->flatten(1);

                foreach($stores as $store)
                {
                    $createParams["index"] = $this->setIndexName($store->id);
                    $this->createIndexIfNotExist($createParams);

                    $params["body"][] = [
                        "index" => [
                            "_index" => $createParams["index"],
                            "_id" => $product->id
                        ]
                    ];

                    $params["body"][] = $product->documentDataStructure($store);
                }
            }
            $this->client->bulk($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function bulkIndexingSimpleTest(object $product): void
    {
        try
        {
            $stores = $product->website->stores;

            foreach($stores as $store) {
                $createParams["index"] = $this->setIndexName($store->id);
                $this->createIndexIfNotExist($createParams);

                $params = array_merge($createParams, [
                    "id" => $product->id,
                    "body" => $this->documentDataStructure($store, $product)
                ]);
                $this->client->index($params);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function bulkIndexingConfigurableTest(object $product, object $store): void
    {
        try
        {
            $createParams["index"] = "test_sail_racing_{$store->id}";
            $this->createIndexIfNotExist($createParams);
            $data = $this->storeCache("reindex", $product->id, function () use ($store, $product) {
                return $this->documentDataStructure($store, $product);
            });
            $params = array_merge($createParams, [
                "id" => $product->id,
                "body" => $data
            ]);
            $this->client->index($params);

            foreach ($product->variants as $variant) {
                Log::info("Variant Products", [
                    "message" => "Variant products started successfully.",
                    "time" => now()->format("H:m:s"),
                    "product" => $variant->id,
                    "parent_id" => $variant->parent_id,
                ]);
                $createParams["index"] = "test_sail_racing_{$store->id}";
                $this->createIndexIfNotExist($createParams);
                $variant_data = $this->storeCache("reindex", $variant->id, function () use ($store, $variant) {
                    return $this->documentDataStructure($store, $variant);
                });
                $params = array_merge($createParams, [
                    "id" => $variant->id,
                    "body" => $variant_data,
                ]);
                $this->client->index($params);
                Log::info("Variant Products", [
                    "message" => "Variant products created successfully.",
                    "time" => now()->format("H:m:s"),
                    "product" => $variant->id,
                    "parent_id" => $variant->parent_id,
                ]);
            }
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function configurableIndexing(array $product, object $store): void
    {
        try
        {
            $params["index"]  = $this->setIndexName($store->id);
            $this->createIndexIfNotExist($params);

            $params = array_merge($params, [
                "id" => $product["id"],
                "body" => $product
            ]);
            $this->client->index($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function bulkConfigurableRemoving(object $parent, object $store): void
    {
        try
        {
            $createParams["index"] = $this->setIndexName($store->id);
            $this->createIndexIfNotExist($createParams);
            $params["body"][] = [
                "delete" => [
                    "_index" => $createParams["index"],
                    "_id" => $parent->id
                ]
            ];
            foreach($parent->variants as $variant)
            {
                $params["body"][] = [
                    "delete" => [
                        "_index" => $createParams["index"],
                        "_id" => $variant->id
                    ]
                ];
            }
            if(isset($params)) $this->client->bulk($params);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }
    }

    public function searchIndex(array $data, object $store): ?array
    {
        try
        {
            $params["index"]  = $this->setIndexName($store->id);
            $exists = $this->checkIndexIfExist($params);

            $params = array_merge($params, [ "body" => $data ]);
            $response = $exists ? $this->client->search($params) : null;
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $response;
    }

    public function getStoreId(): ?object
    {
        try
        {
            $store_code = array_key_exists("store", getallheaders()) ? getallheaders()["store"] : "english-store";
            $store = Store::whereCode($store_code)->first();
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $store;
	}

    public function getStores(object $product): ?object
    {
        try
        {
            $stores = Website::find($product->website_id)->channels->map(function ($channel) {
                return $channel->stores;
            })->flatten(1);
        }
        catch(Exception $exception)
        {
            throw $exception;
        }

        return $stores;
    }

}
