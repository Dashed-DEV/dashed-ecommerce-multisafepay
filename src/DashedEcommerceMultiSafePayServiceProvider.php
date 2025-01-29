<?php

namespace Dashed\DashedEcommerceMultiSafePay;

use Spatie\LaravelPackageTools\Package;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedEcommerceMultiSafePay\Classes\MultiSafePay;
use Dashed\DashedEcommerceMultiSafePay\Commands\SyncMultiSafePayPaymentMethodsCommand;
use Dashed\DashedEcommerceMultiSafePay\Filament\Pages\Settings\MultiSafePaySettingsPage;

class DashedEcommerceMultiSafePayServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-ecommerce-multisafepay';

    public function bootingPackage()
    {
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command(SyncMultiSafePayPaymentMethodsCommand::class)->daily();
        });
    }

    public function configurePackage(Package $package): void
    {
        cms()->builder(
            'settingPages',
            array_merge(cms()->builder('settingPages'), [
                'multisafepay' => [
                    'name' => 'MultiSafePay',
                    'description' => 'Link MultiSafePay aan je webshop',
                    'icon' => 'banknotes',
                    'page' => MultiSafePaySettingsPage::class,
                ],
            ])
        );

        ecommerce()->builder(
            'paymentServiceProviders',
            array_merge(ecommerce()->builder('paymentServiceProviders'), [
                'multisafepay' => [
                    'name' => 'MultiSafePay',
                    'class' => MultiSafePay::class,
                ],
            ])
        );

        $package
            ->name('dashed-ecommerce-multisafepay')
            ->hasCommands([
                SyncMultiSafePayPaymentMethodsCommand::class,
            ]);

        cms()->builder('plugins', [
            new DashedEcommerceMultisafepayPlugin(),
        ]);
    }
}
