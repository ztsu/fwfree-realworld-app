<?php

namespace Realworld\App\Handler\Login\Post;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Realworld\Domain\Model\UserAuthToken;
use Realworld\Infrastructure\Service\AuthTokenCoderService;
use Realworld\App\Common\ResponseDto\AuthenticatedUserResponseDto;
use Realworld\App\Handler\HandlerInterface;
use Realworld\Domain\Service\AuthenticateUserByPasswordService;

/**
 * Handles POST request to /api/users/login
 */
class LoginPostHandler implements HandlerInterface
{
    /**
     * @var AuthenticateUserByPasswordService
     */
    private $service;

    /**
     * @var AuthTokenCoderService
     */
    private $tokenService;

    /**
     * @param AuthenticateUserByPasswordService $service
     * @param AuthTokenCoderService $tokenService
     */
    public function __construct(
        AuthenticateUserByPasswordService $service,
        AuthTokenCoderService $tokenService
    ) {
        $this->service = $service;
        $this->tokenService = $tokenService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $requestDto = null;

        try {
            $requestDto = RequestDto::create($request);

        } catch (\Throwable $exception) {
            $response = $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
            $response->getBody()->write($exception->getMessage());

            return $response;
        }

        $user = $this->service->authenticate($requestDto->email, $requestDto->password);
        $token = $this->tokenService->encode(UserAuthToken::create($user));

        $response = (new ResponseDto(AuthenticatedUserResponseDto::create($user, $token)))->writeTo($response);

        return $response;
    }
}