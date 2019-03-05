<?php

//Decoded by SoarTeam SoarTeam
class OpenStackModelNIC extends OpenStackModel
{
	public $portID;
	public $fixedIP;
	public $fixedNetwork;
	public $floatingIP;
	public $floatingNetwork;
	public $mac;
	public function setFixedNetwork(OpenStackModelNetwork $network)
	{
		$this->fixedNetwork = $network->UUID;
	}
	public function setFloatingNetwork(OpenStackModelNetwork $network)
	{
		$this->floatingNetwork = $network->UUID;
	}
}