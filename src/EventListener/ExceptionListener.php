<?php

namespace App\EventListener;

use App\Enum\AccessDeniedExceptionEnum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

/**
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
class ExceptionListener {

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @param RouterInterface $router
     * @param KernelInterface $kernel
     * @param Environment $twig
     */
    public function __construct(RouterInterface $router, KernelInterface $kernel, Environment $twig) {
        $this->router = $router;
        $this->kernel = $kernel;
        $this->twig = $twig;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void {
        $exception = $event->getThrowable();
        //Dla dev i prod
        if ($exception instanceof AccessDeniedHttpException || $exception->getCode() == Response::HTTP_FORBIDDEN) {
            switch ($exception->getMessage()) {
                case AccessDeniedExceptionEnum::ACCESS_FORBIDDEN: {
                    $event->setResponse(new Response($this->twig->render('errors/error403.html.twig')));
                    break;
                }
                case AccessDeniedExceptionEnum::REQUIRED_LOGIN: {
                    $event->setResponse(new Response($this->twig->render('errors/requiredLogin.html.twig')));
                    break;
                }
                case AccessDeniedExceptionEnum::REDIRECT_TO_LOGIN_PAGE: {
                    $event->setResponse(new RedirectResponse($this->router->generate('login')));
                    break;
                }
                case AccessDeniedExceptionEnum::REDIRECT_TO_HOMEPAGE: {
                    $event->setResponse(new RedirectResponse($this->router->generate('index')));
                    break;
                }
            }
        } else {
            //Dla prod
            if (method_exists($exception, 'getStatusCode') && $this->kernel->getEnvironment() != 'dev') {
                $statusCode = $exception->getStatusCode();
                switch ($statusCode) {
                    case Response::HTTP_NOT_FOUND:
                        $event->setResponse(new Response($this->templatingEngine->render('errors/error' . $statusCode . '.html.twig')));
                        break;
                    default: {
                        $event->setResponse(new Response($this->templatingEngine->render('errors/error.html.twig', ['errorNumber' => $exception->getCode()
                        ])));
                        break;
                    }
                }
            } else {
                //Dla dev
                $response = new Response();
                $response->setContent($exception->getMessage());

                if ($exception instanceof HttpExceptionInterface) {
                    $response->setStatusCode(method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : $exception->getCode());
                    $response->headers->replace($exception->getHeaders());
                } else {
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                if($this->kernel->getEnvironment() == 'dev') {
                    dump($exception);
                } else {
                    $event->setResponse(new Response($this->templatingEngine->render('CoreBundle:Exception:error.html.twig', ['errorNumber' => $exception->getCode()])));
                }
            }
        }
    }
}