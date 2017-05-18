<?php

class BraveDispatcher extends Brave {

    var $controllerAccessor = 'c';
    var $actionAccessor = 'a';
    var $defaultController = DEFAULT_CONTROLLER;
    var $defaultAction = DEFAULT_ACTION;
    var $controllerName = '';
    var $actionName = '';

    function dispatch($config = null, $data = null) {
        // global
        $this->setGlobal(__CLASS__, $this);

        // router
        if (is_null($config)) {
            $BraveRouter = $this->load(CORE, 'BraveRouter');

            if ($result = $BraveRouter->dispatch()) {
                $config = $result['action'];
                $data = $result;
            }
        }
		
        // controller name
        if (isset($config[$this->controllerAccessor])) {
            $controllerName = $config[$this->controllerAccessor];
            $this->controllerName = $controllerName;
        }
        else {
            $controllerName = $this->get($this->controllerAccessor, $this->defaultController);
            $this->controllerName = $controllerName;
        }

        // action name
        if (isset($config[$this->actionAccessor])) {
            $actionName = $config[$this->actionAccessor];
            $this->actionName = $actionName;
        }
        else {
            $actionName = $this->get($this->actionAccessor, $this->defaultAction);
            $this->actionName = $actionName;
        }
        
        // act check
        if (!CLI_MODE) {
            $BraveAct = $this->load(CORE, 'BraveAct');
            $config = array(
                $this->controllerAccessor => $controllerName,
                $this->actionAccessor => $actionName,
            );

            if (!$BraveAct->valid($config)) {
                $this->debug("Act failed!", E_USER_ERROR);
                return false;
            }
        }
		
        // check controller & action
        $controllerName = ucfirst($controllerName);
        $controller = $this->getController($controllerName);
        
        if (!$controller) {
            return false;
        }

        if (!$controller->hasAction($actionName)) {
            $this->debug("Action is not found in {$controller->name}: {$actionName}", E_USER_ERROR);
            return false;
        }

        // action
        $controller->setData($data);
        $controller->execAction($actionName);
        return true;
    }
}

?>
