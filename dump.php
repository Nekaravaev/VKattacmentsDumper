<?php
require_once 'vendor/autoload.php';
set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

/*
* Use https://vk.cc/5WLbhs for access_token obtain
* See config.json.example for structure
*/
try {
    $dumper = new VKDumper\Dumper(file_get_contents('./config.json'));
    $dumper->startBenchmark();
    $dumper->getPhotos('0');
    echo $dumper->endBenchmark();
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage();
}
