<?php

return [
    'table-name' => 'url_rewrites',
    'repository' => Modules\UrlRewrite\Repositories\UrlRewriteRepository::class,
    'model' => Modules\UrlRewrite\Entities\UrlRewrite::class,
    'cache' => true,
    'cache-tag' => 'url_rewrites',
    'cache-ttl' => 86400,
    'cache-decorator' => Modules\UrlRewrite\Repositories\Decorators\CachingUrlRewriteRepository::class,
    'path' => [
        'Category' => 'Modules\Category\Entities\Category',
        'Product' => 'Modules\Product\Entities\ProductAttribute'
    ],
    'types' => ['Category', 'Product']
];

