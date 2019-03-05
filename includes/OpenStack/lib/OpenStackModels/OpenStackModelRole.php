<?php

//Decoded by SoarTeam SoarTeam
class OpenStackModelRole extends OpenStackModel
{
	public $id;
	public $name;
	public function listSource()
	{
		return OpenStackAPI::getInstance()->identity()->listRoles();
	}
}