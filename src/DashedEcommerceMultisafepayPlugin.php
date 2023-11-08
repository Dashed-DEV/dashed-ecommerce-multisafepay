<?php

namespace Dashed\DashedEcommerceMultiSafePay;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedEcommerceMultiSafePay\Filament\Pages\Settings\MultiSafePaySettingsPage;

class DashedEcommerceMultisafepayPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-ecommerce-multisafepay';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                MultiSafePaySettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {

    }
}
