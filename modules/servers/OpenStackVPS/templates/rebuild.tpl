{**********************************************************************
 * Customization Services by ModulesGarden.com
 * Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
 * (2014-03-21, 08:19:48)
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

<div class="rebuild" style="min-height: 400px;">
    <a href="{$serviceMainUrl}" class="btn btn-small"><i class="icon-arrow-left"></i> {$lang.general.back}</a>
	<p class='clear'>&nbsp;</p>
	
    <h3 class="set_main_header">{$lang.rebuild.main_header}</h3> 
    <div id="vm_alerts"></div>
	
	<form method="post">
		<table class="table table-bordered">
			<tr>
				<td style="padding-top:20px;">{$lang.rebuild.image}</td>
				<td>
					<select name="reb_image">
						{foreach from=$images item="img"}
							<option value="{$img->UUID}">{$img->name}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<!--<tr>
				<td style="padding-top:20px;">{$lang.rebuild.password}</td>
				<td>
					<input type="text" name="reb_password" style="width: 291px !important;"/>
				</td>
			</tr>-->
		</table>
		<input type="submit" value="{$lang.rebuild.rebuild_button}" class="btn btn-success" id="rebuild_vm" />
	</form>
</div>
				
<script type="text/javascript">
{literal}
jQuery(document).ready(function(){
	
	var OSUI = {
		'clearMessages': function(){
			$("#vm_alerts").html('');
		},
		'addMessage': function(success, msg){
			var cl = success ? "box-success" : "box-error";
			$("#vm_alerts").append('<div class="'+cl+'">'+msg+'</div>');
		},
		'addLoading': function(){
			$("#vm_alerts").html('<div style="margin:10px 0;"><img src="{/literal}{$assetsUrl}{literal}img/ajax-loader.gif" /> {/literal}{$lang.general.pleasewait}{literal}</div>');
		}
	};
	
	jQuery("#rebuild_vm").click(function(){
		var vm = {
			'image': jQuery("select[name=reb_image]").val()
		};
			
		if (!vm.image){
			alert("Please select all of options for VM");
			return false;
		}
		
		jQuery(this).closest("form").remove();
			
		OSUI.addLoading();
		jQuery.post("{/literal}{$servicePageUrl}{literal}act=ajaxAct",{'subaction':'rebuild', 'vm':'{/literal}{$vmid}{literal}', 'image':vm.image},function(res){
			OSUI.clearMessages();
			if (res.result == '1'){
				OSUI.addMessage(true, res.msg);
				window.location.href = "{/literal}{$serviceMainUrl}{literal}";
			} else {
				OSUI.addMessage(false, res.msg);
			}
			
		}, 'json');
			
		return false;
	});
	
});
{/literal}
</script>