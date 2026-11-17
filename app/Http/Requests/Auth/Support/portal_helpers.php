<?php

namespace {
    use App\Support\PortalRoute;

    if (! function_exists('portal_route')) {
        /** Build a route URL within the currently authenticated role's URL space. */
        function portal_route(string $name, mixed $parameters = [], bool $absolute = true): string
        {
            return route(PortalRoute::resolve($name), $parameters, $absolute);
        }
    }
}
