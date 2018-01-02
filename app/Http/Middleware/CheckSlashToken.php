<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckSlashToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next)
    {
        $tokens = [
            config('services.slack.slash.twitch'),
            config('services.slack.slash.comic'),
            config('services.slack.slash.animation'),
        ];

        $token = $request->input('token', $request->input('payload.token'));
        if (!in_array($token, $tokens, true)) {
            $data = [
                'text' => 'Invalid Token!',
            ];
            return response()->json($data)->setStatusCode(401);
        }
        return $next($request);
    }
}
