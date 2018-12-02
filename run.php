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

if (isset($arguments['file'])) {
    try {
        $command = new AccessReader();
        $command->process($arguments['file']);
    } catch (\Exception $e) {
        \cli\err($e->getMessage());
    }
} else {
    \cli\err('There is no path to log file');
}