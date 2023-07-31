<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay\Classes;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use MultiSafepay\Api\TransactionManager;
use Qubiqx\QcommerceCore\Classes\Locales;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceCore\Classes\Countries;
use Qubiqx\QcommerceEcommerceCore\Models\OrderPayment;
use Qubiqx\QcommerceEcommerceCore\Models\PaymentMethod;
use Qubiqx\QcommerceTranslations\Models\Translation;

class MultiSafePay
{
    public $manager;

//    public static function initialize($siteId = null)
//    {
//        if (! $siteId) {
//            $siteId = Sites::getActive();
//        }
//
//        \Paynl\Config::setApiToken(Customsetting::get('paynl_at_hash', $siteId));
//        \Paynl\Config::setServiceId(Customsetting::get('paynl_sl_code', $siteId));
//    }

    public static function isConnected($siteId = null)
    {
        if (!$siteId) {
            $siteId = Sites::getActive();
        }

        try {
            return Http::get('https://api.multisafepay.com/v1/json/payment-methods', [
                'api_key' => Customsetting::get('multisafepay_api_key', $siteId),
            ])
                ->json()['success'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function syncPaymentMethods($siteId = null)
    {
        $site = Sites::get($siteId);

        if (!Customsetting::get('multisafepay_connected', $site['id'])) {
            return;
        }

        try {
            $allPaymentMethods = Http::get('https://api.multisafepay.com/v1/json/payment-methods', [
                'api_key' => Customsetting::get('multisafepay_api_key', $site['id']),
            ])
                ->json()['data'] ?? [];
        } catch (Exception $exception) {
            $allPaymentMethods = [];
        }

        foreach ($allPaymentMethods as $allPaymentMethod) {
            if (!PaymentMethod::where('psp', 'multisafepay')->where('psp_id', $allPaymentMethod['id'])->count()) {
                $image = file_get_contents($allPaymentMethod['icon_urls']['large']);
                $imagePath = '/qcommerce/payment-methods/multisafepay/' . $allPaymentMethod['id'] . '.png';
                Storage::put($imagePath, $image);

                $paymentMethod = new PaymentMethod();
                $paymentMethod->site_id = $site['id'];
                $paymentMethod->available_from_amount = $allPaymentMethod['allowed_amount']['min'] ?? 0;
                $paymentMethod->psp = 'multisafepay';
                $paymentMethod->psp_id = $allPaymentMethod['id'];
                $paymentMethod->image = $imagePath;
                foreach (Locales::getLocales() as $locale) {
                    $paymentMethod->setTranslation('name', $locale['id'], $allPaymentMethod['name']);
                }
                $paymentMethod->save();
            }
        }
    }

    public static function startTransaction(OrderPayment $orderPayment)
    {
        $orderPayment->psp = 'multisafepay';
        $orderPayment->save();

        $siteId = Sites::getActive();

        $transaction = Http::withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])
            ->post('https://api.multisafepay.com/v1/json/orders?api_key=' . Customsetting::get('multisafepay_api_key', $siteId), [
                'type' => 'redirect',
                'gateway' => $orderPayment->paymentMethod->psp_id,
                'order_id' => $orderPayment->order->hash,
                'currency' => 'EUR',
                'amount' => $orderPayment->amount * 100,
                'description' => Translation::get('order-by-store', 'orders', 'Order by :storeName:', 'text', [
                    'storeName' => Customsetting::get('site_name'),
                ]),
                'payment_options' => [
                    'notification_method' => 'POST',
                    'notification_url' => route('qcommerce.frontend.checkout.exchange'),
                    'redirect_url' => route('qcommerce.frontend.checkout.complete') . '?orderId=' . $orderPayment->order->hash . '&paymentId=' . $orderPayment->hash,
                    'cancel_url' => url('/')
                ]
            ])
            ->json();

        $orderPayment->psp_id = $transaction['data']['order_id'];
        $orderPayment->save();

        return [
            'transaction' => $transaction,
            'redirectUrl' => $transaction['data']['payment_url'],
        ];
    }

    public static function getOrderStatus(OrderPayment $orderPayment)
    {
        $payment = Http::get('https://api.multisafepay.com/v1/json/orders/' . $orderPayment->psp_id, [
            'api_key' => Customsetting::get('multisafepay_api_key', $orderPayment->order->site_id),
        ])
            ->json();

        $paymentStatus = $payment['data']['status'] ?? 'pending';

        if ($paymentStatus == 'completed') {
            return 'paid';
        } elseif ($paymentStatus == 'refunded') {
            return 'refunded';
        } elseif ($paymentStatus == 'cancelled') {
            return 'cancelled';
        } else {
            return 'pending';
        }
    }
}
