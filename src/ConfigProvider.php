<?php

declare(strict_types=1);
/**
 * This file is part of mogody/hyperf-responsable.
 *
 * @link     https://github.com/mogody/hyperf-responsable
 * @document https://github.com/mogody/hyperf-responsable/blob/master/README.md
 * @contact  wenghang1228@gmail.com
 * @license  https://github.com/mogody/hyperf-responsable/blob/master/LICENSE
 */
namespace Mogody\Responsable;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Hyperf\HttpServer\CoreMiddleware::class => \Mogody\Responsable\CoreMiddleware::class,
            ],
        ];
    }
}
