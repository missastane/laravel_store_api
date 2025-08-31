<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];

    public function handle($request, \Closure $next)
    {
        if($request->isMethod('put') && empty($request->all())){
            $data = [];
            parse_str($request->getContent(),$data);
            $request->merge($data);
        }
        return $next($request);
    }
}
