<?php
/**
 * Class Controller_Base
 *
 * @author PhpGame
 */

class Controller_Base extends Controller {
    
    public $getData;
    protected $cmd;
    public $res = array();
    
    /**
     * 初始化数据
     */
    public function initialize()
    {
        $this->getData = Registry::get('GetData');
        $this->cmd = Registry::get('Cmd');
    }

    /**
     * 异常返回
     *
     * @return array
     */
    public function showException()
    {
        // 系统异常
        $result = 10000;
        $this->showResult($result);
    }

    /**
     * 传递数据出错返回
     *
     * @return array
     */
    public function showRequestError()
    {
        $result = 10030;
        $this->showResult($result);
    }

    /**
     * 显示结果
     *
     * @return array
     */
    public function showResult($result)
    {
        $return = '';
        if ($result === true) {
            $this->res['cmd'] = $this->cmd;
            $this->res['data'] = array(1);
        } elseif (is_array($result)) {
            $this->res['cmd'] = $this->cmd;
            $this->res['data'] = $result;
        } else {
            $this->res['cmd'] = $this->cmd;
            $this->res['data'] = array(0);
            $this->res['e'] = $result;
        }
        return $this->res;
    }
}
