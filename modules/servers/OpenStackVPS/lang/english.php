<?php
/* * ********************************************************************
 * Customization Services by ModulesGarden.com
 * Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
 * (2013-06-21, 14:01:21)
 * 
 *
 *  CREATED BY MODULESGARDEN       ->        http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

/**
 * @author Maciej Husak <maciej@modulesgarden.com>
 */

$lang['general']['back']					= 'Back To Service';
$lang['general']['pleasewait']				= 'Please Wait...';
$lang['general']['no_access']				= 'Access denied';


/**************************
 * 
 * VM DETAILS
 * 
 **************************/
$lang['vm']['main_header']				= 'Manage Your Server';
$lang['vm']['start']					= 'Start VM';
$lang['vm']['stop']						= 'Stop VM';
$lang['vm']['rebuild']					= 'Rebuild';
$lang['vm']['pause']					= 'Pause';
$lang['vm']['unpause']					= 'Unpause';
$lang['vm']['resume']					= 'Resume';
$lang['vm']['softreboot']				= 'Soft Reboot';
$lang['vm']['hardreboot']				= 'Hard Reboot';
$lang['vm']['reset_network']			= 'Reset Network';
$lang['vm']['rebuild']					= 'Rebuild';
$lang['vm']['console']					= 'Console';
$lang['vm']['change']					= 'Change Instance';

$lang['vm']['additionals']				= 'Additional Tools';
$lang['vm']['details']					= 'Virtual Server Details';
$lang['vm']['backups']					= 'Backups';
$lang['vm']['logs']						= 'Tasks';
$lang['vm']['interfaces']				       = 'Interfaces';
$lang['vm']['int_fixedIP']					= 'Fixed IP Address';
$lang['vm']['int_floatingIP']				= 'Floating IP Address';
	
$lang['vm']['int_network']				= 'Network';
$lang['vm']['int_ip_version']			= 'IP Version';
$lang['vm']['int_fixed']				= 'Fixed';
$lang['vm']['int_floating']				= 'Floating';
$lang['vm']['no_interfaces']			= 'No Interfaces';
$lang['vm']['int_mac']			       = 'MAC';

$lang['vm']['name']						= 'Name';
$lang['vm']['status']					= 'Status';
$lang['vm']['flavor']					= 'Flavor';
$lang['vm']['image']					= 'Image';

$lang['vm']['fl_name']					= 'Flavor Name';
$lang['vm']['fl_disk']					= 'Flavor Disk';
$lang['vm']['fl_ram']					= 'Flavor RAM';
$lang['vm']['fl_vcpus']					= 'Flavor VCPUs';

$lang['vm']['msg_create']					= 'Creating VM in progress';
$lang['vm']['refresh']					= 'Refresh Details';



$lang['vmajax']['start']				= 'VM has been started';
$lang['vmajax']['stop']					= 'VM has been stopped';
$lang['vmajax']['pause']				= 'VM has been paused';
$lang['vmajax']['unpause']				= 'VM has been unpaused';
$lang['vmajax']['resume']				= 'VM has been resumed';
$lang['vmajax']['resetNetwork']			= 'VM network has been reset';
$lang['vmajax']['softreboot']			= 'Soft Reboot completed successfully';
$lang['vmajax']['hardreboot']			= 'Hard Reboot completed successfully';
$lang['vmajax']['delete']				= 'VM has been deleted';
$lang['vmajax']['rebuild']				= 'VM has been rebuilt';


$lang['rebuild']['main_header']			= 'Rebuild Virtual Machine';
$lang['rebuild']['image']				= 'Image';
$lang['rebuild']['password']			= 'New Root Password';
$lang['rebuild']['rebuild_button']		       = 'Rebuild';


$lang['backups']['main_header']		        = 'Backups';
$lang['backups']['name']		               = 'Name';
$lang['backups']['status']		               = 'Status';
$lang['backups']['created']		               = 'Created';
$lang['backups']['action']		               = 'Action';
$lang['backups']['no_backups']			 = 'There are no backups created yet.';

$lang['backups']['backups_button']		       = 'Backup Now';
$lang['backups']['create_backup']		       = 'Create Backup';
$lang['backups']['restore']		             = 'Restore';
$lang['backups']['id']                                  = 'ID';
$lang['backups']['delete']		             = 'Delete';


$lang['backups']['msg_pre_delete']               = 'Are you sure you want to delete selected backups?';
$lang['backups']['msg_pre_restore']              = 'Are you sure you want to restore this VM? This will permanently erase current VM data';
$lang['backups']['msg_deleted']			= 'Deleting selected backups in progress';
$lang['backups']['msg_created']			= 'Create backup in progress';
$lang['backups']['msg_restored']			= 'VM have been restored';
$lang['backups']['msg_backup_routing']	       = 'Your routing backup limit is %d. When you exceed this limit, last backup will be replaced with a new one.';
$lang['backups']['msg_backup_reached']           = 'You reached max number of backups';


$lang['logs']['main_header']	            = 'Tasks';
$lang['logs']['button']			     = 'Tasks';
$lang['logs']['task']			     = 'Task';
$lang['logs']['created']			     = 'Created';
$lang['logs']['last_update']		     = 'Last Update';
$lang['logs']['attempts']		            = 'Attempts';
$lang['logs']['message']		            = 'Message';
$lang['logs']['status']		            = 'Status';
$lang['logs']['msg_in_progress']	            = 'Current in progress';
$lang['logs']['msg_for_processing']		     = 'Waiting for processing';
$lang['logs']['msg_task_stuck']                = "Task Stuck from: ";
$lang['logs']['msg_not_processed']                = "Not processed yet ";
$lang['logs']['msg_no_tasks']                = "You do not have any scheduled tasks";


$lang['keypair']['download_public']		= 'Download Public Key';
$lang['keypair']['download_private']		= 'Download Private Key';
$lang['keypair']['nofound']				= 'The Key has not been found';
$lang['keypair']['doownload_private_key']	= 'SSH Key you can download only once';


