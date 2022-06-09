<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;


class Language{

    /**
     * Getting the language from each request and setting it in config
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $locale = $request->get('lang');

        if(!isset($locale)){
            $locale = App::getLocale();
        }

        Config::set('locales.lang', $locale);

        return $next($request);
    }

}
