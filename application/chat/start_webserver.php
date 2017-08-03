<?php

use \Workerman\Worker;

use \Workerman\WebServer;
use \Workerman\Autoloader;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';
Autoloader::setRootPath(__DIR__);


// bussinessWorker 进程
$worker = new WebServer('http://0.0.0.0:9999');
$worker->addRoot('0.0.0.0',realpath(__DIR__ . '/../web/'));
// worker名称
$worker->name = 'chat_web';
// bussinessWorker进程数量
$worker->count = 4;
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

