{**********************************************************************
* Customization Services by ModulesGarden.com
* Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
* (2014-03-12, 14:26:56)
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
**********************************************************************}

{**
* @author Grzegorz Draganik <grzegorz@modulesgarden.com>
*}

<link rel="stylesheet" type="text/css" href="{$assetsUrl}style.css" />
<div>
    <h2 class="set_main_header">{$lang.vm.main_header}</h2> 
	<div id="vm_alerts">
		{if $error_msg}
			<div class="box-error">{$error_msg}</div>
		{/if}
		{if $success_msg && !$error_msg}
			<div class="box-success">{$success_msg}</div>
		{/if}
	</div>
    {if $vm->UUID}
        <div id="serverstats">
            <table width="90%" class="table table-striped">
                <tr><td>{$lang.vm.refresh}</td><td><span id="serverstatus" style="display: none;"><img src="modules/servers/OpenStackVPS/assets/img/loadingsml.gif"></span><a href="#" onclick="OpenStackDoAction('details');return false;"><img src="{$assetsUrl}/img/refresh.png" alt="" /></a></td></tr>
                <tr><td>{$lang.vm.name}</td><td class="vps_label">{$vm->name}</td></tr>
                <tr><td>{$lang.vm.status}</td><td id="vm_status">{if $vm->status == "ACTIVE"}<span class="green">{$vm->status}</span>{else}<span class="red">{$vm->status}</span>{/if}</td></tr>
                <tr><td>{$lang.vm.image}</td><td>{$image->name}</td></tr>
                {if $flavor->name}
					<tr><td>{$lang.vm.fl_name}</td><td>{$flavor->name}</td></tr>
				{/if}
                <tr><td>{$lang.vm.fl_disk}</td><td>{$flavor->disk} GB</td></tr>
                <tr><td>{$lang.vm.fl_ram}</td><td>{$flavor->ram} MB</td></tr>
                <tr><td>{$lang.vm.fl_vcpus}</td><td>{$flavor->vcpus}</td></tr>
            </table>
        </div>  

        <div id="rbuttons">
			<button class="btn" rel="start" onclick="return OpenStackDoAction('start');" style="{if !$vm_actions.start}display:none;{/if}">
				<img class="manage_img" src="{$assetsUrl}img/power_on.png"/> {$lang.vm.start}</button>
			<button class="btn" rel="stop" onclick="return OpenStackDoAction('stop');" style="{if !$vm_actions.stop}display:none;{/if}">
				<img class="manage_img" src="{$assetsUrl}img/power_off.png"/> {$lang.vm.stop}</button>
			<button class="btn" rel="pause" onclick="return OpenStackDoAction('pause');" style="{if !$vm_actions.pause}display:none;{/if}">
				<img class="manage_img" src="{$assetsUrl}img/pause_red.png"/> {$lang.vm.pause}</button>
			<button class="btn" rel="unpause" onclick="return OpenStackDoAction('unpause');" style="{if !$vm_actions.unpause}display:none;{/if}">
				<img class="manage_img" src="{$assetsUrl}img/control_pause.png"/> {$lang.vm.unpause}</button>
                {if $perm.caf_resume}
				<button class="btn" rel="resume" onclick="return OpenStackDoAction('resume');" style="{if !$vm_actions.resume}display:none;{/if}">
					<img class="manage_img" src="{$assetsUrl}img/recovery.png"/> {$lang.vm.resume}</button>
                {/if}

			{if $perm.caf_softreboot}
				<button class="btn" rel="softreboot" onclick="return OpenStackDoAction('softreboot');" style="{if !$vm_actions.softreboot}display:none;{/if}">
					<img class="manage_img" src="{$assetsUrl}img/reboot.png"/> {$lang.vm.softreboot}</button>
                {/if}
                {if $perm.caf_hardreboot}
				<button class="btn" rel="hardreboot" onclick="return OpenStackDoAction('hardreboot');" style="{if !$vm_actions.hardreboot}display:none;{/if}">
					<img class="manage_img" src="{$assetsUrl}img/stock_repeat.png"/> {$lang.vm.hardreboot}</button>
                {/if}
                {if $perm.caf_resetnetwork}
				<button class="btn" rel="resetNetwork" onclick="return OpenStackDoAction('resetNetwork');" style="{if !$vm_actions.resetNetwork}display:none;{/if}">
					<img class="manage_img" src="{$assetsUrl}img/network2.png"/> {$lang.vm.reset_network}</button>
                {/if}
                {if $perm.caf_rebuild}
				<button class="btn" onclick="window.location.href ='{$servicePageUrl}&act=rebuild';">
					<img class="manage_img" src="{$assetsUrl}img/rebuild.png"/>{$lang.vm.rebuild}</button>
                {/if}
        </div>

		{if $perm.caf_backups || $perm.caf_console}
			<h3 class="header_label">{$lang.vm.additionals}</h3>
			<div id='nbuttons'>
				{if $perm.caf_backups && !$perm.use_volumes}
				<button class="btn"class="btn" onclick="window.location.href='{$servicePageUrl}&act=backups';">
					<img class="manage_img" src="{$assetsUrl}img/backup.png"/> {$lang.vm.backups}</button>
				{/if}
				{if $perm.caf_console}
				<button class="btn" onclick="window.open('{$servicePageUrl}&act=console','{$lang.vm.console},target=_blank','width=950,height=780,resizable=yes');return false;">
					<img class="manage_img" src="{$assetsUrl}img/console.png"/> {$lang.vm.console}</button>
				{/if}
				{if $perm.caf_keypair && $isPrivateKey}
				<button class="btn"class="btn" id="OpenStack_privateKeyDoownload" onclick="{if $delete_keypair}OpenStack_privateKeyDoownload();{else}window.location.href='{$servicePageUrl}&act=keyDownload&keytype=private';{/if}">
					<img class="manage_img" src="{$assetsUrl}img/notes.png"/> {$lang.keypair.download_private}</button>
                            {/if}
                            {if $perm.caf_keypair && $isPublicKey}
				<button class="btn"class="btn" onclick="window.location.href='{$servicePageUrl}&act=keyDownload&keytype=public';">
					<img class="manage_img" src="{$assetsUrl}img/notes.png"/> {$lang.keypair.download_public}</button>
				{/if}
				<!--
				{if $perm.caf_scheduled_logs}
				<button class="btn" onclick="window.location.href='{$servicePageUrl}&act=logs';" >
					<img class="manage_img" src="{$assetsUrl}img/notes.png"/> {$lang.logs.button}</button>
				{/if}
				-->
			</div>
		{/if}
        <h3 class="header_label">{$lang.vm.interfaces}</h3>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>{$lang.vm.int_fixedIP}</th>
					<th>{$lang.vm.int_floatingIP}</th>
					<th>{$lang.vm.int_mac}</th>
				</tr>
			</thead>
			{foreach from=$interfaces item="int"}
				<tr>
					<td>{$int->fixedIP}</td>
					<td>{$int->floatingIP}</td>
					<td>{$int->mac}</td>
				</tr>
			{foreachelse}
				<tr><td colspan="3">{$lang.vm.no_interfaces}</td></tr>
			{/foreach}
		</table>
		
		
		{if $perm.caf_scheduled_logs}
			<h3 class="set_main_header">{$lang.logs.main_header}</h3>
			<table class="table table-bordered">
				 <thead>
					   <tr>
							 <th>{$lang.logs.task}</th>
							 <th>{$lang.logs.created}</th>
							 <th>{$lang.logs.last_update}</th>
							 <th>{$lang.logs.status}</th>
					   </tr>  
				 </thead>     
				 <tbody>
					   {foreach from=$activeTasks item=task}
							 <tr>
								   <td>{$task.action}</td>
								   <td>{$task.createDate}</td>
								   <td>{$task.lastAttemptDate}</td>
								   <td>{$task.status}</td>

							 </tr>
					   {foreachelse}
							 <tr>
								   <td colspan="6">{$lang.logs.msg_no_tasks}</td>
							 </tr>
					   {/foreach}


				 </tbody>
		   </table>
		{/if}
		
		
	{/if}
