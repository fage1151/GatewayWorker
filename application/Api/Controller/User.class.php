<?php
/**
 * Class Controller_User
 *
 * @author PhpGame
 */

class Controller_User extends Controller_Base
{
    
    /**
     * 用户登陆实例 controller层
     *
     * @return array
     */
    public function action_index()
    {
        $arr = array();
        $arr = $this->getData;
        $fun = $arr['fun'];
        unset($arr['fun']);
        try {
            $result = call_user_func_array([Library_User::Instance(),$fun],$arr);
            return $this->showResult($result);
        } catch (Exception $ex) {
            return $this->showException();
        }
    }
}