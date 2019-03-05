<?php

//Decoded by SoarTeam SoarTeam
function OpenStackVPS_loadClasses()
{
	$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'OpenStack' . DIRECTORY_SEPARATOR;
	require_once $path . 'class.MG_Product.php';
	require_once $path . 'class.MG_Hosting.php';
	require_once $path . 'OpenStackLoader.php';
	require_once $path . 'functions.php';
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.OpenStackVPS_Product.php';
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.OpenStackVPS_Hosting.php';
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.OpenStackVPS_Log.php';
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'OpenStackVPSCron.php';
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.OpenStackVPS_KeyPair.php';
}
function OpenStackVPS_addError($error)
{
	$errors = $_SESSION['OpenStackVPS']['errors'];
	if (!in_array($error, $errors)) {
		$_SESSION['OpenStackVPS']['errors'][] = $error;
	}
}
function OpenStackVPS_addInfo($info)
{
	$infos = $_SESSION['OpenStackVPS']['infos'];
	if (!in_array($info, $infos)) {
		$_SESSION['OpenStackVPS']['infos'][] = $info;
	}
}
function OpenStackVPS_getErrors()
{
	$errors = $_SESSION['OpenStackVPS']['errors'];
	$_SESSION['OpenStackVPS']['errors'] = NULL;
	$cont = '';
	foreach ($errors as $e) {
		$cont .= $e;
	}
	return $cont;
}
function OpenStackVPS_hasErrors()
{
	$errors = $_SESSION['OpenStackVPS']['errors'];
	return empty($errors) ? false : true;
}
function OpenStackVPS_getInfos()
{
	$infos = $_SESSION['OpenStackVPS']['infos'];
	$_SESSION['OpenStackVPS']['infos'] = NULL;
	$cont = '';
	foreach ($infos as $i) {
		$cont .= $i;
	}
	return $cont;
}
if (!function_exists('OpenStackVPS_getLang')) {
	function OpenStackVPS_getLang($params)
	{
		global $CONFIG;
		if (!empty($_SESSION['Language'])) {
			$language = strtolower($_SESSION['Language']);
		} else {
			if (strtolower($params['clientsdetails']['language']) != '') {
				$language = strtolower($params['clientsdetails']['language']);
			} else {
				$language = $CONFIG['Language'];
			}
		}
		$langfilename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . '.php';
		if (file_exists($langfilename)) {
			require_once $langfilename;
		} else {
			require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'english.php';
		}
		return isset($lang) ? $lang : array();
	}
}