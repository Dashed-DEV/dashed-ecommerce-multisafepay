<?php

namespace Qubiqx\QcommerceEcommerceMultiSafePay\Commands;

use Illuminate\Console\Command;
use Qubiqx\QcommerceEcommercePaynl\Classes\MultiSafePay;
use Qubiqx\QcommerceEcommercePaynl\Classes\PayNL;

class SyncMultiSafePayPaymentMethodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qcommerce:sync-multisafepay-payment-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MultiSafePay payment methods';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        MultiSafePay::syncPaymentMethods();
    }
}
