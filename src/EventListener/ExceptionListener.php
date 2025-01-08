<?php

namespace App\EventListener;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

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
        }

        $view = View::create([
            'error' => $error,
            'message' => $message
        ], $statusCode);

        $response = $this->viewHandler->handle($view);
        $event->setResponse($response);

    }
}
