<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckSlashToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tokens = [
            config('services.slack.slash.twitch'),
            config('services.slack.slash.comic'),
            config('services.slack.slash.animation'),
        ];
        if (!in_array($request->token, $tokens, true)) {
            return new Response([
                'text' => 'Invalid Token!',
            ], 401);
        }
        return $next($request);
    }
}
