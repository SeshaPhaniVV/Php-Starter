<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Capsule\Manager as DbManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        DbManager::class => function (ContainerInterface $c) {
            $capsule = new Manager;
            $settings = $c->get('settings');

            $capsule->addConnection($settings['db']);

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        }
    ]);
};
