<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

enum Roles: string
{
    case ADMIN = 'Admin';
    case AGENT = 'Agent';
    case CUSTOMER = 'Customer';
}