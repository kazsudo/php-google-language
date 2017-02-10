<?php
require __DIR__.'/vendor/autoload.php';

use KazSudo\Google\Language\AnalyzeCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AnalyzeCommand());
$application->run();
