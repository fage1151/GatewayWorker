<?php
/**
 * Class Engine
 *
 * @author PhpGame
 */

require_once MS_FRAMEWORK_ROOT . 'Framework.php';
require_once MS_FRAMEWORK_ROOT . 'Controller.php';

class Engine
{
    protected $routePathFields = array();

    /**
     * @var Controller
    */
    protected $controller;

    public function getRoutePathFields()
    {
        return $this->routePathFields;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    public function setRoutePath($data)
    {
        $this->routePathFields[0] = 'User';
        $this->routePathFields[1] = isset($data[0]) ? ucfirst($data[0]) : '';
        Registry::set('Cmd', $data[0]);
        array_shift($data);
        Registry::set('GetData', $data);

    }

    public function run()
    {
        $foundControllerClassName = null;
        $tempControllerClassName = 'Controller_' . join('_', $this->routePathFields);
        if (ClassAutoLoader::Load($tempControllerClassName)) {
            $foundControllerClassName = $tempControllerClassName;
        } else {
            $tmpFields = $this->routePathFields;
            array_pop($tmpFields);
            $tempControllerClassName = 'Controller_' . join('_', $tmpFields);
            if (ClassAutoLoader::Load($tempControllerClassName))
                $foundControllerClassName = $tempControllerClassName;
        }
        if (empty($foundControllerClassName)) {
            return false;
        } else {
            $lastActionField = end($this->routePathFields);
        }
        //then create the action handler
        $this->controller = $foundControllerClassName::CreateActionHandler($this);
        $actionMethod = "action_" . $lastActionField;
        return $this->controller->$actionMethod(); // 404 must be handled in base class's __call
    } //end if function run
}

