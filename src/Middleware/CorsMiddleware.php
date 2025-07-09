<?php
namespace App\Middleware;

use Cake\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        // Adiciona cabeçalhos CORS
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', implode(', ', $this->options['origin']))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->options['methods']))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->options['headers']))
            ->withHeader('Access-Control-Allow-Credentials', $this->options['credentials'] ? 'true' : 'false')
            ->withHeader('Access-Control-Expose-Headers', implode(', ', $this->options['exposeHeaders']))
            ->withHeader('Access-Control-Max-Age', $this->options['maxAge']);

        return $response;
    }
}