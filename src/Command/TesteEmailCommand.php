<?php

// src/Command/TesteEmailCommand.php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use App\Controller\OrdersController;

class TesteEmailCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        
        $controller = new OrdersController();
        $controller->initialize();
        $controller->testSendEmailNat(); // Sua action
    }
}