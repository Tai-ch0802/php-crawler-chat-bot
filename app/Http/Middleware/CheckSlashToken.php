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

        $token = $request->input('token');
        if (null === $token) {
            $token = json_decode($request->payload, true)['token'];
            $message = var_export($request->input('payload'));
        }
        if (!in_array($token, $tokens, true)) {
            $data = [
                'text' => "Invalid Token!  Token: {$token}",
            ];
            return response()->json($data)->setStatusCode(200);
        }
        return $next($request);
    }
}
