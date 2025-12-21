<?php

namespace App\Http\Request;

use Strux\Component\Http\Request\FormRequest;
use Strux\Component\Validation\Rules\MinLength;
use Strux\Component\Validation\Rules\Required;

class TicketCreateRequest extends FormRequest
{
    public string $subject;
    public string $description;

    protected function rules(): array
    {
        return [
            'subject' => [new Required(), new MinLength(5)],
            'description' => [new Required(), new MinLength(5)]
        ];
    }
}