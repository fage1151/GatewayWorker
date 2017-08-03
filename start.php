<?php
/**
 * run with command
 * php start.php start
 */

use Workerman\Worker;

// 检查扩展
if(!extension_loaded('swoole'))
{
    exit("Please install swoole extension. \n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. \n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';

// 加载所有Applications/*/start.php，以便启动所有服务
foreach(glob(__DIR__.'/application/*/start*.php') as $start_file)
{
    require_once $start_file;
}
// 运行所有服务
Worker::runAll();