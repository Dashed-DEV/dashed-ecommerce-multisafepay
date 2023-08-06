<?php

namespace Dashed\DashedEcommerceMultiSafePay\Commands;

use Illuminate\Console\Command;
use Dashed\DashedEcommerceMultiSafePay\Classes\MultiSafePay;

class SyncMultiSafePayPaymentMethodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashed:sync-multisafepay-payment-methods';

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
