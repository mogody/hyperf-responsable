# Hyperf Responseable interface

让 Hyperf 支持新的返回类型 Responsable 接口。从控制器返回时，该接口将对象转换为 HTTP 响应实例，实现 Responsable 接口需要实现一个 toResponse() 表示该对象作为 HTTP 响应方法。

```php
<?php
namespace App\Response;

use Mogody\Responsable\Contract\Responsable;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ExampleObject implements Responsable
{
    public function __construct($name = null)
    {
        $this->name = $name ?? 'Teapot'; 
    }

    public function status()
    {
        switch(strtolower($this->name)) {
            case 'teapot':
                return 418;
            default:
                return 200;
        }
    }

    public function toResponse(PsrRequestInterface $request): PsrResponseInterface
    {
        $response = \Hyperf\Utils\Context::get(PsrResponseInterface::class);    
        return $response->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream("Hello {$this->name}"));
    }
}
```

然后在控制器中：
```php
<?php

namespace App\Controller;

use App\Response\ExampleObject;

class PostsController
{
    public function index()
    {
      
        return new ExampleObject('Taylor');
    }
}
```