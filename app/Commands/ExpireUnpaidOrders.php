<?php

namespace App\Commands;

use App\Libraries\OrderCancellationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ExpireUnpaidOrders extends BaseCommand
{
    protected $group = 'Orders';
    protected $name = 'orders:expire';
    protected $description = 'Cancel expired unpaid orders and return their stock.';

    public function run(array $params)
    {
        $cancelled = (new OrderCancellationService())->expireStaleOrders();
        CLI::write("Cancelled {$cancelled} expired order(s).", 'green');
    }
}
