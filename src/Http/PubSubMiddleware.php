<?php

namespace GenTux\GooglePubSub\Http;

use Closure;
use GenTux\GooglePubSub\Exceptions\PubSubSecurityTokenException;

class PubSubMiddleware
{
    /**
     * Validate the PubSub security token attached to the request
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @throws PubSubSecurityTokenException
     * @return Closure
     */
    public function handle($request, Closure $next)
    {
        $securityToken = getenv('GOOGLE_PUB_SUB_SUBSCRIBER_TOKEN');

        if (empty($securityToken) || $request->input('token') == $securityToken) {
            return $next($request);
        } else {
            throw PubSubSecurityTokenException::forThis($request->input('token'));
        }
    }
}
