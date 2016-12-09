<?php
require_once 'classes/Dumper.php';
/*
* Use https://vk.cc/5WLbhs for access_token obtain
* See config.json.example for structure
*/
try {
    $dumper = new Dumper(file_get_contents('config.json'));
    $dumper->startBenchmark();
    $dumper->getPhotos('0');
    $dumper->endBenchmark();
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage();
}
