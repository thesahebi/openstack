<?php

//Decoded by SoarTeam SoarTeam
function OpenStackAutoLoader($class)
{
	if (strpos($class, 'OpenStack') === 0) {
		$dirs = array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'OpenStackAPI' . DIRECTORY_SEPARATOR, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'OpenStackModels' . DIRECTORY_SEPARATOR);
		$file = false;
		foreach ($dirs as $dir) {
			if (file_exists($dir . $class . '.php')) {
				$file = $dir . $class . '.php';
			}
		}
		if ($file) {
			require_once $file;
		} else {
			throw new OSException('Cant find file for class:' . $class, 0);
		}
	}
}
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'OpenStackExceptions.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PdoWrapper.php';
spl_autoload_register('OpenStackAutoLoader');