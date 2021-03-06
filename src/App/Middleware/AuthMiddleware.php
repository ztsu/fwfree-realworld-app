<?php

namespace Realworld\App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Realworld\Infrastructure\Service\AuthTokenCoderService;
use Realworld\Domain\Service\AuthTokenCoderServiceInterface;

/**
 * Authenticates request with Authorization header
 */
class AuthMiddleware implements MiddlewareInterface
{
    const ATTR_NAME = "auth_token";

    /**
     * @var AuthTokenCoderServiceInterface
     */
    private $service;

    public function __construct(AuthTokenCoderServiceInterface $service)
    {
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeader("Authorization");

        if (count($header) > 0) {
            try {
                $token = $this->service->decode(preg_replace("/^Token\s+/", "", $header[0]));
                $request = $request->withAttribute(self::ATTR_NAME, $token);

            } catch (\Throwable $exception) {
                // pass
            }
        }

        return $handler->handle($request);
    }
}