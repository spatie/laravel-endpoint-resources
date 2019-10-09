<?php

namespace Spatie\ResourceLinks\Tests\Fakes;

use Illuminate\Http\Resources\Json\Resource;
use Spatie\ResourceLinks\HasLinks;
use Spatie\ResourceLinks\HasMeta;

class FakeResource extends Resource
{
    use HasLinks, HasMeta;

    public function toArray($request)
    {
      return [
              'links' => $this->links(TestController::class),
            ];
    }

    public static function meta()
    {
        return [
            'links' => self::collectionLinks(TestController::class),
        ];
    }
};
