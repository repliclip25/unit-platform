<?php

namespace App\Platform\Exceptions;

class BillingException extends \RuntimeException
{
    public string $billingCode;

    public function __construct(string $message, string $code = 'billing_blocked')
    {
        $this->billingCode = $code;
        parent::__construct($message);
    }
}
