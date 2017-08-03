<?php
use GatewayWorker\Lib\Gateway;

class Events
{
    public static $user = [];
    public static $uuid = [];

    public static function onWorkerStart($businessWorker)
    {   //服务准备就绪
        echo "Worker_socket_ready\n";
    }

    public static function onConnect($client_id)
    {
        //当客户端链接上时触发，这里可以做 session  域名来源排除 ，安全过滤等

    }


    public static function onMessage($client_id, $message)
    {

        /*监听事件，需要把客户端发来的json转为数组*/
        $data = json_decode($message, true);
        switch ($data['type']) {
            //登录事件
            case 'login':
                $username = $data['content']['username'];
                $password = $data['content']['password'];
                $frame_data = [$username,$password];
                $frame_rs = self::process($frame_data);
                Gateway::bindUid($client_id,$frame_rs['userid']);
                $group = '';
                Gateway::joinGroup($client_id,$group);
                Gateway::sendToCurrentClient($frame_rs);
                break;
            //处理单聊事件
            case 'send_to_one':
                if(self::check_login() === false){return ;};
                $all_data['type'] = "addList";
                $all_data['content'] = $data['content'];
                $all_data['content']['type'] = 'friend';
                $uid = $data['content']['to']['id'];
                Gateway::sendToUid($uid,$data['content']);
                break;
            //处理广播事件
            case 'send_to_all':
                if(self::check_login() === false){return ;};
                Gateway::sendToAll($data['content']);
                break;
            case 'send_to_group':
                //处理群聊事件
                $msg['username'] = $data['content']['mine']['username'];
                $msg['avatar'] = $data['content']['mine']['avatar'];
                $msg['id'] = $data['content']['mine']['id'];
                $msg['content'] = $data['content']['mine']['content'];
                $msg['type'] = $data['content']['to']['type'];
                $chatMessage['type'] = 'getMessage';
                $chatMessage['content'] = $msg;
                $group = '123';
                Gateway::sendToGroup($group,$message);
                //处理单聊
                if ($data['content']['to']['type'] == 'friend') {

                    if (isset(self::$uuid[$data['content']['to']['id']])) {
                        Gateway::sendToUid(self::$uuid[$data['content']['to']['id']], json_encode($chatMessage));
                    } else {
                        //处理离线消息
                        $noonline['type'] = 'noonline';
                        Gateway::sendToClient($client_id, json_encode($noonline));
                    }
                } else {
                    //处理群聊
                    $chatMessage['content']['id'] = $data['content']['to']['id'];
                    Gateway::sendToAll(json_encode($chatMessage), '', $client_id);
                }
                break;
            default:
                var_dump($message);
                Gateway::sendToClient($client_id, '{"type":1,"msg":"hello"}');
        }


    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {

        //有用户离线时触发 并推送给全部用户
        $data['type'] = "out";
        $data['id'] = array_search($client_id, self::$user);
        unset(self::$user[$data['id']]);
        unset(self::$uuid[$data['id']]);
        $data['num'] = count(self::$user);
        Gateway::sendToAll(json_encode($data));

    }

    /**
     * 过程处理函数，处理业务框架的入口
     * @param array $data
     * @return Mixed
     */
    protected static function process($data = [])
    {
        $data['fun'] = 'index';
        if (!class_exists('Engine')) {
            $frameworkBootstrap = realpath(__DIR__) . '/../Api/index.php';
            require_once $frameworkBootstrap;
        }
        $engine = new Engine ();
        $engine->setRoutePath($data);
        return $engine->run();
    }
    protected static function check_login()
    {
        if(isset($_SESSION['userid'])){
            return true;
        }
        Gateway::closeCurrentClient();
        return false;
    }
}
