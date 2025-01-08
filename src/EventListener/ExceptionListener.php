<?php

namespace App\EventListener;

use App\Exceptions\NoTaskAvailableException;
use App\Exceptions\TaskNotFoundException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final readonly class ExceptionListener
{
    public function __construct(private ViewHandlerInterface $viewHandler)
    {
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $error = 'An error occurred';
        $message = $exception->getMessage();
        $statusCode = 500;

        if ($exception instanceof NotFoundHttpException) {
            $error = 'Not Found';
            $message = 'The requested resource could not be found.';
            $statusCode = 404;
        } elseif ($exception instanceof AuthenticationException || $exception instanceof HttpException) {
            $error = 'Unauthorized';
            $statusCode = 401;
        } elseif ($exception instanceof NoTaskAvailableException) {
            $error = NoTaskAvailableException::ERROR;
            $message = NoTaskAvailableException::MESSAGE;
            $statusCode = 404;
        } elseif ($exception instanceof TaskNotFoundException) {
            $error = TaskNotFoundException::ERROR;
            $message = TaskNotFoundException::MESSAGE;
            $statusCode = 404;
        }


        $view = View::create([
            'error' => $error,
            'message' => $message
        ], $statusCode);

        $response = $this->viewHandler->handle($view);
        $event->setResponse($response);

    }
}