</div>

<script type="text/javascript">
	{literal}
	
	var OSUI = {
		'clearMessages': function(){
			$("#vm_alerts").html('');
		},
		'addMessage': function(success, msg){
			var cl = success ? "box-success" : "box-error";
			$("#vm_alerts").show().append('<div class="'+cl+'">'+msg+'</div>').delay(8200).fadeOut(300);;
		},
		'addLoading': function(){
			$("#vm_alerts").show().html('<div style="margin:10px 0;"><img src="modules/servers/OpenStackVPS/assets/img/loadingsml.gif" /> {/literal}{$lang.general.pleasewait}{literal}</div>');
		}
	};
	
	function OpenStackDoAction(action){
       
              if(action=="details"){
                    $("#serverstatus").show();
              }else{
                    OSUI.clearMessages();
                    OSUI.addLoading();
              }
		jQuery.post("{/literal}{$servicePageUrl}{literal}act=ajaxAct",{'subaction': action, 'vm':'{/literal}{$vm->UUID}{literal}'},function(res){
                    if(action=="details"){
                          $("#serverstatus").hide();
                          if(res.vm_status == "ACTIVE")
                                jQuery("#vm_status").html('<span class="green">'+res.vm_status+'</span>');
                          else 
                                jQuery("#vm_status").html('<span class="red">'+res.vm_status+'</span>');
                          
                    }else{
                          OSUI.clearMessages();
                          OSUI.addLoading();
                          OSUI.clearMessages();
                          if (res.result == '1'){
                                 OSUI.addMessage(true, res.msg);
                          }else
                                 OSUI.addMessage(false, res.msg);
                    }
					if (res.vm_actions){
						showHideButtons(res.vm_actions);
					}
		}, 'json');
		return false;
	}
       
       function OpenStack_privateKeyDoownload(){
             if(confirm("{/literal}{$lang.keypair.doownload_private_key}{literal}")){
                   $("#OpenStack_privateKeyDoownload").hide();
                   window.location.href='{/literal}{$servicePageUrl}{literal}&act=keyDownload&keytype=private';
             }
       }
       
	jQuery(document).ready(function(){
		setInterval("OpenStackDoAction('details')",20000);
	});
	function showHideButtons(vm_actions){
		var $buttons = jQuery("#rbuttons");
		$buttons.find("a[rel], button[rel]").hide();
		for (var a = 0; a < vm_actions.length; a++){
			$buttons.find("[rel="+vm_actions[a]+"]").show();
		}
	}
	{/literal}
</script>
