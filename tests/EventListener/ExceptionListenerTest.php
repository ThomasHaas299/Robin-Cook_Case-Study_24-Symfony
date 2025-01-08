<?php

namespace App\Tests\EventListener;

use App\EventListener\ExceptionListener;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testOnKernelExceptionWithNotFoundHttpException()
    {
        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (View $view) {
                $data = $view->getData();
                return $data['error'] === 'Not Found' && $data['message'] === 'The requested resource could not be found.';
            }))
            ->willReturn(new Response('Not Found', 404));

        $listener = new ExceptionListener($viewHandler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $exception = new NotFoundHttpException();
        $event = new ExceptionEvent($kernel, $this->createMock(Request::class), HttpKernelInterface::MAIN_REQUEST, $exception);

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getContent());
    }

    /**
     * @throws Exception
     */
    public function testOnKernelExceptionWithGenericException()
    {
        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (View $view) {
                $data = $view->getData();
                return $data['error'] === 'An error occurred' && $data['message'] === 'Generic error';
            }))
            ->willReturn(new Response('An error occurred', 500));

        $listener = new ExceptionListener($viewHandler);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $exception = new \Exception('Generic error');
        $event = new ExceptionEvent($kernel, $this->createMock(Request::class), HttpKernelInterface::MAIN_REQUEST, $exception);

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('An error occurred', $response->getContent());
    }
}