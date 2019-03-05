{**********************************************************************
 * Customization Services by ModulesGarden.com
 * Copyright (c) ModulesGarden, INBS Group Brand, All Rights Reserved 
 * (2014-03-27)
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
	
    <h3 class="set_main_header">{$lang.logs.main_header}</h3> 
      <div id="vm_alerts">
            {if $error_msg}
                   <div class="box-error">{$error_msg}</div>
            {/if}
            {if $success_msg && !$error_msg}
                <div class="box-success">{$success_msg}</div>
            {/if}
      </div>


       <table class="table table-bordered">
             <thead>
                   <tr>
                         <th>{$lang.logs.task}</th>
                         <th>{$lang.logs.created}</th>
                         <th>{$lang.logs.last_update}</th>
                         <th>{$lang.logs.attempts}</th>
                         <th>{$lang.logs.message}</th>
                         <th>{$lang.logs.status}</th>
                   </tr>  
             </thead>     
             <tbody>
                   {foreach from=$activeTasks item=task}
                         <tr>
                               <td>{$task.action}</td>
                               <td>{$task.createDate}</td>
                               <td>{$task.lastAttemptDate}</td>
                               <td>{$task.attempt}</td>
                               <td>{$task.message}</td>
                               <td>{$task.status}</td>
                               
                         </tr>
                   {foreachelse}
                         <tr>
                               <td colspan="6">{$lang.logs.msg_no_tasks}</td>
                         </tr>
                   {/foreach}
                   
                   
             </tbody>
       </table>
</div>
