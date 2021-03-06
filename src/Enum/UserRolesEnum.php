<?php

declare(strict_types=1);

namespace App\Enum;


/**
 * User Roles Enum
 *
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
abstract class UserRolesEnum {
    const USER_ROLE_ADMIN = 'ROLE_ADMIN';
    const USER_ROLE_USER = 'ROLE_USER';

    public const CHOICES = [
        'userRoles.role_user' => self::USER_ROLE_USER,
        'userRoles.role_admin' => self::USER_ROLE_ADMIN,
    ];
}