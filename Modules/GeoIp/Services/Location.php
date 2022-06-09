<?php

namespace Modules\GeoIp\Services;

use ArrayAccess;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Location implements ArrayAccess
{
    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function same(string $ip): bool
    {
        return $this->getAttribute('ip') == $ip;
    }

    public function setAttribute(string $key, mixed $value): Location
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        $value = Arr::get($this->attributes, $key);

        if (method_exists($this, 'get' . Str::studly($key) . 'Attribute')) {
            $method = 'get' . Str::studly($key) . 'Attribute';

            return $this->{$method}($value);
        }

        return $value;
    }

    public function getDisplayNameAttribute(): string
    {
        return preg_replace('/^,\s/', '', "{$this->city}, {$this->state}");
    }

    public function getDefaultAttribute(mixed $value): mixed
    {
        return is_null($value) ? false : $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->{$offset});
    }

    public function __isset(mixed $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }
}
