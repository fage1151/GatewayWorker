<?php

/**
 * Class Library_Model_User
 *
 * @author PhpGame
 */
class Library_Model_User extends Library_Model_Base
{

    const USER_TABLE = 'user';
    const USER_LOGIN_TABLE = 'user_login';

    /**
     * @return Library_Model_User
     */
    static public function Instance()
    {
        return parent::InstanceInternal(__CLASS__);
    }

    /**
     * @param $username
     * @param $password
     * @return null|string
     */
    public function check_user($username,$password)
    {
        return $this->dbGamecity->slave()
            ->select('count(*)')
            ->from('user')
            ->where(['username'=>$username,'password'=>$password])
            ->getOne();
    }
    /**
     * @param $username
     * @param $password
     * @return null|string
     */
    public function update_user_login_info($username)
    {
        return $this->dbGamecity->master()
            ->update('user',[],['username'=>$username]);
    }
    /**
     * 根据用户名查出用户名和密码
     *
     * @param $username 用户名
     *
     * @return Array
     */
    public function getPrintSignByMkey($mkey)
    {
        return $this->dbGamecity->slave()
            ->select('*')
            ->from('user_print')
            ->where(array('mkey' => $mkey))
            ->getRow();
    }

    /**
     * 根据用户ID查出用户信息
     *
     * @param $userid 用户id
     *
     * @return Array
     */
    public function getUserInfoByUserid($userid)
    {
        return $this->dbGamecity->slave()
            ->select('*')
            ->from('user')
            ->where(array('id' => $userid))
            ->getRow();
    }

    /**
     * 根据用户ID查出用户信息
     *
     * @param $userid 用户id
     *
     * @return Array
     */
    public function checkUser($userid, $username)
    {
        return $this->dbGamecity->slave()
            ->select('*')
            ->from('user')
            ->where(array('id' => $userid, 'username' => $username))
            ->getRow();
    }

    public function wirtePrint($arr)
    {
        return $this->dbGamecity->master()
            ->insert('user_print', $arr);
    }


    /**
     * 更新打印机信息 登录
     *
     * @param string $mkey 机器码
     * @param string $ip IP
     *
     * @return Boolean
     */
    public function updatePrintLoginByMkey($mkey, $ip)
    {
        //echo "aaa";
        return $this->dbGamecity->master()
            ->update('user_print',
                array('status' => 0,
                    'online' => 1,
                    'printip' => $ip,
                    'updatetime' => time()),
                array('mkey' => $mkey));
    }

    /**
     * 更新打印机信息 无纸，有纸 0 不在线， 1 在线 2 无纸
     *
     * @param string $id 机器码
     *
     * @return Boolean
     */
    public function updatePrintLoginByid($id, $status = '1')
    {
        return $this->dbGamecity->master()
            ->update('user_print',
                array('status' => 0,
                    'online' => $status,
                    'updatetime' => time()),
                array('id' => $id));
    }

    /**
     * 根据实际情况更新打印机信息
     *
     * @param string $id 机器码
     *
     * @return Boolean
     */
    public function updatePrintInfoByid($id, $cond)
    {
        return $this->dbGamecity->master()
            ->update('user_print',
                $cond,
                array('id' => $id));
    }

    /**
     * 更新打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function insertPrintData($printid, $printdata)
    {
        return $this->dbGamecity->master()
            ->insert('user_print_data',
                array('status' => 0,
                    'user_print_id' => $printid,
                    'printdata' => $printdata,
                    'gettime' => time(),
                    'sendtime' => time()
                ));
    }

    /**
     * 更新打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function updatePrintDataByPrintid($id, $status)
    {

        echo $status;
        $rs = $this->dbGamecity->slave()
            ->select('status')->from('user_print_data')->where(array('id' => $id))->getRow();
        if ($rs['status'] >= $status) {
            return FALSE;
        }
        return $this->dbGamecity->master()
            ->update('user_print_data',
                array('status' => $status,
                    'finishtime' => time()
                ),
                array('id' => $id, 'status<>' => $status));
    }

    /**
     * 登陆获取打印机API数据
     *
     * @param string $printid 机器id
     *
     * @return Boolean
     */
    public function getPrintDataLogin($print_id)
    {
        $note = $this->dbGamecity->slave()
            ->select('id, printdata, status, ctype')
            ->from('user_print_data')
            ->where(array('user_print_id' => $print_id, 'status <' => 3))
            ->order(' id asc')
            ->limit(1)
            ->getRow();

        if (!isset($note['status'])) {
            return false;
        } else {
            $this->updatePrintDataByPrintid($note['id'], '1');
            // $note['printdata'] = 'MSGBEGIN' . $note['printdata'] . 'MSGEND';
            return $note;
        }
    }

