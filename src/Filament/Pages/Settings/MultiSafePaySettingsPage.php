<?php

namespace Dashed\DashedEcommerceMultiSafePay\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Dashed\DashedCore\Classes\Sites;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Dashed\DashedCore\Models\Customsetting;
use Filament\Infolists\Components\TextEntry;
use Dashed\DashedCore\Traits\HasSettingsPermission;
use Dashed\DashedEcommerceCore\Models\OrderPayment;
use Dashed\DashedEcommerceMultiSafePay\Classes\MultiSafePay;

class MultiSafePaySettingsPage extends Page
{
    use HasSettingsPermission;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'MultiSafePay';

    protected string $view = 'dashed-core::settings.pages.default-settings';
    public array $data = [];

    public function mount(): void
    {
        //        MultiSafePay::getOrderStatus(OrderPayment::latest()->first());
        //        MultiSafePay::syncPaymentMethods();
        $formData = [];
        $sites = Sites::getSites();
        foreach ($sites as $site) {
            $formData["multisafepay_api_key_{$site['id']}"] = Customsetting::get('multisafepay_api_key', $site['id']);
            $formData["multisafepay_connected_{$site['id']}"] = Customsetting::get('multisafepay_connected', $site['id']);
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $sites = Sites::getSites();
        $tabGroups = [];

        $tabs = [];
        foreach ($sites as $site) {
            $newSchema = [
                TextEntry::make("MultiSafePay voor {$site['name']}")
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextEntry::make("MultiSafePay is " . (! Customsetting::get('multisafepay_connected', $site['id'], 0) ? 'niet' : '') . ' geconnect')
                    ->state(Customsetting::get('multisafepay_connection_error', $site['id'], ''))
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),
                TextInput::make("multisafepay_api_key_{$site['id']}")
                    ->label('MultiSafePay API key')
                    ->maxLength(255),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($newSchema)
                ->columns([
                    'default' => 1,
                    'lg' => 2,
                ]);
        }
        $tabGroups[] = Tabs::make('Sites')
            ->tabs($tabs);

        return $schema->schema($tabGroups)
            ->statePath('data');
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

        Notification::make()
            ->title('De MultiSafePay instellingen zijn opgeslagen')
            ->success()
            ->send();

        return redirect(MultiSafePaySettingsPage::getUrl());
    }
}
