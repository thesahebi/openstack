{**********************************************************************
 * Customization Services by ModulesGarden.com
 * Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
 * (2014-03-24)
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
 * @author Paweł Kopeć <pawelk@modulesgarden.com>
 *}
<link rel="stylesheet" type="text/css" href="{$assetsUrl}style.css" />

<div class="rebuild" style="min-height: 400px;">
    <a href="{$serviceMainUrl}" class="btn btn-small"><i class="icon-arrow-left"></i> {$lang.general.back}</a>
	<p class='clear'>&nbsp;</p>
	
    <h3 class="set_main_header">{$lang.backups.main_header}</h3> 
      <div id="vm_alerts">
            {if $error_msg}
                   <div class="box-error">{$error_msg}</div>
            {/if}
            {if $success_msg && !$error_msg}
                <div class="box-success">{$success_msg}</div>
            {/if}
      </div>
	
       <form method="post">
       <table class="table table-bordered mg-backup-table">
             <thead>
                   <tr>
                         <th style="text-aling:center; width:4%;">{if $backupSurces} <input id="select-all" type="checkbox">{/if}</th>
                         <th>{$lang.backups.name}</th>
                         <th>{$lang.backups.created}</th> 
                         <th>{$lang.backups.status}</th>
                         <th style="width:100px; text-align: center;">{$lang.backups.action}</th>
                   </tr>  
             </thead>     
             <tbody>
                   {foreach from=$backupSurces item=backupSurce}
                         <tr>
                               <td><input class="select-me" type="checkbox" value="{$backupSurce.id}" name="deletes[]" /></td>
                               <td>{$backupSurce.name}</td>
                               <td>{$backupSurce.created|date_format}</td>
                               <td>{$backupSurce.status}</td>
                               <td style="text-align: center;"><a class="btn btn-warning vmRestore" href="{$servicePageUrl}&act=backups&restoreid={$backupSurce.id}">{$lang.backups.restore}</a></td>
                         </tr>
                   {foreachelse}
                         <tr>
                               <td colspan="6" style="text-align: center">{$lang.backups.no_backups}</td>
                         </tr>
                   {/foreach}
                   
                   
             </tbody>
       </table>
       {if $backupSurces} 
             <div>
                   <input type="submit" name="delete" value="{$lang.backups.delete}" class="btn btn-danger" id="deleteBackups" />
             </div>
       {/if}
                   
       </form>  
       {if $isCreateBackup}
           <h3 class="header_label" style="margin-top:20px;">{$lang.backups.create_backup}</h3>
           {if $backupRouting && $backupsFilesLimit!="-1"}
                 <div class="alert alert-warning"> {$lang.backups.msg_backup_routing|replace:'%d':$backupsFilesLimit}</div>
           {/if}
            <form method="post">
                    <table class="table table-bordered mg-backup-table">
                            <tr>
                                    <td>{$lang.backups.name}</td>
                                    <td>
                                            <input type="text" name="newbackup[name]" />
                                    </td>
                            </tr>
                    </table>
                    <input type="submit" value="{$lang.backups.backups_button}" class="btn btn-success" id="rebuild_vm" />
            </form>
        {/if}
</div>
{literal}
    <style>
        .mg-backup-table td{
            vertical-align: middle !important;
        }
         
    </style>
<script type="text/javascript">
      $(document).ready(function() {
            $('#select-all').click(function (event){
                  var selected = this.checked;
                  $(':checkbox').each(function () {    this.checked = selected; });
            });
            $("#deleteBackups").click(function (){
                  return confirm("{/literal}{$lang.backups.msg_pre_delete}{literal}");
            });
            $(".vmRestore").click(function (){
                  return confirm("{/literal}{$lang.backups.msg_pre_restore}{literal}");
            });
      });
</script>
{/literal}