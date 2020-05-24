<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Enum\AccessDeniedExceptionEnum;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Enum\SecurityEnum;

/**
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
class ControllerListener {
    
    /**
     * @var RouterInterface 
     */
    private $router;
    
    /**
     * @var TokenInterface
     */
    private $securityToken;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param RouterInterface $router
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker) {
        $this->router = $router;
        $this->securityToken = $tokenStorage->getToken();
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param ControllerEvent $event
     * @throws AccessDeniedHttpException
     */
    public function onKernelController(ControllerEvent $event): void {
        if (!$this->securityToken) {
            return;
        }

        $controllerInfo = $event->getController();

        if (!is_array($controllerInfo)) {

            return;
        }
        $controllerPath = get_class($controllerInfo[0]);
        $action = $controllerInfo[1];

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (SecurityEnum::checkControllerRequireLogin($controllerPath))
            {
                throw new AccessDeniedHttpException(AccessDeniedExceptionEnum::REQUIRED_LOGIN);
            } else if (SecurityEnum::checkControllerActionRequireLogin($controllerPath, $action))
            {
                throw new AccessDeniedHttpException(AccessDeniedExceptionEnum::REQUIRED_LOGIN);
            }
        } else { //Zalogowani użytkownicy
            $currentUser = $this->securityToken->getUser();
            if (!SecurityEnum::checkControllerAccessByUserRoles($currentUser, $controllerPath))
            {
                throw new AccessDeniedHttpException(AccessDeniedExceptionEnum::ACCESS_FORBIDDEN);
            } elseif (!SecurityEnum::checkControllerActionAccessByUserRoles($currentUser, $controllerPath, $action))
            {
                throw new AccessDeniedHttpException(AccessDeniedExceptionEnum::ACCESS_FORBIDDEN);
            }
        }
    }
}
