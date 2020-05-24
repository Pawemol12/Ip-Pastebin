<?php


namespace App\Enum;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class SecurityEnum {

    CONST REQUIRED_LOGIN_CONTROLLERS = [
      'App\Controller\Admin'
    ];

    CONST REQUIRED_LOGIN_CONTROLLER_ACTIONS = [
        'App\Controller\PasteController' => [
            'pasteEdit',
            'pasteDeleteMine',
            'pasteDelete',
            'myPasteList',
            'myPasteListSearch',
            'pasteList',
            'pasteListSearch',
        ]
    ];

    CONST CONTROLLER_ACCESS_BY_ROLE = [
        'App\Controller\Admin' => [UserRolesEnum::USER_ROLE_ADMIN],
    ];

    CONST CONTROLLER_ACTIONS_ACCESS_BY_ROLE = [
        'App\Controller\PasteController' => [
            'actions' => [
                'pasteList',
                'pasteListSearch',
                'pasteDelete'
            ],
            'roles' => [UserRolesEnum::USER_ROLE_ADMIN]
        ]
    ];

    public static function checkControllerRequireLogin(string $controller)
    {
        foreach(self::REQUIRED_LOGIN_CONTROLLERS as $requiredLoginController)
        {
            if (strpos($controller, $requiredLoginController) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function checkControllerActionRequireLogin(string $controller, string $action)
    {
        foreach(self::REQUIRED_LOGIN_CONTROLLER_ACTIONS as $requiredLoginController => $actions)
        {
            if (strpos($controller, $requiredLoginController) !== false) {
                foreach ($actions as $requiredAction) {
                    if ($action == $requiredAction) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function checkControllerAccessByUserRoles(UserInterface $user, string $controller) {
        $userRoles = $user->getRoles();

        foreach(self::CONTROLLER_ACCESS_BY_ROLE as $controllerAccessKey => $requiredRoles) {
            if (strpos($controller, $controllerAccessKey) !== false) {
                $result = array_intersect($requiredRoles, $userRoles);
                if (empty($result))
                {
                    return false;
                }
            }
        }

        return true;
    }

    public static function checkControllerActionAccessByUserRoles(UserInterface $user, string $controller, string $action) {
        $userRoles = $user->getRoles();

        foreach(self::CONTROLLER_ACTIONS_ACCESS_BY_ROLE as $controllerAccessKey => $info) {
            if (strpos($controller, $controllerAccessKey) !== false) {
                $requiredRoles = $info['roles'];
                foreach($info['actions'] as $requiredAction) {
                    if ($requiredAction == $action) {
                        $result = array_intersect($requiredRoles, $userRoles);
                        if (empty($result))
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}