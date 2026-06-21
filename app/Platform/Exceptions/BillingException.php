<?php

namespace App\Platform\Exceptions;

class BillingException extends \RuntimeException
{
    public function __construct(string $message, public readonly string $code = 'billing_blocked')
    {
        parent::__construct($message);
    }
}
