<?php

include('MemcacheSASL.php');

$m = new MemcacheSASL;
$m->addServer(getenv('MEMCACHIER_SERVERS'));
$m->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));

var_dump($m->add('test', '123'));
echo $m->get('test');
$m->delete('test');

