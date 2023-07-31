<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Qubiqx\QcommerceEcommercePaynl\QcommerceEcommerceMultiSafePay
 */
class QcommerceEcommerceMultiSafePay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qcommerce-ecommerce-multisafepay';
    }
}
