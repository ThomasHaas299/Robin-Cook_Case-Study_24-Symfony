<?php

namespace App\Tests\EventListener;

use App\EventListener\ExceptionListener;
use App\Exceptions\NoTaskAvailableException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    /** @var MockObject&ViewHandlerInterface */
    private ViewHandlerInterface $viewHandler;
    private ExceptionListener $listener;
    private HttpKernelInterface $kernel;
    private Request $request;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->viewHandler = $this->createMock(ViewHandlerInterface::class);
        $this->listener = new ExceptionListener($this->viewHandler);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->request = $this->createMock(Request::class);
    }

    /**
     * @throws Exception
     */
    public function testOnKernelExceptionWithNoTaskAvailableException(): void
    {
        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (View $view) {
                $data = $view->getData();

                return NoTaskAvailableException::ERROR === $data['error'] && NoTaskAvailableException::MESSAGE === $data['message'];
            }))
            ->willReturn(new Response(NoTaskAvailableException::ERROR, 404));

        $exception = new NoTaskAvailableException();
        $event = new ExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(NoTaskAvailableException::ERROR, $response->getContent());
    }

    /**
     * @throws Exception
     */
    public function testOnKernelExceptionWithNotFoundHttpException(): void
    {
        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (View $view) {
                $data = $view->getData();

                return 'Not Found' === $data['error'] && 'The requested resource could not be found.' === $data['message'];
            }))
            ->willReturn(new Response('Not Found', 404));

        $exception = new NotFoundHttpException();
        $event = new ExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getContent());
    }

    /**
     * @throws Exception
     */
    public function testOnKernelExceptionWithGenericException(): void
    {
        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (View $view) {
                $data = $view->getData();

                return 'An error occurred' === $data['error'] && 'Generic error' === $data['message'];
            }))
            ->willReturn(new Response('An error occurred', 500));

        $exception = new \Exception('Generic error');
        $event = new ExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $this->listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('An error occurred', $response->getContent());
    }
}