    /**
     * 登陆获取打印机API数据
     *
     * @param string $machinecode 机器码
     *
     * @return Boolean
     */
    public function getPrintData($machinecode)
    {
        $note = $this->dbGamecity->slave()
            ->select()
            ->from('user_print')
            ->where(array('mkey' => $machinecode))
            ->getRow();

        return $note;
    }

    /**
     * 通过id获取打印数据状态
     *
     * @param string $id 打印数据ID
     *
     * @return Mix
     */
    public function getPrintDataStatusById($id)
    {
        $note = $this->dbGamecity->slave()
            ->select('status')
            ->from('user_print_data')
            ->where(array('id' => $id))
            ->getRow();

        return $note;
    }

    /**
     * 批量获取打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function getPrintDataByPrintid($id)
    {
        $print_id = $this->dbGamecity->slave()
            ->select('user_print_id')
            ->from('user_print_data')
            ->where(array('id' => $id))
            ->getOne();

        $note = $this->dbGamecity->slave()
            ->select('id, printdata, status, ctype')
            ->from('user_print_data')
            ->where(array('user_print_id' => $print_id, 'status <' => 3))
            ->order(' id asc')
            ->limit(1)
            ->getRow();

        if (isset($note['status'])) {
            if ($note['status'] == '2' || $note['status'] == '1') {
                return false;
            } else {
                $rs = $this->updatePrintDataByPrintid($note['id'], '1');
                if (empty($rs)) {
                    return false;
                }
                // $note['printdata'] = 'MSGBEGIN' . $note['printdata'] . 'MSGEND';
                return $note;
            }
        } else {
            return false;
        }
    }

    /**
     * 批量获取打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function getuserurl($id)
    {
        $print_id = $this->dbGamecity->slave()
            ->select('user_print_id, gettime')
            ->from('user_print_data')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $print_id['user_print_id']))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $note = $this->dbGamecity->slave()
            ->select('id, apikey, conf_url, level')
            ->from('user')
            ->where(array('id' => $user_id['user_id'], 'level >' => 1))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        if (!empty($note['conf_url'])) {
            $note = array('conf_url' => $note['conf_url'] . '\t',
                'print_id' => $user_id['mkey'] . '\t',
                'apikey' => $note['apikey'] . '\t',
                'gettime' => $print_id['gettime'] . '\t'
            );

            return $note;
        }
        return false;
    }

    /**oauth批量获取打印机API数据
     * @param $id
     * @return array|bool
     */
    public function oauthgetuserurl($id){
        $print_id = $this->dbGamecity->slave()
            ->select('user_print_id, finishtime')
            ->from('user_print_data')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $print_id['user_print_id']))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $bind = $this->dbGamecity->slave()
            ->select('client_id,origin_id')
            ->from('oauth_bindprintdata')
            ->where(array('updata_id' => $id))
            ->limit(1)
            ->getRow();

        if(!empty($bind['client_id'])){
            $data = $this->dbGamecity->slave()
                ->select('client_id,client_secret')
                ->from('oauth_clients')
                ->where(array('client_id'=>$bind['client_id']))
                ->limit(1)
                ->getRow();

            $url_data = $this->dbGamecity->slave()
                ->select('url,type,status')
                ->from('oauth_push')
                ->where(array('client_id'=>$bind['client_id']))
                ->limit(1)
                ->getRow();

            $token = $this->dbGamecity->slave()
                ->select('access_token,fresh_time,create_time,expires')
                ->from('oauth_tokens')
                ->where(array('client_id'=>$bind['client_id'],'user_id'=>$user_id['user_id']))
                ->limit(1)
                ->getRow();

            if(!empty($token)){
                if($token['fresh_time'] == 0){
                    $time = intval(time())-intval($token['create_time']);
                    if($time >= $token['expires']){
                        return false;
                    }
                }elseif($token['fresh_time'] >0){
                    $time = intval(time())-intval($token['fresh_time']);
                    if($time >= $token['expires']){
                        return false;
                    }
                }
                if(!empty($url_data)){
                    if($url_data['status'] == 2){
                        return false;
                    }
                    if($url_data['type'] == 3){
                        return false;
                    }
                    $note = array(
                        'conf_url' => $url_data['url'].'\t',
                        'print_id' => $user_id['mkey'].'\t',
                        'client_id' => $data['client_id'].'\t',
                        'client_secret' => $data['client_secret'].'\t',
                        'finishtime' => $print_id['finishtime'].'\t',
                        'origin_id' => $bind['origin_id'].'\t',
                    );
                    //var_dump($note);
                    return $note;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 打印机状态改变推送
     * @param $id 打印机id
     * @return array|bool
     */
    public function printstate($id)
    {
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey,online')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $note = $this->dbGamecity->slave()
            ->select('id, apikey, conf_url, level')
            ->from('user')
            ->where(array('id' => $user_id['user_id'], 'level' => 3))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        if (!empty($note['conf_url'])) {
            $note = array('request_url' => $note['conf_url'] . '\t',
                'print_id' => $user_id['mkey'] . '\t',
                'apikey' => $note['apikey'] . '\t',
                'online' => $user_id['online'] . '\t'
            );

            return $note;
        }
        return false;
    }


    /**oauth打印机状态改变推送
     * @param $id
     * @return array|bool
     */
    public function oauthprintstate($id)
    {
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey,online')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $bind = $this->dbGamecity->slave()
            ->select('client_id,fresh_time,create_time,expires')
            ->from('oauth_tokens')
            ->where(array('user_id'=>$user_id['user_id']))
            ->getAll();
        if(!empty($bind)){
            foreach($bind as $key=>$val){
                if($val['fresh_time'] == 0){
                    $time = intval(time())-intval($val['create_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }elseif($val['fresh_time'] >0){
                    $time = intval(time())-intval($val['fresh_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }
                $data = $this->dbGamecity->slave()
                    ->select('client_id,client_secret')
                    ->from('oauth_clients')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();

                $url_data = $this->dbGamecity->slave()
                    ->select('url,type,status')
                    ->from('oauth_push')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();

                if(!empty($url_data)){
                    if($url_data['status'] == 2){
                        return false;
                    }
                    if($url_data['type'] != 3){
                        return false;
                    }
                    $note = array(
                        'request_url' => $url_data['url'].'\t',
                        'print_id' => $user_id['mkey'].'\t',
                        'client_id' => $data['client_id'].'\t',
                        'client_secret' => $data['client_secret'].'\t',
                        'online' => $user_id['online'].'\t'
                    );
                    var_dump($note);
                    return $note;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }
    
    /**
     * 接收到自定义上报请求
     * @param unknown $id
     */
    public function printMenu($id)
    {
        //用户打印机信息
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        //自定义菜单信息
        $print_menu = $this->dbGamecity->slave()
            ->select('content')
            ->from('printmenu')
            ->where(array('user_print_id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        //用户信息
        $note = $this->dbGamecity->slave()
            ->select('id, apikey, conf_url, level')
            ->from('user')
            ->where(array('id' => $user_id['user_id'], 'level >' => 1))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $note = array('request_url' => $note['conf_url'] . '\t',
            'print_id' => $user_id['mkey'] . '\t',
            'apikey' => $note['apikey'] . '\t',
            'content' => $print_menu['content'],
        );

        return $note;

    }


    public function oauthprintmenu($id)
    {
        //用户打印机信息
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        //自定义菜单信息
        $print_menu = $this->dbGamecity->slave()
            ->select('content')
            ->from('printmenu')
            ->where(array('user_print_id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $bind = $this->dbGamecity->slave()
            ->select('client_id,fresh_time,create_time,expires')
            ->from('oauth_tokens')
            ->where(array('user_id'=>$user_id['user_id']))
            ->getAll();
        if(!empty($bind)){
            foreach($bind as $key=>$val){
                if($val['fresh_time'] == 0){
                    $time = intval(time())-intval($val['create_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }elseif($val['fresh_time'] >0){
                    $time = intval(time())-intval($val['fresh_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }
                $data = $this->dbGamecity->slave()
                    ->select('client_id,client_secret')
                    ->from('oauth_clients')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();

                $url_data = $this->dbGamecity->slave()
                    ->select('url,type,status')
                    ->from('oauth_push')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();
                if(!empty($url_data)){
                    if($url_data['status'] == 2){
                        return false;
                    }
                    if($url_data['type'] != 5){
                        return false;
                    }
                    if(!empty($url_data['url'])){
                        $note = array(
                            'request_url' => $url_data['url'].'\t',
                            'print_id' => $user_id['mkey'].'\t',
                            'client_id' => $data['client_id'] . '\t',
                            'client_secret' => $data['client_secret'] . '\t',
                            'content' => $print_menu['content'],
                        );
                        return $note;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }
    /**
     * 批量获取打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function getrequest($id)
    {
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $note = $this->dbGamecity->slave()
            ->select('id, apikey, conf_url, level')
            ->from('user')
            ->where(array('id' => $user_id['user_id'], 'level >' => 1))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        if (!empty($note['conf_url'])) {
            $note = array('request_url' => $note['conf_url'] . '\t',
                'print_id' => $user_id['mkey'] . '\t',
                'apikey' => $note['apikey'] . '\t'
            );

            return $note;
        }
        return false;
    }


    /**oauth批量获批订单状态
     * @param $id
     * @return array|bool
     */
    public function oauthgetrequest($id)
    {
        $user_id = $this->dbGamecity->slave()
            ->select('user_id, mkey')
            ->from('user_print')
            ->where(array('id' => $id))
            ->order('id asc')
            ->limit(1)
            ->getRow();

        $bind = $this->dbGamecity->slave()
            ->select('client_id,fresh_time,create_time,expires')
            ->from('oauth_tokens')
            ->where(array('user_id'=>$user_id['user_id']))
            ->getAll();

        if(!empty($bind)){
            foreach($bind as $key=>$val){
                if($val['fresh_time'] == 0){
                    $time = intval(time())-intval($val['create_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }elseif($val['fresh_time'] >0){
                    $time = intval(time())-intval($val['fresh_time']);
                    if($time >= $val['expires']){
                        return false;
                    }
                }
                $data = $this->dbGamecity->slave()
                    ->select('client_id,client_secret')
                    ->from('oauth_clients')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();

                $url_data = $this->dbGamecity->slave()
                    ->select('url,type,status')
                    ->from('oauth_push')
                    ->where(array('client_id'=>$val['client_id']))
                    ->limit(1)
                    ->getRow();

                if(!empty($url_data)){
                    if($url_data['status'] == 2){
                        return false;
                    }
                    if($url_data['type'] != 4){
                        return false;
                    }
                    if(!empty($url_data['url'])){
                        $note = array(
                            'request_url' => $url_data['url'].'\t',
                            'print_id' => $user_id['mkey'].'\t',
                            'client_id' => $data['client_id'] . '\t',
                            'client_secret' => $data['client_secret'] . '\t',
                        );
                        var_dump($note);
                        return $note;
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * 批量获取打印机API数据
     *
     * @param string $printid 机器id
     * @param string $printdata api数据
     *
     * @return Boolean
     */
    public function getcheckversion($check)
    {
        // $checkcontent = explode(":", $check_content);
        $products_model = $check['products_model'];
        $hardware_id = $check['hardware_id'];
        $software_id = $check['software_id'];
        $update_id = $check['update_id'];
        //echo $this->dbGamecity->slave()

        $apkurl = $this->dbGamecity->slave()
            ->select('products_model, software_id, hardware_id, update_id, binfile, upbinfile, utype')
            ->from('version_upgrade')
            ->where(array('products_model' => $products_model,
                'hardware_id' => $hardware_id,
                'status' => 1))
            ->order('software_id desc, update_id desc')
            ->limit(1)
            ->getRow();
        if (empty($apkurl)) {
            return false;
        }
        if (($apkurl['software_id'] > $software_id) && ($apkurl['update_id'] > $update_id)) {
            $apkurl['result'] = 3;
            return $apkurl;
        }
        if (($apkurl['software_id'] > $software_id)) {
            $apkurl['result'] = 2;
            return $apkurl;
        }
        if (($apkurl['update_id'] > $update_id)) {
            $apkurl['result'] = 1;
            return $apkurl;
        }
        return false;
    }

    /**
     * 更新打印机信息 登出
     *
     * @param string $mkey 机器码
     *
     * @return Boolean
     */
    public function updatePrintLogoutByMkey($mkey)
    {
        return $this->dbGamecity->master()
            ->update('user_print',
                array('status' => 0,
                    'online' => 0,
                    'updatetime' => time()),
                array('mkey' => $mkey));
    }

    /**
     * 更新打印机信息 登出
     *
     * @param string $mkey 机器码
     *
     * @return Boolean
     */
    public function updatePrintLogoutByID($mkey)
    {
        return $this->dbGamecity->master()
            ->update('user_print',
                array('status' => 0,
                    'online' => 0,
                    'updatetime' => time()),
                array('id' => $mkey));
    }

    public function updateGetOrderStatus($mkey, $status)
    {
        $status = !empty($status) ?: 0;
        return $this->dbGamecity->master()
            ->update(
                'user_print',
                array('getOrderStatus' => $status),
                array('id' => $mkey)
            );
    }

}
