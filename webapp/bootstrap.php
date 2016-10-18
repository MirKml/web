<?php
require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// "192.168.56.1" is the IP for the virtualbox host IP address
$configurator->setDebugMode(["127.0.0.1", "192.168.56.1", "94.112.21.42"]); // enable for your remote IP
// enable only when there isn't obvious error reason from normal apache log
// because tracy logs aren't rotated
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
