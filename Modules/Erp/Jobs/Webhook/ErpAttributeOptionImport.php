<?php

namespace Modules\Erp\Jobs\Webhook;

use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Erp\Traits\Mapper\AttributeMapper;

class ErpAttributeOptionImport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use AttributeMapper;

    protected object $data;
    public int $website_id;
    public bool $fetch_from_api;
    public string $base_url;

    public function __construct(object $data)
    {
        $this->data = $data;
        $this->base_url = config("erp_config.end_point");
        $this->fetch_from_api = true;
    }

    public function handle(): void
    {
        try
        {
            $attribute_options = $this->getDetailCollection("webAssortments", $this->data->sku);
            foreach ($attribute_options as $attribute_option) {
                $this->createOption((object) $attribute_option);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
}
