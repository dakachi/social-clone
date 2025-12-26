<?php
@error_reporting(0);
@ini_set('display_errors', 0); 
@ini_set('display_startup_errors', 0);

if (file_exists(__DIR__.'/../.env') && str_contains(file_get_contents(__DIR__.'/../.env'), 'APP_INSTALLED=true')) {
    header('Location: /');
    exit;
}

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/InstallerApp.php';

$app = new InstallerApp;
$app->run();
