<?php

namespace Dashed\DashedEcommerceMultiSafePay;

use Filament\PluginServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Dashed\DashedEcommerceMultiSafePay\Commands\SyncMultiSafePayPaymentMethodsCommand;
use Dashed\DashedEcommerceMultiSafePay\Filament\Pages\Settings\MultiSafePaySettingsPage;
use Dashed\DashedEcommerceMultiSafePay\Classes\MultiSafePay;
use Spatie\LaravelPackageTools\Package;

class DashedEcommerceMultiSafePayServiceProvider extends PluginServiceProvider
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
            ->name('dashed-ecommerce-multisafepay')
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
