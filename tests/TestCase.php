<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Qubiqx\QcommerceEcommerceMultiSafePay\QcommerceEcommerceMultiSafePayServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Qubiqx\\QcommerceEcommerceMultiSafePay\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            QcommerceEcommerceMultiSafePayServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_qcommerce-ecommerce-multisafepay_table.php.stub';
        $migration->up();
        */
    }
}
