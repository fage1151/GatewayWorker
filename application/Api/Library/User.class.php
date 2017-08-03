<?php
/**
 * Class Library_User
 *
 * @author
 */

class Library_User extends Library_Base
{
    
    /**
     * @return Library_User
     */
    static public function Instance()
    {
        return parent::InstanceInternal(__CLASS__);
    }

    /**
     * 用户登陆实例
     * 
     * @param string $username 机器码
     * @param string $password 认证
     * @param string $ip          服务器IP
     * @param string $port        端口
     * @param string $loginip     客户端访问IP
     * 
     * @return array || integer
     */
    public function userLogin($username, $password)
    {
        $user_count = Library_Model_User::Instance()->check_user($username,$password);
        if ($user_count == 1) {
                Library_Model_User::Instance()->update_user_login_info($username);
                return array();
            } else {
                return '10002'; //认证错误
            }
        } else {
            return '10001'; //机器码错误
        }
    }
}