<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay\Filament\Pages\Settings;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Qubiqx\QcommerceCore\Classes\Sites;
use Qubiqx\QcommerceCore\Models\Customsetting;
use Qubiqx\QcommerceEcommerceCore\Models\OrderPayment;
use Qubiqx\QcommerceEcommerceMultiSafePay\Classes\MultiSafePay;

class MultiSafePaySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'MultiSafePay';

    protected static string $view = 'qcommerce-core::settings.pages.default-settings';

    public function mount(): void
    {
        MultiSafePay::getOrderStatus(OrderPayment::latest()->first());
//        MultiSafePay::syncPaymentMethods();
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["multisafepay_api_key_{$site['id']}"] = Customsetting::get('multisafepay_api_key', $site['id']);
            $formData["multisafepay_connected_{$site['id']}"] = Customsetting::get('multisafepay_connected', $site['id']);
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $schema = [
                Placeholder::make('label')
                    ->label("MultiSafePay voor {$site['name']}")
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                Placeholder::make('label')
                    ->label("MultiSafePay is " . (! Customsetting::get('multisafepay_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->content(Customsetting::get('multisafepay_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("multisafepay_api_key_{$site['id']}")
                    ->label('MultiSafePay API key')
                    ->rules([
                        'max:255',
                    ]),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($schema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $tabGroups;
    }

    public function submit()
    {
        $sites = Sites::getSites();

        foreach ($sites as $site) {
            Customsetting::set('multisafepay_api_key', $this->form->getState()["multisafepay_api_key_{$site['id']}"], $site['id']);
            Customsetting::set('multisafepay_connected', MultiSafePay::isConnected($site['id']), $site['id']);

            if (Customsetting::get('multisafepay_connected', $site['id'])) {
                MultiSafePay::syncPaymentMethods($site['id']);
            }
        }

        $this->notify('success', 'De MultiSafePay instellingen zijn opgeslagen');

        return redirect(MultiSafePaySettingsPage::getUrl());
    }
}
