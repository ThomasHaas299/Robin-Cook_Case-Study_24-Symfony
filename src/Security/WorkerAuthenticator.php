<?php

namespace App\Security;

use App\Repository\WorkerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Throwable;

class WorkerAuthenticator extends AbstractAuthenticator
{

    public function __construct(private readonly WorkerRepository $workerRepository)
    {
    }

    public function supports(Request $request): ?bool
    {
        // return $request->headers->has('X-Worker-Id') && $request->headers->has('X-Worker-Token');
        // Supports all /api/-requests
        return str_starts_with($request->getPathInfo(), '/api/');
    }

    public function authenticate(Request $request): Passport
    {
        if (!($request->headers->has('X-Worker-Id') && $request->headers->has('X-Worker-Token'))) {
            throw new AuthenticationException('No authentication provided');
        }

        $workerId = $request->headers->get('X-Worker-Id');
        $workerToken = $request->headers->get('X-Worker-Token');

        $userBadgeCallback = function ($workerId) use ($workerToken) {
            return $this->workerRepository->findOneBy(
                [
                    'id' => $workerId,
                    'disabled' => 0,
                    'accessToken' => $workerToken]
            );
        };

        return new SelfValidatingPassport(
            new UserBadge((string) $workerId, $userBadgeCallback)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // successful authentication, nothing to do
        return null;
    }

    /**
     * @throws Throwable
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // authentication failure, the exception is thrown
        throw $exception;
    }
}
