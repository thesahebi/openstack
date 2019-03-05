<?php

//Decoded by SoarTeam SoarTeam
abstract class MG_Clientarea
{
	public function init($act, $params, $lang)
	{
	}
	public function run($action, $params)
	{
		if (!method_exists($this, $action . 'Action')) {
			throw new Exception('Client Area action not found');
		}
		$action .= 'Action';
		return $this->{$action}($params);
	}
}