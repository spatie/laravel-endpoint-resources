<?php

namespace Spatie\LaravelEndpointResources;

use Illuminate\Support\Arr;

trait HasEndpoints
{
    public function endpoints(string $controller = null, $parameters = null): EndpointResource
    {
        $endPointResource = new EndpointResource($this->resource, EndpointResourceType::ITEM);

        if ($controller !== null) {
            $endPointResource->addController($controller, Arr::wrap($parameters));
        }

        return $endPointResource;
    }

    public static function collectionEndpoints(string $controller = null, $parameters = null): EndpointResource
    {
        $endPointResource = new EndpointResource(null, EndpointResourceType::COLLECTION);

        if ($controller !== null) {
            $endPointResource->addController($controller, Arr::wrap($parameters));
        }

        return $endPointResource;
    }

    public static function meta()
    {
        return [];
    }

    public static function collection($resource)
    {
        if ($meta = self::meta()) {
            return parent::collection($resource)->additional([
                'meta' => $meta,
            ]);
        }

        return parent::collection($resource);
    }

    public static function make(...$parameters)
    {
        if ($meta = self::meta()) {
            return parent::make(...$parameters)->additional([
                'meta' => $meta,
            ]);
        }

        return parent::make(...$parameters);
    }
}
