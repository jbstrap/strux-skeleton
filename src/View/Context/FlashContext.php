<?php

declare(strict_types=1);

namespace App\View\Context;

use Strux\Component\View\Context\ContextInterface;
use Strux\Support\Helpers\FlashServiceInterface;

class FlashContext implements ContextInterface
{
    private FlashServiceInterface $flash;

    public function __construct(FlashServiceInterface $flash)
    {
        $this->flash = $flash;
    }

    public function process(): array
    {
        return [
            'flash' => $this->flash,
        ];
    }
}