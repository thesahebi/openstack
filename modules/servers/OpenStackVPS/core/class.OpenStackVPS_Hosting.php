<?php

//Decoded by SoarTeam SoarTeam
class OpenStackVPS_Hosting extends MG_Hosting
{
	public function setIPAddresses($interfaces)
	{
		$this->load();
		$tabIP = array();
		foreach ($interfaces as $int) {
			if ($int->floatingIP != '') {
				$tabIP[] = $int->floatingIP;
			}
			if ($int->fixedIP != '') {
				$tabIP[] = $int->fixedIP;
			}
		}
		if (isset($tabIP[0]) && $tabIP[0] != '') {
			$this->updateDetails(array('dedicatedip' => $tabIP[0]));
			unset($tabIP[0]);
		} else {
			$this->updateDetails(array('dedicatedip' => ''));
		}
		if (!empty($tabIP)) {
			$this->updateDetails(array('assignedips' => implode("\n", $tabIP)));
		} else {
			$this->updateDetails(array('assignedips' => ''));
		}
	}
}