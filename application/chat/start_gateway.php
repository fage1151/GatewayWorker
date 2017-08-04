<?php

use \Workerman\Worker;
use \GatewayWorker\Gateway;
use \Workerman\Autoloader;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';
Autoloader::setRootPath(__DIR__);

// gateway 进程，这里使用Text协议，可以用telnet测试
$gateway = new Gateway("text://0.0.0.0:8282");
// gateway名称，status方便查看
$gateway->name = 'gateway';
// gateway进程数
$gateway->count = 5;
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = '127.0.0.1';
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
$gateway->startPort = 2900;
// 服务注册地址
$gateway->registerAddress = '127.0.0.1:1238';
// 心跳间隔 单位：秒
$gateway->pingInterval = 20;
//客户端连续$pingNotResponseLimit次$pingInterval时间内不回应心跳则断开链接
//$gateway->pingNotResponseLimit = 2;
// 心跳数据
$gateway->pingData = '{"type":"ping"}'; //'{"type":"ping"}';
// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

