<?php

use Amne\Phpasync\App;
use GuzzleHttp\Client;

require_once dirname(__DIR__).'/vendor/autoload.php';

$app = new App(new Client());

echo print_r($app->testAsync(), true);
