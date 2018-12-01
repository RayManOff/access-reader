<?php
use App\AccessReader;

require __DIR__ . '/vendor/autoload.php';

$arguments = new \cli\Arguments();
$arguments->addOption(['file', 'f'], [
    'default'     => '',
    'description' => 'Path to access log file'
]);

$arguments->parse();
$arguments = $arguments->getArguments();

$command = new AccessReader();
$command->process($arguments['file']);