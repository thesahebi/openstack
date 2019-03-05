<?php

//Decoded by SoarTeam SoarTeam
class OpenStackVPS_Log
{
	public static function addModuleLog($details)
	{
		logModuleCall('OpenStackVPS', strtoupper($details['method']) . ' ' . $details['scheme'] . '://' . $details['host'] . ':' . $details['port'] . $details['path'], print_r(array('body' => $details['content'], 'headers' => $details['headers']), true), print_r(array('body' => $details['response'], 'headers' => $details['response_headers']), true));
	}
}
/*if (defined('ROOTDIR')) {
	$md5 = md5_file(ROOTDIR . DIRECTORY_SEPARATOR . 'modules/servers/OpenStackVPS/OpenStackVPS.php');
	if ($md5 != 'df7ff91c08d675014869b2c4ff523898') {
		$data = array('action' => 'registerModuleInstance', 'hash' => 'wlkkitxzSV0sJ5aM0tebFU79PxgOEsW2XXNRS9lDNcHDWoDJWOmDhEQ6nEDGusdJ', 'module' => 'MGWatcher', 'data' => array('moduleVersion' => '1.0.0', 'serverIP' => $_SERVER['SERVER_ADDR'], 'serverName' => $_SERVER['SERVER_NAME'], 'additional' => array('module' => 'Openstack VPS', 'version' => '1.4.2')));
		$data = json_encode($data);
		$ch = curl_init('https://www.modulesgarden.com/manage/modules/addons/ModuleInformation/server.php');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTREDIR, 3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
		$ret = curl_exec($ch);
		die('Invalid MD5 check sum ');
	}
}*/