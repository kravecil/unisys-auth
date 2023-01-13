<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Models\Log;
use App\Models\User;

class Logger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requestTime = microtime(true);

        $log = new Log();

        $log->username = app(User::class)->id;
        $log->ip = $request->ip();
        $log->url = $request->fullUrl();
        $log->method = $request->method();
        $log->content = $request->getContent();
        $log->duration = number_format(microtime(true) - $requestTime, 3);

        $log->save();

        return $next($request);
    }
}
