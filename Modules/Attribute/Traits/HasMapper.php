<?php

namespace Modules\Attribute\Traits;

use Illuminate\Support\Facades\Schema;

trait HasMapper
{
    public $mapper; 

    public function getMapperData(): void
    {
        $this->mapper = config('mapper');
    }

    public function getConfigOption(): ?object
    {
        $fetched = null;
            
        if($this->checkOption())
        {
            $configSlug = $this->mapper[$this->slug];
            $model = new $configSlug["module"];
            //$fetched =  $model->select($configSlug["pluck"][0], $configSlug["pluck"][1]);
            if(Schema::hasColumn($model->getTable(), "status")) $model = $model->whereStatus(1);
            $fetched = $model->get();
        }
        return $fetched;
    }

    public function getModelClass(): ?object
    {
        $configSlug = $this->mapper[$this->slug];
        $model = new $configSlug["module"];
        return $model;
    }

    public function checkMapper(): bool
    {
        $this->getMapperData();
        return in_array($this->slug, array_keys($this->mapper)); 
    }

    public function checkOption(): bool
    {
        if($this->checkMapper()){
            return ($this->mapper[$this->slug]["options"] == 1);
        }
        return false;
    }

    public function checkTranslation(): bool
    {
        if($this->checkMapper() && $this->checkOption()){
            return ($this->mapper[$this->slug]["translations"] == 1);
        }
        else return true;
    }

    public function checkCreateOrUpdate(): bool
    {
        if($this->checkMapper()){
            return ($this->mapper[$this->slug]["create-update"] == 1);
        }
        return false;
    }
}
