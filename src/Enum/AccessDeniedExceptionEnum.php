<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statusy wyjątków controller listenera
 * 
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
abstract class AccessDeniedExceptionEnum {
    const REDIRECT_TO_HOMEPAGE = 'redirectToHomePage';
    const REDIRECT_TO_LOGIN_PAGE = 'redirectToLoginRoute';
    const ACCESS_FORBIDDEN = 'accessForbidden';
    const REQUIRED_LOGIN = 'requiredLogin';
}
