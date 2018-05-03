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
            config('services.slack.slash.secretary')
        ];

        $token = $request->input('token');
        $payload = $request->input('payload');
        if (null !== $payload) {
            $token = json_decode($payload)->token;
        }
        if (!in_array($token, $tokens, true)) {
            $data = [
                'text' => "Invalid Token!  Token: {$token}",
            ];
            return response()->json($data);
        }

        return $next($request);
    }
}
