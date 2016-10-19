<?php
require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// "192.168.56.1" is the IP for the virtualbox host IP address
// "94.112.21.42" is the IP address from IPS for mmy home connection
$configurator->setDebugMode(["127.0.0.1", "192.168.56.1", "94.112.21.42"]); // enable for your remote IP
// be aware for log growing, so setup e.g. logrotate
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
