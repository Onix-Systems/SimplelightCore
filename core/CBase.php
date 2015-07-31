<?php

/**
 * AltuhovKernel
 *
 * @copyright 	2015 Altuhov Konstantin
 * @author 		Altuhov Konstantin
 *
 * Базовый класс роботы приложения
 *
 */

class Base
{

	public function run(){
		$this->autoload();
		$this->router();
	}

	public static function getConfig($key = false)
	{

		$config = require(__DIR__.'/../../config/config.php');
		return $key ? $config[$key] : $config;
	}



	public function autoload(){
		require_once 'CBasetool.php';
		require_once 'CModel.php';
		require_once 'CController.php';
		require_once __DIR__.'/../libs/smarty/libs/Smarty.class.php';
	}

	public function router(){
		Core::$model = new Model;
		Core::$app = new Basetools();
		$frontend_module = self::getConfig('forntend_module_folder');
		$modules = array_flip(scandir('app/modules', 1));
		$use_module = false;
		unset($modules[0], $modules[1]);
		$uri = explode('/', $_SERVER['REQUEST_URI']);
		unset($uri[0]);
		foreach ($uri as $value)
			$data_url[] = !strrchr($value, '?') ? $value : explode('?', $value)[0];
		$use_module = isset($modules[$data_url[0]]) ? true : false;
		$module = $use_module ? $data_url[0] : $frontend_module;
		$controller_name = $use_module ? ($data_url[1] ? ucfirst($data_url[1]).'Controller' : 'MainController') : ($data_url[0] ? ucfirst($data_url[0]).'Controller' : 'MainController') ;
		$dir = 'app/modules/'.$module.'/controllers/';
		if (!file_exists($dir.$controller_name.'.php'))
			return core::$app->application->redirect('/', true, 'Такой страници не существует');
		require_once $dir.'IndexController.php';
		require_once $dir.$controller_name.'.php';
		$action = $use_module ? ($data_url[2] ? 'action'.ucfirst($data_url[2]) : 'actionIndex') : ($data_url[1] ? 'action'.ucfirst($data_url[1]) : 'actionIndex');
		$this->addParams($data_url);
		$controller = new $controller_name;
		$controller->always();
		$controller->module = $module;
		method_exists($controller, $action) ? $controller->$action() : $controller->actionIndex() ;
		$controller->render();
	}

	public function addParams($params){
		if (count($params) > 0) {
			$request_get = array_chunk($params, 2);
			foreach ($request_get as $value)
				$_GET[$value[0]] = $value[1];
		}
	}
}