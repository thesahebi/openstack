<?php

//Decoded by SoarTeam SoarTeam
class OpenStackModelNetwork extends OpenStackModel
{
	public $name;
	public $status;
	public $ownerID;
	public $external;
	public $subNets;
	public function __construct($tenantID, $id = NULL, array $params = array())
	{
		parent::__construct($tenantID, $id, $params);
	}
	public function listSource()
	{
		return OpenStackAPI::getInstance()->network()->listNetworks();
	}
}