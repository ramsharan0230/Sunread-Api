<?php

namespace  Modules\UrlRewrite\Exceptions;

use Exception;

class UrlRewriteException extends Exception
{
	public static function requestPath(string $requestPath): self
    {
        return new static("Request path `{$requestPath}` already exists.");
    }

	public static function noConfiguration(): self
    {
        return new static('Not configure properly.');
    }

    public static function invalidType($type): self
    {
        return new static("Type `{$type}` does not exist.");
    }

    public static function columnNotSet($urlRewrite, $column): self
    {
        return new static("Url rewrite with id `{$urlRewrite->id}` has no `{$column}`");
    }
}