<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Qubiqx\QcommerceEcommerceMultiSafePay\Commands\SyncMultiSafePayPaymentMethodsCommand;
use Qubiqx\QcommerceEcommerceMultiSafePay\Filament\Pages\Settings\MultiSafePaySettingsPage;
use Qubiqx\QcommerceEcommerceMultiSafePay\Classes\MultiSafePay;
use Spatie\LaravelPackageTools\Package;

class QcommerceEcommerceMultiSafePayServiceProvider extends PluginServiceProvider
{
    public static string $name = 'qcommerce-ecommerce-multisafepay';

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
                    'icon' => 'cash',
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
            ->name('qcommerce-ecommerce-multisafepay')
            ->hasCommands([
                SyncMultiSafePayPaymentMethodsCommand::class,
            ]);
    }

    protected function getPages(): array
    {
        return array_merge(parent::getPages(), [
            MultiSafePaySettingsPage::class,
        ]);
    }
}
