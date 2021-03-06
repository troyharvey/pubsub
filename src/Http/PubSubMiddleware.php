<?php

namespace GenTux\PubSub\Http;

use GenTux\PubSub\Exceptions\PubSubSecurityTokenException;
use Closure;

class PubSubMiddleware
{
    /**
     * Validate the PubSub security token attached to the request
     *
     * @param \Illuminate\Http\Request $request HTTP Request
     * @param Closure $next Next middleware
     *
     * @throws PubSubSecurityTokenException
     *
     * @return Closure
     */
    public function handle($request, Closure $next)
    {
        $securityToken = getenv('PUBSUB_SUBSCRIBER_TOKEN');

        if (empty($securityToken) || $request->input('token') == $securityToken) {
            return $next($request);
        } else {
            throw PubSubSecurityTokenException::forThis($request->input('token'));
        }
    }
}
