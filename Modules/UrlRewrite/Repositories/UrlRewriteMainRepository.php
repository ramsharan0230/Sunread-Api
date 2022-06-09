<?php

namespace Modules\UrlRewrite\Repositories;

use Exception;
use Modules\Core\Repositories\BaseRepository;
use Modules\UrlRewrite\Rules\UrlRewriteRule;
use Modules\UrlRewrite\Entities\UrlRewrite;
use Modules\UrlRewrite\Facades\UrlRewrite as FacadesUrlRewrite;

class UrlRewriteMainRepository extends BaseRepository
{
    protected $repository;

    public function __construct(UrlRewrite $url_rewrite)
    {
        $this->model = $url_rewrite;
        $this->model_key = "url_rewrites";
        $this->rules = [
            "type" => "required",
            "type_attributes" =>"required|array",
            "request_path" => "required",
            "target_path" => "required",
            "redirect_type" => "sometimes",
        ];
    }

    public function geturlRewriteData(array $item, ?int $id = null): array
    {
        try
        {
            $urlRewrite = [];
            $model_path = config("url-rewrite.path.".$item['type']);
            $model = new $model_path(); 
    
            $urlRewrite['type_attributes']["parameter"][$model->urlRewriteParameterKey[0]] = $item['parameter_id'];
            $urlRewrite['type'] = $model->urlRewriteType;
    
            $this->requestPathExists($item, $id) ? $urlRewrite['request_path'] = FacadesUrlRewrite::generateUnique($item['request_path']) : $urlRewrite['request_path'] = $item['request_path'];
    
            if($item['store_id']) $urlRewrite['type_attributes']["extra_fields"]["store_id"] = $item['store_id'];
    
            if($this->urlRewriteExists($urlRewrite, $id)) throw new Exception("Already Exists", 422);
            
            $urlRewrite['target_path'] = route($model->urlRewriteRoute,  $urlRewrite['type_attributes']["parameter"], false);
    
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $urlRewrite;
    }

    public function validateUrlRewrite(object $request): array
    {
        $types = implode(',', config('url-rewrite.types'));
        $data = $request->validate([
            "type" => "required|in:$types",
            "parameter_id" => [ "required", new UrlRewriteRule($request->type) ],
            "store_id" => "nullable|exists:stores,id",
            "request_path" => "required" 
        ]);
        return $data;
    }

    public function urlRewriteExists(array $urlRewrite, int $id = null): bool
    {
        try
        {
            $exist_data_query = $this->model->getByTypeAndAttributes($urlRewrite['type'], $urlRewrite['type_attributes']);
            if(isset($id)) $exist_data_query = $exist_data_query->where('id', '!=', $id);
            $existed = (boolean) $exist_data_query->first();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        
        return $existed;
    }

    public function requestPathExists(array $item, int $id = null): bool
    {
        try
        {
            $exist_data_query = $this->model->where('request_path', $item['request_path']);
            if(isset($id)) $exist_data_query = $exist_data_query->where('id', '!=', $id);
            $existed = (boolean) $exist_data_query->first();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $existed;
    }
}
