<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory as FactoriesHasFactory;

trait HasFactory
{
    use FactoriesHasFactory;

    /**
     * Get a new factory instance for the model.
     * Modified to get Factories from Module
     *
     * @param  mixed  $parameters
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function factory(...$parameters)
    {
        $factory_class = self::factoryNameResolver(get_called_class());
        $factory = $factory_class::new();

        return $factory
            ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : null)
            ->state(is_array($parameters[0] ?? null) ? $parameters[0] : ($parameters[1] ?? []));
    }

    public static function factoryNameResolver($model_name)
    {
        $model_array = explode("\\", $model_name);
        $module_name = $model_array[1] ?: "Core";
        $model_name = $model_array[count($model_array) - 1] ?: $model_name;

        return "\\Modules\\{$module_name}\\Database\\factories\\{$model_name}Factory";
    }
}
