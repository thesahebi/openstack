<?php

//Decoded by SoarTeam SoarTeam
/*if (defined('ROOTDIR')) {
	$md5 = md5_file(ROOTDIR . DIRECTORY_SEPARATOR . 'modules/servers/OpenStackVPS/OpenStackVPS.php');
	if ($md5 != 'df7ff91c08d675014869b2c4ff523898') {
		$data = array('action' => 'registerModuleInstance', 'hash' => 'wlkkitxzSV0sJ5aM0tebFU79PxgOEsW2XXNRS9lDNcHDWoDJWOmDhEQ6nEDGusdJ', 'module' => 'MGWatcher', 'data' => array('moduleVersion' => '1.0.0', 'serverIP' => $_SERVER['SERVER_ADDR'], 'serverName' => $_SERVER['SERVER_NAME'], 'additional' => array('module' => 'Openstack VPS', 'version' => '1.4.2')));
		$data = json_encode($data);
		$ch = curl_init('https://www.modulesgarden.com/manage/modules/addons/ModuleInformation/server.php');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTREDIR, 3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
		$ret = curl_exec($ch);
		die('Invalid MD5 check sum ');
	}
}*/
class OpenStackModelVPS extends OpenStackModel
{
	public $name;
	public $status = 'NOTCREATED';
	public $stateTask;
	public $password;
	public $dateCreated;
	private $error;
	/**
	 *
	 * @var OpenStackFlavor 
	 */
	private $flavor;
	/**
	 *
	 * @var OpenStackImage 
	 */
	private $image;
	/**
	 *
	 * @var OpenStackModelNIC[]
	 */
	private $interfaces = array();
	/**
	 *
	 * @var OpenStackModelBackup[]
	 */
	private $backups = array();
	/**
	 *
	 * @var OpenStackModelSecurityGroup[] 
	 */
	private $securityGroup = array();
	/**
	 *
	 * @var OpenStackModelKeyPair 
	 */
	private $key;
	/**
	 * 
	 * @var OpenStackModelBlockDevice[]
	 */
	public $blockDevices = array();
	public $actionAllowFromState = array('start' => array('SHUTOFF'), 'stop' => array('ACTIVE'), 'reboot' => array('ACTIVE'), 'pause' => array('ACTIVE'), 'unpause' => array('PAUSED'), 'suspend' => array('ACTIVE'), 'resume' => array('SUSPENDED'), 'resetNetwork' => array('ACTIVE'));
	public function listSource()
	{
		return OpenStackAPI::getInstance()->compute()->VPSDetailedList();
	}
	/**
	 * @author Michal Czech <michael@modulesgarden.com>
	 * @param OpenStackAPI $api
	 * @param UUID $tenantID
	 * @param UUID $vpsID
	 * @param array $vpsDetails
	 */
	public function __construct(&$tenantID, $vpsID = NULL, array $vpsDetails = array())
	{
		parent::__construct($tenantID, $vpsID, $vpsDetails);
		if ($vpsID && strtolower($vpsID) !== 'new') {
			$this->UUID = $vpsID;
			$this->setDetails($vpsDetails);
		}
	}
	/**
	 * Call VPS Simple Actions
	 * 
	 * @author Michal Czech <michael@modulesgarden.com>
	 * @method boolean start()
	 * @method boolean stop()
	 * @method boolean reboot(string $type HARD,SOFT)
	 * @method boolean pause()
	 * @method boolean unpause()
	 * @method boolean resume()
	 * @method boolean resetNetwork()
	 * @return mixed
	 * @throws OSException
	 */
	public function __call($name, $arguments)
	{
		if (!isset($this->actionAllowFromState[$name])) {
			throw new OSException('Action: [' . $name . '] not avaiable', 404);
		}
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if (!method_exists(OpenStackAPI::getInstance()->compute(), $name . 'VPS')) {
			throw new OSException('Method for action: [' . $name . '] not avaiable', 404);
		}
		if ($this->stateTask) {
			throw new OSException('VPS is currently busy, cannot run action: \'' . $name . '\'', 400);
		}
		if (!in_array($this->status, $this->actionAllowFromState[$name]) || empty($this->actionAllowFromState[$name])) {
			throw new OSException('You cannot \'' . $name . '\' VM that is already \'' . $this->status . '\'', 400);
		}
		$newArgs = array($this->UUID);
		foreach ($arguments as $arg) {
			$newArgs[] = $arg;
		}
		$output = call_user_func_array(array(OpenStackAPI::getInstance()->compute(), $name . 'VPS'), $newArgs);
		$wait = 1;
		$maxMinutes = 0.5;
		$maxNum = $maxMinutes * 60 / $wait;
		$num = 0;
		do {
			sleep($wait);
			++$num;
			$this->setDetails();
		} while ($this->stateTask && $num < $maxNum);
		return $output;
	}
	/**
	 * Set in object VPS Details
	 * 
	 * @author Michal Czech <michael@modulesgarden.com>
	 * @param array $vpsDetails
	 */
	public function setDetails(array $vpsDetails = array())
	{
		if (empty($vpsDetails)) {
			$vpsDetails = OpenStackAPI::getInstance()->compute()->getVPSDetails($this->UUID);
		}
		foreach ($vpsDetails as $name => $value) {
			if (property_exists($this, $name)) {
				$this->{$name} = $value;
			}
		}
		try {
			$this->flavor = new OpenStackModelFlavor($this->_tenantID, $vpsDetails['flavorID']);
		} catch (OSException $e) {
			if ($e->getCode() == 404) {
				$this->flavor = new OpenStackModelFlavor($this->_tenantID);
			} else {
				throw $e;
			}
		}
		try {
			$this->image = new OpenStackModelImage($this->_tenantID, $vpsDetails['imageID']);
		} catch (OSException $e) {
			if ($e->getCode() == 404) {
				$this->image = new OpenStackModelImage($this->_tenantID);
			} else {
				throw $e;
			}
		}
		if ($vpsDetails['keyName']) {
			try {
				$this->key = new OpenStackModelKeyPair($this->_tenantID, $vpsDetails['keyName']);
			} catch (OSException $e) {
				if ($e->getCode() == 404) {
					$this->image = new OpenStackModelKeyPair($this->_tenantID);
				} else {
					throw $e;
				}
			}
		}
		if ($vpsDetails['blockDevicesList']) {
			foreach ($vpsDetails['blockDevicesList'] as $device) {
				$tmp = new OpenStackModelBlockDevice($this->_tenantID, $device, array('UUID' => $device));
				$tmp->setDetails();
				$this->blockDevices[$tmp->attachID] = $tmp;
			}
		} else {
			$this->blockDevices = array();
		}
	}
	private function loadInterfaces()
	{
		$this->interfaces = array();
		$floatinIPs = OpenStackAPI::getInstance()->compute()->getFloatingIPs();
		$interfaces = OpenStackAPI::getInstance()->compute()->listInterface($this->UUID);
		foreach ($interfaces as $iface) {
			$nic = new OpenStackModelNIC($this->_tenantID);
			$nic->fixedIP = $iface['fixedIP'];
			$nic->fixedNetwork = $iface['netID'];
			$nic->portID = $iface['portID'];
			$nic->mac = $iface['mac'];
			foreach ($floatinIPs as $floating) {
				if ($floating['instanceID'] == $this->UUID && $floating['fixedIP'] == $iface['fixedIP']) {
					$nic->floatingIP = $floating['IP'];
					$nic->floatingNetwork = $floating['pool'];
				}
			}
			$this->interfaces[$iface['portID']] = $nic;
		}
	}
	public function getInterface($portID = NULL, $fixedIP = NULL, $floatingIP = NULL)
	{
		if (empty($this->interfaces)) {
			$this->loadInterfaces();
		}
		if ($portID) {
			return $this->interfaces[$portID];
		}
		if ($fixedIP) {
			foreach ($this->interfaces as $iface) {
				if ($fixedIP == $iface->fixedIP) {
					return $iface;
				}
			}
		}
		if ($floatingIP) {
			foreach ($this->interfaces as $iface) {
				if ($floatingIP == $iface->floatingIP) {
					return $iface;
				}
			}
		}
		return reset($this->interfaces);
	}
	public function listInterfaces($force = false)
	{
		if (empty($this->interfaces) || $force) {
			$this->loadInterfaces();
		}
		return $this->interfaces;
	}
	/**
	 * Return true if VPS is in provided state
	 * 
	 * @author Michal Czech <michael@modulesgarden.com>
	 * @param string $status [ACTIVE,SHUTOFF,SUSPENDED]
	 * @param boolean $force
	 * @return boolean
	 */
	public function haveStatus($status = 'ACTIVE', $force = false)
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if ($force) {
			$this->setDetails();
		}
		if ($this->status === $status) {
			return true;
		}
		return false;
	}
	public function create($setting = array())
	{
		if (!empty($this->UUID)) {
			throw new OSException('This is action cannot be executet on this VPS', 0);
		}
		if (empty($this->name)) {
			throw new OSException('Setup VPS name at first');
		}
		if (!is_a($this->flavor, 'OpenStackModelFlavor')) {
			throw new OSException('Setup Flavor at first', 0);
		}
		if (!is_a($this->image, 'OpenStackModelImage')) {
			throw new OSException('Setup Image at first', 0);
		}
		$networks = array();
		$floatingIP = array();
		$fixedNetwork = array();
		foreach ($this->interfaces as $network) {
			if ($network->fixedNetwork) {
				$notFound = true;
				foreach ($networks as $net) {
					if ($net['uuid'] == $network->fixedNetwork) {
						$notFound = false;
						break;
					}
				}
				if ($notFound) {
					$networks[] = array('uuid' => $network->fixedNetwork);
				} else {
					$fixedNetwork[] = $network->fixedNetwork;
				}
			}
			if ($network->floatingNetwork) {
				$floatingIP[] = $network->floatingNetwork;
			}
		}
		if (empty($networks)) {
			throw new OSException('Setup Fixed Network at first', 0);
		}
		$blockDevices = array();
		try {
			$letters = range('a', 'z');
			$bootIndex = 0;
			foreach ($this->blockDevices as $device) {
				$device->setDetails();
				if ($device->status == 'creating' || $device->status == 'downloading') {
					$wait = 5;
					$maxMinutes = 60;
					$maxNum = $maxMinutes * 60 / $wait;
					$num = 0;
					do {
						sleep($wait);
						++$num;
						$device->setDetails();
					} while (($device->status == 'creating' || $device->status == 'downloading') && $num < $maxNum);
				}
				if ($device->status !== 'available') {
					throw new OSException('You can\'t use this block storage device status:' . $device->status);
				}
				$blockDevice = array('source_type' => 'volume', 'destination_type' => 'volume', 'uuid' => $device->UUID, 'boot_index' => $bootIndex);
				if (!isset($setting['useDeviceName']) || $setting['useDeviceName'] == true) {
					$blockDevice['device_name'] = 'vd' . $letters[$bootIndex];
				}
				$blockDevices[] = $blockDevice;
			}
			$keyName = $this->key ? $this->key->name : NULL;
			$secriutyGroups = array();
			foreach ($this->securityGroup as $group) {
				$secriutyGroups[] = array('name' => $group->name);
			}
			$data = OpenStackAPI::getInstance()->compute()->create($this->name, $this->flavor->UUID, $this->image->UUID, $networks, $keyName, $secriutyGroups, $blockDevices, $this->password);
			$this->UUID = $data['id'];
			$this->password = $data['password'];
			$wait = 5;
			$maxMinutes = 60;
			$maxNum = $maxMinutes * 60 / $wait;
			$num = 0;
			do {
				sleep($wait);
				++$num;
				$this->setDetails();
			} while ($this->stateTask && $num < $maxNum);
			if ($maxNum <= $num) {
				throw new OSException('Timout during wait for VM Launch');
			}
			if (empty($this->status)) {
				throw new OSException('VM Status Empty');
			}
			if ($this->status == 'ERROR') {
				$data = OpenStackAPI::getInstance()->compute()->getVPSDetails($this->UUID);
				throw new OSException('Failed during VM spawning:' . $data['error']);
			}
			if ($fixedNetwork) {
				$this->listInterfaces(true);
				foreach ($fixedNetwork as $iface) {
					$network = new OpenStackModelNetwork($this->_tenantID);
					$network->UUID = $iface;
					$this->addFixedIP($network);
				}
			}
			if ($floatingIP) {
				$this->listInterfaces(true);
				foreach ($floatingIP as $iface) {
					$network = new OpenStackModelNetwork($this->_tenantID);
					$network->UUID = $iface;
					$this->addFloatingIP($network);
				}
			}
		} catch (OSException $problem) {
			if ($this->UUID) {
				try {
					$this->delete();
				} catch (OSException $e) {
				}
			}
			if ($this->blockDevices) {
				foreach ($this->blockDevices as $device) {
					try {
						$device->delete();
					} catch (OSAPIException $e) {
					}
				}
			}
			throw $problem;
		}
		return true;
	}
	public function delete()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		foreach ($this->listBackups(true) as $backup) {
			$backup->delete();
		}
		$k = 0;
		try {
			$floatingIPs = OpenStackAPI::getInstance()->compute()->getFloatingIPs();
			$toDelete = array();
			foreach ($floatingIPs as $ip) {
				if ($ip['instanceID'] == $this->UUID) {
					$toDelete[] = $ip;
				}
			}
			foreach ($toDelete as $ip) {
				OpenStackAPI::getInstance()->compute()->removeFloatingIPFromVPS($this->UUID, $ip['IP']);
			}
			foreach ($toDelete as $ip) {
				OpenStackAPI::getInstance()->compute()->deallocateFloatingIP($ip['UUID']);
			}
		} catch (OSAPIException $e) {
			if ($e->getCode() != 28) {
				throw $e;
			}
		}
		OpenStackAPI::getInstance()->compute()->delete($this->UUID);
		foreach ($this->blockDevices as $device) {
			$device->setDetails();
			if ($device->status == 'in-use') {
				$wait = 5;
				$maxMinutes = 60;
				$maxNum = $maxMinutes * 60 / $wait;
				$num = 0;
				do {
					sleep($wait);
					++$num;
					$device->setDetails();
				} while ($device->status == 'in-use' && $num < $maxNum);
			}
			$device->delete();
		}
		$this->UUID = NULL;
	}
	/**
	 * 
	 * @param OpenStackModelFlavor $flavor
	 * @return OpenStackModelFlavor
	 */
	public function flavor(OpenStackModelFlavor $flavor = NULL)
	{
		if ($flavor === NULL) {
			return $this->flavor;
		}
		$this->flavor = $flavor;
	}
	public function image(OpenStackModelImage $image = NULL)
	{
		if ($image === NULL) {
			return $this->image;
		}
		$this->image = $image;
	}
	private function addInterface(OpenStackModelNIC $interface)
	{
		$portID = NULL;
		if ($this->UUID) {
			$port = OpenStackAPI::getInstance()->network()->createPort($interface->fixedNetwork);
			$ips = OpenStackAPI::getInstance()->compute()->createInterface($this->UUID, $port['id']);
			$interface->fixedIP = $ips['address'];
			$interface->portID = $ips['id'];
			$portID = $ips['id'];
		}
		if ($portID) {
			$this->interfaces[$portID] = $interface;
		} else {
			$this->interfaces[] = $interface;
		}
		return $interface;
	}
	public function addFixedIP(OpenStackModelNetwork $internalNetwork)
	{
		return $this->addInterface(new OpenStackModelNIC(NULL, NULL, array('fixedNetwork' => $internalNetwork->UUID)));
	}
	public function addFloatingIP(OpenStackModelNetwork $externalNetwork, $useOnlyMyRouter = false)
	{
		if (empty($this->UUID)) {
			$iface = new OpenStackModelNIC($this->_tenantID);
			$iface->floatingNetwork = $externalNetwork->UUID;
			$this->addInterface($iface);
			return true;
		}
		if (empty($this->interfaces)) {
			$this->getInterface();
		}
		$routerToUse = false;
		foreach (OpenStackAPI::getInstance()->network()->listRouters() as $router) {
			if (($router['ownerID'] == $this->_tenantID || !$useOnlyMyRouter) && $router['externalNetwork'] == $externalNetwork->UUID) {
				$routerToUse = $router['id'];
			}
		}
		if ($routerToUse == false) {
			foreach (OpenStackTenant::adminLists('routers') as $router) {
				if (($router['ownerID'] == $this->_tenantID || !$useOnlyMyRouter) && $router['externalNetwork'] == $externalNetwork->UUID) {
					$routerToUse = $router['id'];
				}
			}
		}
		if ($routerToUse == false) {
			throw new OSException('Cannot find properly router to use', 0);
		}
		$subNetToUse = false;
		foreach (OpenStackAPI::getInstance()->network()->listPorts() as $port) {
			if ($routerToUse == $port['device_id']) {
				if ($port['network_id'] !== $externalNetwork->UUID) {
					$subNetToUse = $port['network_id'];
				}
			}
		}
		if ($subNetToUse == false) {
			foreach (OpenStackTenant::adminLists('ports') as $port) {
				if ($routerToUse == $port['device_id']) {
					if ($port['network_id'] !== $externalNetwork->UUID) {
						$subNetToUse = $port['network_id'];
					}
				}
			}
		}
		if ($subNetToUse == false) {
			throw new OSException('Cannot find properly subnet to use', 0);
		}
		$interface = NULL;
		foreach ($this->interfaces as $iface) {
			if ($iface->fixedNetwork == $subNetToUse && empty($iface->floatingIP)) {
				$interface = $iface;
				break;
			}
		}
		if ($interface === NULL) {
			$interface = $this->addInterface(new OpenStackModelNIC(NULL, 0, array('fixedNetwork' => $subNetToUse)));
		}
		$floatingIP = OpenStackAPI::getInstance()->network()->createFloatingIP($externalNetwork->UUID);
		sleep(1);
		OpenStackAPI::getInstance()->compute()->assignFloatingIPtoVPS($this->UUID, $floatingIP['address'], $this->interfaces[$interface->portID]->fixedIP);
		$this->interfaces[$interface->portID]->floatingIP = $floatingIP['address'];
		$this->interfaces[$interface->portID]->floatingNetwork = $externalNetwork->UUID;
		return $this->interfaces[$interface->portID];
	}
	public function deleteFloatingIP(OpenStackModelNIC $interface)
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if (empty($interface->floatingIP)) {
			throw new OSException('This is not floating IP', 0);
		}
		$floatingIPs = OpenStackAPI::getInstance()->compute()->getFloatingIPs();
		OpenStackAPI::getInstance()->compute()->removeFloatingIPFromVPS($this->UUID, $interface->floatingIP);
		foreach ($floatingIPs as $ip) {
			if ($ip['instanceID'] == $this->UUID && $ip['IP'] == $interface->floatingIP) {
				OpenStackAPI::getInstance()->compute()->deallocateFloatingIP($ip['UUID']);
			}
		}
	}
	public function deleteFixedIP(OpenStackModelNIC $interface)
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if ($interface->floatingIP) {
			$this->deleteFloatingIP($interface);
		}
		OpenStackAPI::getInstance()->compute()->deleteInterface($this->UUID, $interface->portID);
	}
	public function getIPs()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		$this->loadInterfaces();
		$output = array('fixed' => array(), 'floating' => array());
		foreach ($this->interfaces as $interface) {
			if ($interface->fixedIPVersion) {
				$output['fixed'] = $interface->fixedIP;
			} else {
				$output['floating'] = $interface->floatingIP;
			}
		}
		return $output;
	}
	public function changeFlavor()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if ($this->stateTask) {
			throw new OSException('Cannot Change flavor, VM is currently processing');
		}
		if (!is_a($this->flavor, 'OpenStackModelFlavor')) {
			throw new OSException('Setup Flavor at first', 0);
		}
		if ($this->blockDevices) {
			$currentBlock = NULL;
			foreach ($this->blockDevices as $currentBlock) {
				if ($currentBlock->attachDevice == 'vda') {
					break;
				}
			}
			$vpsDetails = OpenStackAPI::getInstance()->compute()->getVPSDetails($this->UUID);
			$currentFlavor = new OpenStackModelFlavor($this->_tenantID, $vpsDetails['flavorID']);
			if ($currentBlock->size < $this->flavor->disk) {
				if ($this->status != 'SHUTOFF') {
					$this->stop();
				}
				$wait = 5;
				$maxMinutes = 1;
				$maxNum = $maxMinutes * 60 / $wait;
				$num = 0;
				do {
					sleep($wait);
					++$num;
					$this->setDetails();
				} while ($this->stateTask && $num < $maxNum);
				if ($maxNum <= $num) {
					throw new OSException('Cannot stop instance device:' . $this->UUID);
				}
				OpenStackAPI::getInstance()->compute()->volumeDeattachment($this->UUID, $currentBlock->UUID);
				$this->setDetails();
				$wait = 5;
				$maxMinutes = 1;
				$maxNum = $maxMinutes * 60 / $wait;
				$num = 0;
				do {
					sleep($wait);
					++$num;
					$this->setDetails();
					$allow = true;
					foreach ($this->blockDevices as $block) {
						if ($block->attachDevice == $currentBlock->attachDevice) {
							$allow = false;
							break;
						}
					}
				} while (!$allow && $num < $maxNum);
				if ($maxNum <= $num) {
					throw new OSException('Cannot deattach device:' . '/dev/' . $currentBlock->attachDevice);
				}
				$currentBlock->extend($this->flavor->disk);
				sleep(2);
				OpenStackAPI::getInstance()->compute()->volumeAttachment($this->UUID, $currentBlock->UUID, $currentBlock->attachDevice);
				sleep(2);
				$this->start();
			}
		}
		OpenStackAPI::getInstance()->compute()->resize($this->UUID, $this->flavor->UUID);
		$wait = 5;
		$maxMinutes = 60;
		$maxNum = $maxMinutes * 60 / $wait;
		$num = 0;
		do {
			sleep($wait);
			++$num;
			$this->setDetails();
		} while ($this->stateTask && $num < $maxNum);
		if ($maxNum <= $num) {
			throw new OSException('Timout during wait for VM Resoze');
		}
		OpenStackAPI::getInstance()->compute()->confirmResize($this->UUID);
		return true;
	}
	public function confirmFlavorChange()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		OpenStackAPI::getInstance()->compute()->confirmResize($this->UUID);
		return true;
	}
	public function rebuild($adminPass)
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		if (!is_a($this->image, 'OpenStackModelImage')) {
			throw new OSException('Setup Image at first', 0);
		}
		OpenStackAPI::getInstance()->compute()->rebuild($this->UUID, $this->name, $adminPass, $this->image->UUID);
	}
	public function getConsole()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		return OpenStackAPI::getInstance()->compute()->getConsole($this->UUID);
	}
	public function getSpiceConsole()
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		return OpenStackAPI::getInstance()->compute()->getSpiceConsole($this->UUID);
	}
	public function changePassword($newPassword)
	{
		if (empty($this->UUID)) {
			throw new OSException('This object is not properly loaded', 0);
		}
		OpenStackAPI::getInstance()->compute()->changePassword($this->UUID, $newPassword);
	}
	private function loadBackups()
	{
		$list = OpenStackModelBackup::listSource($this->UUID);
		$this->backups = array();
		foreach ($list as $snap) {
			$snap['parentVM'] = $this;
			$this->backups[$snap['id']] = new OpenStackModelBackup($this->_tenantID, $snap['id'], $snap);
		}
	}
	public function listBackups($force = false)
	{
		if (empty($this->backups) || $force) {
			$this->loadBackups();
		}
		return $this->backups;
	}
	/**
	 * 
	 * @param string $id
	 * @return OpenStackModelBackup
	 * @throws OSException
	 */
	public function backup($id = 'NEW')
	{
		if (empty($this->backups)) {
			$this->loadBackups();
		}
		if ($id == 'NEW') {
			$this->backups['NEW'] = new OpenStackModelBackup($this->_tenantID, NULL, array('sourceVPS' => $this->UUID));
		}
		if (!isset($this->backups[$id])) {
			throw new OSException('Cant find provided snapshot', 0);
		}
		return $this->backups[$id];
	}
	public function addSecurityGroup(OpenStackModelSecurityGroup $group)
	{
		$this->securityGroup[] = $group;
	}
	public function setKeyPair(OpenStackModelKeyPair $key)
	{
		$this->key = $key;
	}
	public function addBlockDevice(OpenStackModelBlockDevice $device)
	{
		$this->blockDevices[$device->UUID] = $device;
		if (empty($this->UUID)) {
			return true;
		}
		$letters = array_fill_keys(range('d', 'z'), 1);
		foreach ($this->blockDevices as $deviceTemp) {
			if ($deviceTemp->attachDevice) {
				if (strpos($deviceTemp->attachDevice, '/dev/') === false) {
					$letter = substr($deviceTemp->attachDevice, 2);
				} else {
					$letter = substr($deviceTemp->attachDevice, 7);
				}
				unset($letters[$letter]);
			}
		}
		$device->setDetails();
		if ($device->status == 'creating') {
			$wait = 5;
			$maxMinutes = 60;
			$maxNum = $maxMinutes * 60 / $wait;
			$num = 0;
			do {
				sleep($wait);
				++$num;
				$device->setDetails();
			} while (($device->status == 'creating' || $device->status == 'downloading') && $num < $maxNum);
		}
		if ($device->status !== 'available') {
			throw new OSException('You can\'t use this block storage device status:' . $device->status);
		}
		$letter = key($letters);
		if (empty($letter)) {
			throw new OSException('Can\'t find new letter for volume');
		}
		OpenStackAPI::getInstance()->compute()->volumeAttachment($this->UUID, $device->UUID, '/dev/vd' . $letter);
		$this->blockDevices[$device->UUID]->attachServer = $this->UUID;
		$this->blockDevices[$device->UUID]->attachDevice = '/dev/vd' . $letter;
	}
	public function blockDevices($UUID)
	{
		if (isset($this->blockDevices[$UUID])) {
			return $this->blockDevices[$UUID];
		}
		throw new OSException('Can\'t find provided block device');
	}
	public function delBlockDevice(OpenStackModelBlockDevice $device)
	{
		unset($this->blockDevices[$device->UUID]);
		if (empty($this->UUID)) {
			return true;
		}
		OpenStackAPI::getInstance()->compute()->volumeDeattachment($this->UUID, $device->UUID);
		return true;
	}
}