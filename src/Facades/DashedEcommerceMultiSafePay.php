<?php

namespace Dashed\DashedEcommerceMultiSafePay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dashed\DashedEcommerceMultiSafePay\DashedEcommerceMultiSafePay
 */
class DashedEcommerceMultiSafePay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashed-ecommerce-multisafepay';
    }
}
