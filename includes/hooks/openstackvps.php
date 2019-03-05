<?php

//Decoded by SoarTeam SoarTeam
function OpenStackVPS_ProductEdit($params)
{
	require_once dirname(__FILE__) . DS . '..' . DS . 'OpenStack' . DS . 'OpenStackLoader.php';
	if (!isset($params['servertype'])) {
		$q = \MGModule\OpenStack\PdoWrapper::query('SELECT servertype FROM tblproducts WHERE id = ?', array($params['pid']));
		$row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($q);
		$params['servertype'] = $row['servertype'];
	}
	if (strtolower($params['servertype']) == 'openstackvps' && $_REQUEST['customconfigoption']) {
		require_once dirname(__FILE__) . DS . '..' . DS . 'OpenStack' . DS . 'functions.php';
		require_once dirname(__FILE__) . DS . '..' . DS . 'OpenStack' . DS . 'class.MG_Product.php';
		require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'modules' . DS . 'servers' . DS . 'OpenStackVPS' . DS . 'core' . DS . 'class.OpenStackVPS_Product.php';
		$conf = new OpenStackVPS_Product($params['pid']);
		$conf->saveConfigOptions($_REQUEST['customconfigoption']);
	}
}
function OpenStackVPS_ServerEdit($vars)
{
	if ($vars['filename'] != 'configservers' && ($_GET['action'] != 'manage' || !isset($_GET['id']))) {
		return NULL;
	}
	$script = "\n            <script type=\"text/javascript\">\n                var OpenStackVPS_td1;\n                var OpenStackVPS_td2;\n                var OpenStackVPS_accesshash;\n                var OpenStackVPS_selectAdded = false;\n                \n                function OpenStackVPS_addInput(){\n                    \$( \".fieldlabel\").each(function () {\n                        if(\$(this).html()==\"Access Hash<br>\\(Instead of password<br>for cPanel servers\\)\"){\n                            OpenStackVPS_td1 = \$(this).html();\n                            \$(this).html(\"Tenant ID\");\n                            \$(this).attr( \"id\", \"AccessHash\" );\n                        }\n                    });\n                    \$( \".fieldlabel\").each(function () {\n                        if(\$(this).html()==\"Access Hash<br>\\(Instead of password<br>for cPanel servers\\)\"){\n                            OpenStackVPS_td1 = \$(this).html();\n                            \$(this).addClass( \"OpenStackVPStd1\");\n                            \$(this).html(\"Tenant ID\");\n                        }\n                    });\n                    \n                     OpenStackVPS_selectAdded = true;\n                    \n                }\n                \n                function OpenStackVPS_removeInput(){\n                    \n                    \$(\"#AccessHash\").html(OpenStackVPS_td1);\n                    OpenStackVPS_selectAdded = false\n                }\n\n                \$(document).ready(function(){\n                    var pc_ServerType;\n                    \$( \"select[name='type'] option:selected\").each(function () {\n                        pc_ServerType = \$(this).val();\n                    });\n                    OpenStackVPS_accesshash = \$( \"textarea[name='accesshash']\").val();\n                    if(pc_ServerType==\"OpenStackVPS\" || pc_ServerType==\"OpenStackCloud\"){\n                         OpenStackVPS_addInput();\n                    }\n                    \n                    \$( \"select[name='type']\").change(function(){\n                          \$( \"select[name='type'] option:selected\").each(function () {\n                                pc_ServerType = \$(this).val();\n                          });\n                          if(OpenStackVPS_selectAdded==true &&  (pc_ServerType!=\"OpenStackVPS\" && pc_ServerType!=\"OpenStackCloud\") ){\n                              OpenStackVPS_removeInput();\n                          }else if(!OpenStackVPS_selectAdded && pc_ServerType==\"OpenStackVPS\" ){\n                              OpenStackVPS_addInput();\n                          }\n\n                    });\n               });\n            \n            </script>";
	return $script;
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
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
if (isset($_SESSION['adminid']) && isset($_REQUEST['action']) && (isset($_GET['id']) || $_POST['productid']) && isset($_POST['packageconfigoption']) && isset($_POST['OpenStackVPS_ajax'])) {
	require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'modules' . DS . 'servers' . DS . 'OpenStackVPS' . DS . 'core' . DS . 'functions.php';
	OpenStackVPS_loadClasses();
	$pid = $_POST['productid'] ? $_POST['productid'] : $_GET['id'];
	$product = new OpenStackVPS_Product($pid);
	$params = $product->getParams();
	try {
		switch ($_REQUEST['action']) {
			case 'OpenaStackVPS_setup_configurable_options':
				if ($product->hasConfigurableOptions()) {
					$res = array('result' => 0, 'msg' => 'Product has already configurable options assigned.');
					break;
				}
				$tenant = OpenStackTenant::WHMCSFactory($params, true);
				if ($product->getConfig('debug_mode')) {
					$tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
				}
				$flavors = $tenant->listFlavors();
				$images = $tenant->listImages();
				$product->setupDefaultConfigurableOptions($flavors, $images);
				$res = array('result' => 1, 'msg' => 'Default Configurable options have been created.');
				break;
			case 'OpenaStackVPS_remove_ip':
				$tenantID = $product->getConfig('tenantID');
				$tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);
				if ($product->getConfig('debug_mode')) {
					$tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
				}
				$vmID = $_REQUEST['vmID'];
				$ip = $_REQUEST['ip'];
				$vm = $tenant->VPS($vmID);
				$portID = $_REQUEST['portID'];
				$interface = $vm->getInterface($portID);
				$cron = new OpenStackVPSCron();
				$cron->setTaskContext($_REQUEST['serviceid']);
				$cron->addTask('deleteIP', array('portID' => $portID), $portID);
				$res = array('result' => 1, 'msg' => 'Added to  schedule');
				break;
			case 'OpenaStackVPS_remove_cron':
				$serviceID = $_REQUEST['serviceid'];
				$UUID = $_REQUEST['UUID'];
				$cron = new OpenStackVPSCron();
				$cron->setTaskContext($serviceID);
				$cron->deleteTaskByUUID($UUID);
				$res = array('result' => 1, 'msg' => 'Cron have been removed');
				break;
			case 'OpenaStackVPS_unlock_cron':
				$serviceID = $_REQUEST['serviceid'];
				$UUID = $_REQUEST['UUID'];
				$cron = new OpenStackVPSCron();
				$cron->setTaskContext($serviceID);
				$cron->unlockTaskByUUID($UUID);
				$res = array('result' => 1, 'msg' => 'Cron have been unlocked');
				break;
			case 'details':
				$tenantID = $product->getConfig('tenantID');
				$tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);
				if ($product->getConfig('debug_mode')) {
					$tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
				}
				$vmID = $_REQUEST['vmID'];
				$vm = $tenant->VPS($vmID);
				$res = array('result' => 1, 'vm_status' => $vm->status);
				break;
			case 'checkTenant':
				$tenantID = $_POST['tenantID'];
				$tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);
				if ($product->getConfig('debug_mode')) {
					$tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
				}
				$res = array('result' => 1);
				break;
		}
	} catch (Exception $e) {
		$res = array('result' => '0', 'msg' => $e->getMessage());
	}
	echo json_encode($res);
	die;
}
add_hook('ProductEdit', 1, 'OpenStackVPS_ProductEdit');
add_hook('AdminAreaHeaderOutput', 1, 'OpenStackVPS_ServerEdit');