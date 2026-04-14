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

        cms()->registerSettingsDocs(
            page: \Dashed\DashedEcommerceMultiSafePay\Filament\Pages\Settings\MultiSafePaySettingsPage::class,
            title: 'MultiSafepay instellingen',
            intro: 'Koppel de webshop aan MultiSafepay voor betalingen via iDEAL, creditcards en tientallen andere methodes. MultiSafepay is een van de simpelste integraties: je hebt alleen een API sleutel nodig. Deze instelling staat per site, zodat elke webshop een eigen MultiSafepay account kan gebruiken.',
            sections: [
                [
                    'heading' => 'Wat kun je hier instellen?',
                    'body' => 'Per site vul je hier een API sleutel in. Of je in test of live modus werkt wordt automatisch bepaald door het type sleutel dat je gebruikt: een test account geeft een test sleutel, een live account een live sleutel.',
                ],
                [
                    'heading' => 'Hoe zet je MultiSafepay op?',
                    'body' => <<<MARKDOWN
1. Maak een account aan op [multisafepay.com](https://www.multisafepay.com).
2. Begin met een **test account** zodat je veilig kunt proefdraaien zonder echte betalingen.
3. Log in op het merchant dashboard en ga naar **Settings > API Keys**.
4. Kopieer de **API key** en plak die hieronder.
5. Doe een complete testbestelling in je webshop om te controleren dat alles werkt.
6. Maak vervolgens een live account aan, doorloop de verificatie en vervang de test sleutel door de live sleutel.
MARKDOWN,
                ],
            ],
            fields: [
                'API sleutel' => 'De API sleutel van je MultiSafepay account. Je haalt deze op in het merchant dashboard onder Settings, sectie API Keys. De sleutel zelf bepaalt of je in test of live modus werkt, dus let goed op welke je hier plakt.',
            ],
            tips: [
                'Begin met een test account en testbestelling voordat je een live sleutel invult.',
                'Wissel niet halverwege een livegang tussen test en live sleutels, dat geeft verwarrende rapportages in MultiSafepay.',
                'Werken betalingen ineens niet meer? Controleer dan eerst of de sleutel nog actief is in het MultiSafepay dashboard.',
            ],
        );
    }

    public function configurePackage(Package $package): void
    {
        cms()->registerSettingsPage(MultiSafePaySettingsPage::class, 'MultiSafePay', 'banknotes', 'Link MultiSafePay aan je webshop');

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
