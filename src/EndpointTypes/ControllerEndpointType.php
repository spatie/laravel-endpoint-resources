<?php

namespace Spatie\LaravelResourceEndpoints\EndpointTypes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ControllerEndpointType extends EndpointType
{
    /** @var string */
    private $controller;

    /** @var array */
    private $methods = [];

    /** @var array */
    private static $cachedRoutes = [];

    /** @var array */
    private $names = [];

    public static function make(string $controller): ControllerEndpointType
    {
        return new ControllerEndpointType($controller);
    }

    public function __construct(string $controller)
    {
        $this->controller = $controller;
    }

    public function methods($methods): ControllerEndpointType
    {
        $this->methods = Arr::wrap($methods);

        return $this;
    }

    public function names(array $names): ControllerEndpointType
    {
        $this->names = $names;

        return $this;
    }

    public function getEndpoints(Model $model = null): array
    {
        $methodsToInclude = empty($this->methods)
            ? ['show', 'edit', 'update', 'destroy']
            : $this->methods;

        return $this->resolveEndpoints($methodsToInclude, $model);
    }

    public function getCollectionEndpoints(): array
    {
        $methodsToInclude = empty($this->methods)
            ? ['index', 'store', 'create']
            : $this->methods;

        return $this->resolveEndpoints($methodsToInclude);
    }

    public static function clearCache()
    {
        self::$cachedRoutes = [];
    }

    private function resolveEndpoints(array $methodsToInclude, Model $model = null): array
    {
        $endpoints = self::getRoutesForController($this->controller)
            ->filter(function (Route $route) use ($methodsToInclude) {
                return in_array($route->getActionMethod(), $methodsToInclude);
            })->map(function (Route $route) use ($model) {
                $route = RouteEndpointType::make($route)
                    ->parameters($this->parameters)
                    ->name($this->resolveNameForRoute($route))
                    ->prefix($this->prefix)
                    ->formatter($this->formatter)
                    ->getEndpoints($model);

                return $route;
            })->toArray();

        return ! empty($endpoints)
            ? array_merge_recursive(...$endpoints)
            : [];
    }

    private static function getRoutesForController(string $controller): Collection
    {
        if (array_key_exists($controller, self::$cachedRoutes)) {
            return self::$cachedRoutes[$controller];
        }

        $routes = collect(resolve(Router::class)->getRoutes()->getRoutes());

        self::$cachedRoutes[$controller] = $routes
            ->filter(function (Route $route) use ($controller) {
                return $controller === Str::before($route->getActionName(), '@');
            });

        return self::$cachedRoutes[$controller];
    }

    private function resolveNameForRoute(Route $route): string
    {
        $method = $route->getActionMethod();

        if (array_key_exists($method, $this->names)) {
            return $this->names[$method];
        }

        return $method;
    }
}
