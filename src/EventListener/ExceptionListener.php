<?php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

class ExceptionListener
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Get the exception object from the event
        $exception = $event->getThrowable();

        // Redirect to home page for all exceptions
        dd("jh");
        $response = new RedirectResponse($this->router->generate('/dashboard'));
        $event->setResponse($response);
    }
}
