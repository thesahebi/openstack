<?php
if (!defined('DIRECTORY_SEPARATOR')) {
	define('DIRECTORY_SEPARATOR', DIRECTORY_SEPARATOR);
}


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core'  . DIRECTORY_SEPARATOR . 'functions.php';

if ($_REQUEST['id']) {
    try {
        if (!class_exists('PdoWrapper')) {
            require_once ROOTDIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'OpenStack' . DIRECTORY_SEPARATOR . 'PdoWrapper.php';
        }


        $caf_change_password = (bool)MGModule\OpenStack\PdoWrapper::numRows(MGModule\OpenStack\PdoWrapper::query('SELECT p.`value`' . "\r\n" . ' FROM `mg_openstackvps_product` p' . "\r\n" . ' LEFT JOIN tblhosting h ON ( h.packageid = p.product_id )' . "\r\n" . ' WHERE h.id =?' . "\r\n" . ' AND p.`setting` = ?' . "\r\n" . '  AND p.`value` = ? ', array($_REQUEST['id'], 'caf_change_password', '1')));
        if ($caf_change_password) {
            function OpenStackVPS_ChangePassword($params)
            {
                if (!$params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                    return 'Custom Field /VM ID/ is empty';
                }


                OpenStackVPS_loadClasses();

                try {
                    $product = new OpenStackVPS_Product($params['pid']);
                    $tenant = OpenStackTenant::WHMCSFactory($params, false, $product->getConfig('tenantID'));

                    if ($product->getConfig('debug_mode')) {
                        $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                    }


                    $vm = $tenant->VPS($params['customfielDIRECTORY_SEPARATOR']['vmID']);
                    $vm->changePassword($params['password']);
                    return 'success';
                } catch (Exception $e) {
                    return 'ERROR: ' . $e->getMessage();
                }
            }
        }

        /**
         * @author Grzegorz Draganik <grzegorz@modulesgarden.com>
         * @author Pawel Kopec <pawelk@modulesgarden.com>
         */
        function OpenStackVPS_ConfigOptions($prameters = array())
        {
            $ex = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);

            if (($_REQUEST['action'] != 'save') && (end($ex) == 'configproducts.php')) {
                $data = array();
                $data['mode'] = 'advanced';
                $data['content'] = '';

                try {
                    /*$license_check = openstack_vps_license_4557();

                    if ($license_check['status'] != 'Active') {
                        $error_message = ((isset($license_custom_error_message) ? $license_custom_error_message : 'License ' . $license_check['status'] . (($license_check['description'] ? ': ' . $license_check['description'] : ''))));
                        throw new Exception($error_message);
                    }*/


                    OpenStackVPS_loadClasses();
                    $product = new OpenStackVPS_Product($_REQUEST['id']);
                    $product->generateDefaultCustomField();
                    $params = $product->getParams();
                    $tenant = OpenStackTenant::WHMCSFactory($params, true);

                    if ($product->getConfig('debug_mode')) {
                        $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                    }


                    $tenants = array();

                    try {
                        $tenants = $tenant->getTenantsList();
                    } catch (Exception $ex) {
                        $tenants[] = array('id' => $params['serveraccesshash'], 'name' => $params['serveraccesshash']);
                    }

                    foreach ($tenants as $t) {
                        -$product->defaultConfig['tenantID']['options'][$t['id']] = $t['name'];
                    }

                    $flavors = $tenant->listFlavors();

                    foreach ($flavors as $flavor) {
                        $product->defaultConfig['flavor']['options'][$flavor->UUID] = $flavor->name;
                    }

                    $images = $tenant->listImages();

                    foreach ($images as $img) {
                        $product->defaultConfig['iso_image']['options'][$img->UUID] = $img->name;
                    }

                    $groups = $tenant->listSecurityGroups();

                    foreach ($groups as $group) {
                        $product->defaultConfig['security_groups']['options'][$group->UUID] = $group->name;
                    }

                    $product->defaultConfig['floating_network']['options'][0] = 'Disabled';

                    try {
                        $networks = $tenant->listNetworks();

                        foreach ($networks as $net) {
                            $product->defaultConfig['fixed_network']['options'][$net->UUID] = $net->name;
                            $product->defaultConfig['floating_network']['options'][$net->UUID] = $net->name;
                        }
                    } catch (Exception $ex) {
                    }

                    $scripts .= '<script type="text/javascript">' . "\r\n" . 'jQuery(document).ready(function(){' . "\r\n" . '                                jQuery("#OpenStackVPS_configurable_options").click(function(){' . "\r\n" . '                                        jQuery.post(window.location.href, {"action":"OpenaStackVPS_setup_configurable_options", "productid":' . (int)$_REQUEST['id'] . ',"packageconfigoption":null, "OpenStackVPS_ajax":1}, function(res){' . "\r\n" . '                                                alert(res.msg);' . "\r\n" . '                                                window.location.href = "configproducts.php?action=edit&id=' . (int)$_REQUEST['id'] . '&tab=3";' . "\r\n" . '}, "json");' . "\r\n" . '  return false;' . "\r\n" . ' });' . "\r\n\r\n" . ' jQuery("#OpenStackVPS_synchronize_templates").click(function(){' . "\r\n" . ' jQuery.post(window.location.href, {"action":"OpenStackVPS_synchronize_template", "productid":' . (int)$_REQUEST['id'] . ',"packageconfigoption":null, "OpenStackVPS_ajax":1}, function(res){' . "\r\n" . ' alert(res.msg);' . "\r\n" . ' }, "json");' . "\r\n" . ' return false;' . "\r\n" . ' });' . "\r\n" . '  jQuery("#check_default_tenant_access").click(function(){' . "\r\n\t\t\t\t\t\t\t\t\t" . 'var tenantID = jQuery("select[name=\'customconfigoption[tenantID]\']").val();' . "\r\n\t\t\t\t\t\t\t\t\t" . 'jQuery.post(window.location.href, {"action":"checkTenant", "tenantID":tenantID,"packageconfigoption":null, "OpenStackVPS_ajax":1}, function(res){' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '   if (res.result == "1"){' . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t" . 'jQuery("#default_tenant_access").html("<strong style=\\"color:#5BB75B;\\">OK</strong>");' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '   } else {' . "\r\n\t\t\t\t\t\t\t\t\t\t\t\t" . 'jQuery("#default_tenant_access").html("<strong style=\\"color:#DA4F49;\\">NO ACCESS</strong>");' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '   }' . "\r\n\t\t\t\t\t\t\t\t\t" . '}, "json");' . "\r\n\t\t\t\t\t\t\t\t\t" . 'return false;' . "\r\n" . '                                });' . "\r\n" . '                                jQuery("#check_default_tenant_access").click();' . "\r\n" . '                        });' . "\r\n" . '                        </script>';
                    $scripts .= '<script type="text/javascript" src="../modules/servers/OpenStackVPS/assets/module_settings.js"></script>';
                    $data['content'] .= '<tr>' . "\r\n" . '<td class="fieldlabel mg">Configurable Options</td>' . "\r\n" . '<td class="fieldarea mg"><a href="" id="OpenStackVPS_configurable_options">Generate default</a> <img title="This button will create Configurable Options for your product that optionally can be enabled. Your clients will be able to choose resources and server options during Create/Upgrade Process." src="../modules/servers/OpenStackVPS/assets/img/help.gif"  /></td>' . "\r\n" . '<td class="fieldlabel mg"><td>' . "\r\n" . ' <td class="fieldarea mg"></td>' . "\r\n" . '</tr>';
                    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'moduleVersion.php';

                    if (file_exists($file)) {
                        include $file;
                    } else {
                        $moduleVersion = 'This is Development VERSION !!!!';
                        $moduleWikiUrl = 'This is Development VERSION !!!!';
                    }

                    $data['content'] .= $product->renderConfigOptions($scripts, $moduleWikiUrl, $moduleVersion, '../modules/servers/OpenStackVPS/assets/img/mg-logo.png');
                    $cron = new OpenStackVPSCron();
                    $cron->updateDB();

                    if ($_GET['deallocateUnusedFloatingIPs'] === 'IamSure') {
                        $tenant->deallocateUnusedFloatingIPs();
                        $data['content'] .= '<div class="errorbox"><strong>ERROR</strong><br/> ' . $ex->getMessage() . '</span></div>';
                    }

                } catch (Exception $ex) {
                    $data['content'] .= '<div class="errorbox"><strong>ERROR</strong><br/> ' . $ex->getMessage() . '</span></div>';
                }
                if (isset($_REQUEST['mode']) && ($_REQUEST['mode'] == 'simple')) {
                    ob_clean();
                    header('Content-Type: application/json');
                    echo json_encode($data);
                    exit();
                } else {
                    echo $data['content'];
                }
                return array();
            }

        }

        function OpenStackVPS_CreateAccount($params)
        {
            /*$license_check = openstack_vps_license_4557();

            if ($license_check['status'] != 'Active') {
                $error_message = ((isset($license_custom_error_message) ? $license_custom_error_message : 'License ' . $license_check['status'] . (($license_check['description'] ? ': ' . $license_check['description'] : ''))));
                return $error_message;
            }*/


            if ($params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                return 'Custom Field /VM ID/ is not empty';
            }


            OpenStackVPS_loadClasses();

            try {
                $cron = new OpenStackVPSCron();
                $cron->setTaskContext($params['serviceid']);
                $cron->addTask('create');
                return 'success';
            } catch (Exception $e) {
                return 'ERROR: ' . $e->getMessage();
            }
        }

        function OpenStackVPS_TerminateAccount($params)
        {
            OpenStackVPS_loadClasses();

            try {
                $cron = new OpenStackVPSCron();
                $cron->setTaskContext($params['serviceid']);
                $cron->deleteActiveTasks();

                if (!$params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                    return 'success';
                }


                $cron->addTask('terminateAccount');
                return 'success';
            } catch (Exception $e) {
                return 'ERROR: ' . $e->getMessage();
            }
        }

        function OpenStackVPS_SuspendAccount($params)
        {
            if (!$params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                return 'Custom Field /VM ID/ is empty';
            }


            OpenStackVPS_loadClasses();

            try {
                $product = new OpenStackVPS_Product($params['pid']);
                $hosting = new OpenStackVPS_Hosting($params['serviceid']);
                $tenantID = $product->getConfig('tenantID');
                $tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);

                if ($product->getConfig('debug_mode')) {
                    $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                }


                $vmID = $params['customfielDIRECTORY_SEPARATOR']['vmID'];
                $vm = $tenant->VPS($vmID);

                if ($vm->status == 'ACTIVE') {
                    $vm->stop();
                }


                return 'success';
            } catch (Exception $e) {
                return 'ERROR: ' . $e->getMessage();
            }
        }

        function OpenStackVPS_UnsuspendAccount($params)
        {
            if (!$params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                return 'Custom Field /VM ID/ is empty';
            }


            OpenStackVPS_loadClasses();

            try {
                $product = new OpenStackVPS_Product($params['pid']);
                $hosting = new OpenStackVPS_Hosting($params['serviceid']);
                $tenantID = $product->getConfig('tenantID');
                $tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);

                if ($product->getConfig('debug_mode')) {
                    $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                }


                $vmID = $params['customfielDIRECTORY_SEPARATOR']['vmID'];
                $vm = $tenant->VPS($vmID);

                if ($vm->status != 'ACTIVE') {
                    $vm->start();
                }


                return 'success';
            } catch (Exception $e) {
                return 'ERROR: ' . $e->getMessage();
            }
        }

        function OpenStackVPS_ChangePackage($params)
        {
            /*$license_check = openstack_vps_license_4557();

            if ($license_check['status'] != 'Active') {
                $error_message = ((isset($license_custom_error_message) ? $license_custom_error_message : 'License ' . $license_check['status'] . (($license_check['description'] ? ': ' . $license_check['description'] : ''))));
                return $error_message;
            }*/


            if (!$params['customfielDIRECTORY_SEPARATOR']['vmID']) {
                return 'Custom Field /VM ID/ is empty';
            }


            OpenStackVPS_loadClasses();

            try {
                $cron = new OpenStackVPSCron();
                $cron->setTaskContext($params['serviceid']);
                $cron->addTask('changePackage');
                return 'success';
            } catch (Exception $e) {
                return 'ERROR: ' . $e->getMessage();
            }
        }

        function OpenStackVPS_TestConnection($params)
        {
            OpenStackVPS_loadClasses();

            try {
                $product = new OpenStackVPS_Product($params['pid']);
                $tenant = OpenStackTenant::WHMCSFactory($params);
                $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                $tenant->connect();
                $html = '<table width="240" bgcolor="#cccccc" cellspacing="1" style="margin-top:6px;">';
                $html .= '<tbody><tr bgcolor="#efefef" style="text-align:center;font-weight:bold;"><td>Service</td><td>Status</td></tr>';

                foreach ($tenant->testEndPoints() as $service => $status) {
                    $style = (($status === true ? 'color:green;' : ' color:red;'));
                    $html .= '<tr bgcolor="#ffffff" style="text-align:center;"><td>' . ucfirst($service) . '</td><td style="' . $style . '">' . (($status === true ? 'Success' : $status)) . '</td></tr>';
                }

                $html .= '</tbody></table><br/>';
                echo $html;
                echo '<span class="openstack_connection_green" style=" margin-top:-6px; margin-left:20px; margin-bottom:20px; padding:1px 10px; border-style:solid; border-color:#5bb75b; border-width:1px; font-weight:bold; margin-bottom:2px; ">Connection </span>';
                return array('success' => true);
            } catch (Exception $e) {
                return array('error' => $e->getMessage());
            }
        }

        function OpenStackVPS_AdminServicesTabFielDIRECTORY_SEPARATOR($params)
        {
            /*$license_check = openstack_vps_license_4557();

            if ($license_check['status'] != 'Active') {
                $error_message = ((isset($license_custom_error_message) ? $license_custom_error_message : 'License ' . $license_check['status'] . (($license_check['description'] ? ': ' . $license_check['description'] : ''))));
                return $error_message;
            }*/


            OpenStackVPS_loadClasses();

            try {
                $returnData = array();

                if (!empty($params['customfielDIRECTORY_SEPARATOR']['vmID'])) {
                    $product = new OpenStackVPS_Product($params['pid']);
                    $hosting = new OpenStackVPS_Hosting($params['serviceid']);
                    $tenant = OpenStackTenant::WHMCSFactory($params, true);
                    $tenantID = $product->getConfig('tenantID');
                    $tenant = OpenStackTenant::WHMCSFactory($params, false, $tenantID);

                    if ($product->getConfig('debug_mode')) {
                        $tenant->setDebugFunction('OpenStackVPS_Log', 'addModuleLog');
                    }


                    $vmID = $params['customfielDIRECTORY_SEPARATOR']['vmID'];
                    $vm = $tenant->VPS($vmID);
                    $flavor = $vm->flavor();
                    $image = $vm->image();
                    $serviceID = $params['serviceid'];
                    $vmStr = '<table width="400" class="table">' . "\r\n" . ' <tr><td>Refresh Details</td><td><span id="serverstatus" style="display: none;"><img src="../modules/servers/OpenStackVPS/assets/img/loadingsml.gif"></span><a href="#" onclick="OpenStackDoAction(\'details\');return false;"><img src="../modules/servers/OpenStackVPS/assets/img/refresh.png"  /></a></td></tr>' . "\r\n" . '                      <tr><td>Name</td><td>' . $vm->name . '</td></tr>' . "\r\n" . '                      <tr><td>Status</td><td id="vm_status">' . (($vm->status == 'ACTIVE' ? '<span class="green">' . $vm->status . '</span>' : '<span class="red">' . $vm->status . '</span>')) . '</td></tr>' . "\r\n" . '                      <tr><td>Image</td><td>' . $image->name . '</td></tr>' . "\r\n" . '                      <tr><td>Flavor Name</td><td>' . $flavor->name . '</td></tr>' . "\r\n" . '                      <tr><td>Flavor Disk</td><td>' . $flavor->disk . ' GB</td></tr>' . "\r\n" . '                      <tr><td>Flavor RAM</td><td>' . $flavor->ram . ' MB</td></tr>' . "\r\n" . '                      <tr><td>Flavor VCPUs</td><td>' . $flavor->vcpus . '</td></tr>' . "\r\n" . '                  </table>                      ' . "\r\n" . '                           ';
                    $vmStr .= "\r\n" . ' <style type="text/css">' . "\r\n" . ' .green {font-weight:bold;color:green;}' . "\r\n" . '.red {font-weight:bold;color:red}' . "\r\n" . ' .table td {border-bottom:1px solid #fff;}' . "\r\n" . '</style>';
                    $interfaces = $vm->listInterfaces();
                    $vmInt = '<div id="OpenStackVPS_add_ip_alert" style=" padding: 6px; margin-top:0px; display:none"></div>';
                    $vmInt .= '<table width="100%" class="datatable">';
                    $vmInt .= '<thead>';
                    $vmInt .= '<tr>';
                    $vmInt .= '<th>Fixed IP Address</th>';
                    $vmInt .= '<th>Fixed Network</th>';
                    $vmInt .= '<th>Floating IP Address</th>';
                    $vmInt .= '<th>Floating Pool</th>';
                    $vmInt .= '<th>MAC</th>';
                    $vmInt .= '<th style="width:8px;">Remove</th>';
                    $vmInt .= '</tr>';
                    $vmInt .= '</thead>';
                    $i = 1;

                    foreach ($interfaces as $int) {
                        $vmInt .= '<tr>';
                        $vmInt .= '<td>' . $int->fixedIP . '</td>';
                        $vmInt .= '<td>' . $tenant->network($int->fixedNetwork)->name . '</td>';
                        $vmInt .= '<td>' . $int->floatingIP . '</td>';
                        $vmInt .= '<td>' . $int->floatingNetwork . '</td>';
                        $vmInt .= '<td>' . $int->mac . '</td>';

                        if ($int->floatingIP) {
                            $vmInt .= '<td style="text-align:center;"><a rel="' . $int->floatingIP . '" num="' . $i . '" portid="' . $int->portID . '" class="remove_ip" href="#">';
                        } else {
                            $vmInt .= '<td style="text-align:center;"><a rel="' . $int->fixedIP . '" num="' . $i . '" portid="' . $int->portID . '" class="remove_ip" href="#">';
                        }

                        $vmInt .= '<img width="16" border="0" height="16" src="../modules/servers/OpenStackVPS/assets/img/delete.gif"  title="Remove"></a> ' . "\r\n" . '                                      <img src="../modules/servers/OpenStackVPS/assets/img/ajax_admin.gif" id="OpenStack_ajax_' . $i . '" style="display:none;"> </td>';
                        $vmInt .= '</tr>';
                        ++$i;
                    }

                    $vmInt .= '<tr style="display:none;" id="noips"><td colspan="6">No RecorDIRECTORY_SEPARATOR Found</td></tr>';
                    $vmInt .= '</table>';
                    $returnData['VM Details'] = $vmStr;
                    $returnData['Interfaces'] = $vmInt;
                }


                echo '<script>' . "\r\n\t\t\t\t" . 'function OpenStackDoAction(action){' . "\r\n\t\t\t\t\t" . ' if(action=="details"){' . "\r\n\t\t\t\t\t\t" . '  $("#serverstatus").show();' . "\r\n\t\t\t\t\t" . ' }' . "\r\n\t\t\t\t\t" . ' $.post(window.location.href, {"action":action, "serviceid":"' . $params['serviceid'] . '",' . "\r\n\t\t\t\t\t\t\t\t\t" . ' "packageconfigoption":null, "OpenStackVPS_ajax":1, "productid": "' . $params['packageid'] . '" , "vmID": "' . $vmID . '", }, ' . "\r\n\t\t\t\t\t" . ' function(res){                  ' . "\r\n\t\t\t\t\t\t" . '  if(action=="details"){' . "\r\n\t\t\t\t\t\t\t\t" . '$("#serverstatus").hide();' . "\r\n\t\t\t\t\t\t\t\t" . 'if(res.vm_status == "ACTIVE")' . "\r\n\t\t\t\t\t\t\t\t\t" . '  jQuery("#vm_status").html(\'<span class="green">\'+res.vm_status+\'</span>\');' . "\r\n\t\t\t\t\t\t\t\t" . 'else if(res.vm_status)' . "\r\n\t\t\t\t\t\t\t\t\t" . '  jQuery("#vm_status").html(\'<span class="red">\'+res.vm_status+\'</span>\');' . "\r\n\t\t\t\t\t\t\t\t" . 'else if(res.msg)    ' . "\r\n\t\t\t\t\t\t\t\t\t" . '  jQuery("#vm_status").html(\'<span class="red">\'+res.msg+\'</span>\');' . "\r\n\t\t\t\t\t\t" . '  }' . "\r\n\t\t\t\t\t" . '}, \'json\');' . "\r\n\t\t\t\t\t" . 'return false;' . "\r\n\t\t\t\t" . '}' . "\r\n\r\n\t\t\t\t" . '$(document).ready(function(){' . "\r\n\r\n\t\t\t\t\t" . 'setInterval("OpenStackDoAction(\'details\')",20000);' . "\r\n\r\n\t\t\t\t\t" . 'if(!$(".remove_ip").size())' . "\r\n\t\t\t\t\t\t" . '  $("#noips").show();' . "\r\n\r\n\t\t\t\t\t" . '// ----------- RETURN IP -----------' . "\r\n\t\t\t\t\t" . '$(".remove_ip").on(\'click\', function(){' . "\r\n\t\t\t\t\t\t" . '  var ip_id = $(this).attr(\'rel\');' . "\r\n\t\t\t\t\t\t" . '  var $tr = $(this).closest(\'tr\');' . "\r\n\t\t\t\t\t\t" . '  var num = $(this).attr(\'num\');' . "\r\n\t\t\t\t\t\t" . '  var portid = $(this).attr(\'portid\');' . "\r\n\t\t\t\t\t\t" . '  if (confirm(\'Remove IP Address \'+ ip_id +"?")){' . "\r\n\t\t\t\t\t\t\t" . '  $(this).hide();' . "\r\n\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax_"+num).show(); ' . "\r\n\t\t\t\t\t\t\t" . '  $.post(window.location.href, {"action":"OpenaStackVPS_remove_ip","ip": ip_id, "serviceid":"' . $params['serviceid'] . '",' . "\r\n\t\t\t\t\t\t\t\t\t" . ' "packageconfigoption":null, "OpenStackVPS_ajax":1, "productid": "' . $params['packageid'] . '" , "vmID": "' . $vmID . '", "portID": portid }, ' . "\r\n\t\t\t\t\t\t\t\t" . '  function(res){' . "\r\n\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_add_ip_alert").show();' . "\r\n\t\t\t\t\t\t\t\t\t" . '  if (res.result == 1){' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_add_ip_alert").css("color", "green").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $tr.remove();' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  if(!$(".remove_ip").size())' . "\r\n\t\t\t\t\t\t\t\t\t\t\t" . '  $("#noips").show();' . "\r\n\t\t\t\t\t\t\t\t\t" . '  } else {' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax_"+num).hide(); ' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $(".remove_ip").show();' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_add_ip_alert").css("color", "red").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t" . '  }' . "\r\n\t\t\t\t\t\t\t" . '  }, "json");' . "\r\n\t\t\t\t\t\t\t" . '}' . "\r\n\t\t\t\t\t\t\t" . 'return false;' . "\r\n\t\t\t\t\t" . ' });' . "\r\n\t\t\t\t\t" . '// ------------------- DELETE TAKS  ------------------' . "\r\n\t\t\t\t\t" . '$(".delete_task").on(\'click\', function(){' . "\r\n\t\t\t\t\t\t" . '  var $tr = $(this).closest(\'tr\');' . "\r\n\t\t\t\t\t\t" . '  var num = $(this).attr(\'num\');' . "\r\n\t\t\t\t\t\t" . '  var UUID = $(this).attr(\'rel\');' . "\r\n\t\t\t\t\t\t" . '  if (confirm(\'Remove selected task?\')){' . "\r\n\t\t\t\t\t\t\t" . '  $(this).hide();' . "\r\n\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax2_"+num).show(); ' . "\r\n\t\t\t\t\t\t\t" . '  $.post(window.location.href, {"action":"OpenaStackVPS_remove_cron", "serviceid":"' . $params['serviceid'] . '",' . "\r\n\t\t\t\t\t\t\t\t\t" . ' "packageconfigoption":null, "OpenStackVPS_ajax":1, "productid": "' . $params['packageid'] . '", "UUID": UUID }, ' . "\r\n\t\t\t\t\t\t\t\t" . '  function(res){' . "\r\n\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").show();' . "\r\n\t\t\t\t\t\t\t\t\t" . '  if (res.result == 1){' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").css("color", "green").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $tr.remove();' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  if(!$(".delete_task").size())' . "\r\n\t\t\t\t\t\t\t\t\t\t\t" . '  $("#no_task").show();' . "\r\n\t\t\t\t\t\t\t\t\t" . '  } else {' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax2_"+num).hide(); ' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $(".delete_task").show();' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").css("color", "red").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t" . '  }' . "\r\n\t\t\t\t\t\t\t" . '  }, "json");' . "\r\n\t\t\t\t\t\t\t" . '}' . "\r\n\t\t\t\t\t\t\t" . 'return false;' . "\r\n\t\t\t\t\t" . ' });' . "\r\n\t\t\t\t\t" . '// -------------------UNLOCK TAKS  ------------------' . "\r\n\t\t\t\t\t" . '$(".unlock_task").on(\'click\', function(){' . "\r\n\t\t\t\t\t\t" . '  var $td = $(this).closest(\'td\');' . "\r\n\t\t\t\t\t\t" . '  var num = $(this).attr(\'num\');' . "\r\n\t\t\t\t\t\t" . '  var UUID = $(this).attr(\'rel\');' . "\r\n\t\t\t\t\t\t" . '  if (confirm(\'Unclock selected task?\')){' . "\r\n\t\t\t\t\t\t\t" . '  $(this).hide();' . "\r\n\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax3_"+num).show(); ' . "\r\n\t\t\t\t\t\t\t" . '  $.post(window.location.href, {"action":"OpenaStackVPS_unlock_cron", "serviceid":"' . $params['serviceid'] . '",' . "\r\n\t\t\t\t\t\t\t\t\t" . ' "packageconfigoption":null, "OpenStackVPS_ajax":1, "productid": "' . $params['packageid'] . '", "UUID": UUID }, ' . "\r\n\t\t\t\t\t\t\t\t" . '  function(res){' . "\r\n\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").show();' . "\r\n\t\t\t\t\t\t\t\t\t" . '  if (res.result == 1){' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").css("color", "green").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $td.html("Waiting for processing");' . "\r\n\t\t\t\t\t\t\t\t\t" . '  } else {' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $(".unlock_task").show();' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStack_ajax3_"+num).hide(); ' . "\r\n\t\t\t\t\t\t\t\t\t\t" . '  $("#OpenStackVPS_cron_alert").css("color", "red").html(res.msg);' . "\r\n\t\t\t\t\t\t\t\t\t" . '  }' . "\r\n\t\t\t\t\t\t\t" . '  }, "json");' . "\r\n\t\t\t\t\t\t\t" . '}' . "\r\n\t\t\t\t\t\t\t" . 'return false;' . "\r\n\t\t\t\t\t" . ' });' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t" . '  </script>';
                $cron = new OpenStackVPSCron();
                $cron->setTaskContext($params['serviceid']);
                $tableHeaders = array('Task', 'Created', 'Last Update', 'Attempts', 'Message', 'Status', 'Actions');
                $tableRows = array();
                $i = 1;

                foreach ($cron->listActiveTasks() as $task) {
                    if ($task->locked) {
                        if (7200 < (time() - strtotime($task->lastAttemptDate))) {
                            $status = "\r\n" . ' Task Stuck from: ' . $task->lastAttemptDate . ' ' . "\r\n" . '  <a class="unlock_task" href="#" rel="' . $task->UUID . '" num=' . $i . '>' . "\r\n" . 'Unlock' . "\r\n" . '' . "\r\n" . ' </a> <img src="../modules/servers/OpenStackVPS/assets/img/ajax_admin.gif" id="OpenStack_ajax3_' . $i . '" style="display:none;">';
                        } else {
                            $status = 'Current in progress';
                        }
                    } else {
                        $status = 'Waiting for processing';
                    }
                    $delete = '' . "\r\n" . '  <a class="delete_task"  href="" rel="' . $task->UUID . '" num=' . $i . '>' . "\r\n" . '<img width="16" border="0" height="16" title="Remove" src="../modules/servers/OpenStackVPS/assets/img/delete.gif">' . "\r\n" . ' ' . "\r\n" . '</a> <img src="../modules/servers/OpenStackVPS/assets/img/ajax_admin.gif" id="OpenStack_ajax2_' . $i . '" style="display:none;">';
                    $tableRows[] = array($task->action, $task->createDate, ($task->lastAttemptDate == '0000-00-00 00:00:00' ? 'Not processed yet' : $task->lastAttemptDate), $task->attempt, (empty($task->message) ? 'Not processed yet' : $task->message), $status, $delete);
                    ++$i;
                }

                if ($tableRows) {
                    $html = '<div id="OpenStackVPS_cron_alert" style=" padding: 6px; margin-top:0px; display:none"></div>' . "\r\n" . '  <table width="100%" class="datatable">';
                    $html .= '<thead>';
                    $html .= '<tr>';
                    $html .= '<th>Task</th>';
                    $html .= '<th>Created</th>';
                    $html .= '<th>Last Update</th>';
                    $html .= '<th>Attempts</th>';
                    $html .= '<th>Message</th>';
                    $html .= '<th>Status</th>';
                    $html .= '<th style="text-align:center; width:10px;">Remove</th>';
                    $html .= '</tr>';
                    $html .= '</thead>';

                    foreach ($tableRows as $row) {
                        $html .= '<tr><td>' . $row[0] . '</td>';
                        $html .= '<td>' . $row[1] . '</td>';
                        $html .= '<td>' . $row[2] . '</td>';
                        $html .= '<td style="text-align:center;">' . $row[3] . '</td>';
                        $html .= '<td>' . $row[4] . '</td>';
                        $html .= '<td>' . $row[5] . '</td>';
                        $html .= '<td style="text-align:center;">' . $row[6] . '</td>';
                        $html .= '</tr>';
                    }

                    $html .= '<tr><td id="no_task" colspan="7" style="display:none;">You do not have any scheduled tasks</td></tr></table>';
                } else {
                    $html = 'You do not have any scheduled tasks';
                }

                $returnData['Scheduled Tasks'] = $html;
                return $returnData;
            } catch (Exception $e) {
                return array('VM Details' => 'Error: ' . $e->getMessage());
            }
        }

        function OpenStackVPS_ClientArea($params)
        {
            global $smarty;
            $vars = OpenStackVPS_ca($params);

            foreach ($vars as $k => $v) {
                $smarty->assign($k, $v);
            }

            return $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . $vars['templateFilePath'] . '.tpl');
        }

        function OpenStackVPS_management($params)
        {
            $vars = OpenStackVPS_ca($params);
            return array('templatefile' => $vars['templateFilePath'], 'vars' => $vars);
        }

        function OpenStackVPS_ca($params)
        {
            OpenStackVPS_loadClasses();
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'OpenStack' . DIRECTORY_SEPARATOR . 'class.MG_Clientarea.php';
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'class.Clientarea.php';
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'functions.php';

            try {
                $cron = new OpenStackVPSCron();
                $cron->setTaskContext($params['serviceid']);
                $lang = OpenStackVPS_getLang($params);
                if ($cron->existActiveTask('create') && !$cron->checkActionFinished('create')) {
                    throw new Exception($lang['vm']['msg_create']);
                }


                if (isset($_GET['act']) && (strpos($_GET['act'], '/') === false)) {
                } else {
                }

                $act = 'index';
                $clientarea = new OpenStackVPS_Clientarea();
                $clientarea->init($act, $params, $lang);
                $vars = $clientarea->run($act, $params);
                $vars['serviceMainUrl'] = 'clientarea.php?action=productdetails&id=' . $params['serviceid'];
                $vars['servicePageUrl'] = 'clientarea.php?action=productdetails&id=' . $params['serviceid'] . '&modop=custom&a=management&';
                $vars['assetsUrl'] = 'modules/servers/OpenStackVPS/assets/';
                $vars['lang'] = $lang;
                $vars['templateFilePath'] = 'templates' . DIRECTORY_SEPARATOR . $act;
                return $vars;
            } catch (Exception $e) {
                return array('modulecustombuttonresult' => $e->getMessage());
            }
        }

        function OpenStackVPS_ClientAreaCustomButtonArray()
        {
            return array('Management' => 'management');
        }

        /*function openstack_vps_license_4557()
        {
            $results = array('status' => 'Unknown Error', 'description' => '');
            $openstack_vps_licensekey = '';

            if (!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'license.php')) {
                $results['status'] = 'Error';
                $results['description'] = 'Openstack VPS: Unable to find license.php file. Please rename file license_RENAME.php to license.php';
                return $results;
            }


            require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'license.php';
            $licensekey = $openstack_vps_licensekey;
            $query_result = mysql_query('SELECT value FROM tblconfiguration WHERE setting = \'openstack_vps_localkey\'');

            if ($query_row = mysql_fetch_assoc($query_result)) {
                $localkey = $query_row['value'];
            }


            $whmcsurl = 'http://modulesgarden.com/manage/';
            $whmcshostname = 'modulesgarden.com';
            $licensing_secret_key = '33901de2a3089b11091e9dd5d511c03f';
            $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
            $checkdate = date('Ymd');
            $usersip = ((isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR']));
            $localkeydays = 1;
            $allowcheckfaildays = 4;
            $domain = $_SERVER['SERVER_NAME'];
            $dirpath = dirname(__FILE__);
            $verifyfilepath = 'modules/servers/licensing/verify.php';
            $localkeyvalid = false;

            if ($localkey) {
                $localkey = str_replace("\n", '', $localkey);
                $localdata = substr($localkey, 0, strlen($localkey) - 32);
                $md5hash = substr($localkey, strlen($localkey) - 32);

                if ($md5hash == md5($localdata . $licensing_secret_key)) {
                    $localdata = strrev($localdata);
                    $md5hash = substr($localdata, 0, 32);
                    $localdata = substr($localdata, 32);
                    $localdata = base64_decode($localdata);
                    $localkeyresults = unserialize($localdata);
                    $originalcheckdate = $localkeyresults['checkdate'];

                    if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                        $localexpiry = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - $localkeydays, date('Y')));

                        if ($localexpiry < $originalcheckdate) {
                            $localkeyvalid = true;
                            $results = $localkeyresults;
                            $validdomains = explode(',', $results['validdomain']);

                            if (!empty($_SERVER['SERVER_NAME']) && !in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                                $localkeyvalid = false;
                                $localkeyresults['status'] = 'Invalid';
                                $results = array();
                            }


                            $validips = explode(',', $results['validip']);

                            if (!empty($usersip) && !in_array($usersip, $validips)) {
                                $localkeyvalid = false;
                                $localkeyresults['status'] = 'Invalid';
                                $results = array();
                            }


                            $validdirs = explode(',', $results['validdirectory']);

                            if (!in_array($dirpath, $validdirs)) {
                                $localkeyvalid = false;
                                $localkeyresults['status'] = 'Invalid';
                                $results = array();
                            }

                        }

                    }

                }

            }


            if (!$localkeyvalid) {
                $postfielDIRECTORY_SEPARATOR = array('licensekey' => $licensekey, 'domain' => $domain, 'ip' => $usersip, 'dir' => $dirpath);

                if ($check_token) {
                    $postfielDIRECTORY_SEPARATOR['check_token'] = $check_token;
                }


                $query_string = http_build_query($postfielDIRECTORY_SEPARATOR);
                $http_code = 0;

                if (function_exists('curl_exec')) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDIRECTORY_SEPARATOR, $query_string);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $data = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                } else {
                    $fp = fsockopen($whmcshostname, 80, $errno, $errstr, 5);

                    if ($fp) {
                        $newlinefeed = "\r\n";
                        $header = 'POST ' . $whmcsurl . $verifyfilepath . ' HTTP/1.0' . $newlinefeed;
                        $header .= 'Host: ' . $whmcsurl . $newlinefeed;
                        $header .= 'Content-type: application/x-www-form-urlencoded' . $newlinefeed;
                        $header .= 'Content-length: ' . @strlen($query_string) . $newlinefeed;
                        $header .= 'Connection: close' . $newlinefeed . $newlinefeed;
                        $header .= $query_string;
                        $data = '';
                        @stream_set_timeout($fp, 20);
                        @fputs($fp, $header);
                        $status = @socket_get_status($fp);

                        while (@socket_get_status($fp) && !@feof($fp) && $status) {
                            $data .= @fgets($fp, 1024);
                            $status = @socket_get_status($fp);
                        }
                        @fclose($fp);
                    }

                }

                list($headerline) = explode("\r\n", $data, 2);

                if (preg_match('/(?<http>[A-Z]{4,5})\\/(?<http_version>[0-9\\.]+) (?<http_code>[0-9]{3})/', trim($headerline), $headers)) {
                    $http_code = $headers['http_code'];
                }


                if (!$data || ($http_code != '200')) {
                    $localexpiry = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - ($localkeydays + $allowcheckfaildays), date('Y')));
                    $lenght = strlen($localkeyresults['checktoken']) - 32;
                    $timestamp = substr($localkeyresults['checktoken'], 0, $lenght);
                    $originalcheckdate = date('Ymd', $timestamp);

                    if ($localexpiry < $originalcheckdate) {
                        $results = $localkeyresults;
                        $checkdate = $results['checkdate'];
                        $check_token = $results['checktoken'];
                    } else {
                        $results['status'] = 'Invalid';
                        $results['description'] = 'Remote Check Failed';
                        return $results;
                    }
                } else {
                    preg_match_all('/<(.*?)>([^<]+)<\\/\\1>/i', $data, $matches);
                    $results = array();

                    foreach ($matches[1] as $k => $v) {
                        $results[$v] = $matches[2][$k];
                    }
                }

                if (!is_array($results)) {
                    $results['status'] = 'Invalid';
                    $results['description'] = 'Invalid License Server Response';
                    return $results;
                }


                if ($results['md5hash']) {
                    if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                        $results['status'] = 'Invalid';
                        $results['description'] = 'MD5 Checksum Verification Failed';
                        return $results;
                    }

                }


                if ($results['status'] == 'Active') {
                    $results['checkdate'] = $checkdate;
                    $results['checktoken'] = $check_token;
                    $data_encoded = serialize($results);
                    $data_encoded = base64_encode($data_encoded);
                    $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
                    $data_encoded = strrev($data_encoded);
                    $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
                    $data_encoded = wordwrap($data_encoded, 80, "\n", true);
                    $results['localkey'] = $data_encoded;
                }


                $results['remotecheck'] = true;
            }


            unset($postfielDIRECTORY_SEPARATOR);
            unset($data);
            unset($matches);
            unset($whmcsurl);
            unset($licensing_secret_key);
            unset($checkdate);
            unset($usersip);
            unset($localkeydays);
            unset($allowcheckfaildays);
            unset($md5hash);

            if (isset($results['localkey']) && ($results['localkey'] != '')) {
                $query_result = mysql_query('SELECT value FROM tblconfiguration WHERE setting = \'openstack_vps_localkey\'');
                $query_row = mysql_fetch_assoc($query_result);

                if (isset($query_row['value'])) {
                    mysql_query('UPDATE tblconfiguration SET value = \'' . mysql_real_escape_string($results['localkey']) . '\' WHERE setting = \'openstack_vps_localkey\'');
                } else {
                    mysql_query('INSERT INTO tblconfiguration (setting,value) VALUES (\'openstack_vps_localkey\',\'' . mysql_real_escape_string($results['localkey']) . '\')');
                }
            }


            switch ($results['status']) {
                case 'Active':
                    $results['description'] = 'Your module license is active.';
                    break;

                case 'Invalid':
                    $results['description'] = 'Your module license is invalid.';
                    break;

                case 'Expired':
                    $results['description'] = 'Your module license has expired.';
                    break;

                case 'Suspended':
                    $results['description'] = 'Your module license is suspended.';
                    break;

                case 'Error':
                    if (!$results['description']) {
                        $results['description'] = 'Connection not possible. Please report your server IP to contact@modulesgarden.com';
                    }


                    break;

                    $results['description'] = 'Connection not possible. Please report your server IP to contact@modulesgarden.com';
            }

            return $results;
        }*/
    }finally{}
}
?>