<?php

//Decoded by SoarTeam SoarTeam
class OpenStackVPS_KeyPair
{
	public $id;
	public $hid;
	public $key;
	public $public_key;
	public $name;
	private $secretKey;
	public function __construct($hostingID)
	{
		global $cc_encryption_hash;
		$this->secretKey = '1rfv*($ccs' . $cc_encryption_hash . 'd\\*(HD=-g45Sa';
		$q = \MGModule\OpenStack\PdoWrapper::query('SELECT *, AES_DECRYPT(`key`,?) AS `key`, AES_DECRYPT(`public_key`,?) AS `public_key` FROM `openstackvps_keypairs` WHERE `hid`=?', array($this->secretKey, $this->secretKey, $hostingID));
		$r = \MGModule\OpenStack\PdoWrapper::fetchAssoc($q);
		foreach ($r as $k => $v) {
			$this->{$k} = $v;
		}
	}
	public function add($hostingID, $key)
	{
		$this->setupDB();
		$result = \MGModule\OpenStack\PdoWrapper::query('INSERT INTO `openstackvps_keypairs` (`hid`,`key`,`public_key`,`date`,`name`) VALUES (?, AES_ENCRYPT(?, ?), AES_ENCRYPT(?, ?), NOW(), ?)  ON DUPLICATE KEY UPDATE `key`= AES_ENCRYPT(?, ?),`public_key` = AES_ENCRYPT(?, ?), name = ?', array($hostingID, $key->private, $this->secretKey, $key->public, $this->secretKey, $key->name, $key->private, $this->secretKey, $key->public, $this->secretKey, $key->name));
	}
	public function isPrivateKey()
	{
		return !empty($this->key);
	}
	public function isPublicKey()
	{
		return !empty($this->public_key);
	}
	public function delete()
	{
		return \MGModule\OpenStack\PdoWrapper::query('DELETE FROM `openstackvps_keypairs` WHERE `hid`=? LIMIT 1', array($this->hid));
	}
	public function deletePrivate()
	{
		return \MGModule\OpenStack\PdoWrapper::query('UPDATE `openstackvps_keypairs` SET `key`= AES_ENCRYPT(?, ?)  WHERE `hid`=? LIMIT 1', array('', $this->secretKey, $this->hid));
	}
	public function setupDB()
	{
		\MGModule\OpenStack\PdoWrapper::query("CREATE TABLE IF NOT EXISTS `openstackvps_keypairs` (id` int(10) NOT NULL AUTO_INCREMENT,`hid` int(10),`key` BLOB,`public_key` BLOB, `date` date,`name` varchar(100), PRIMARY KEY (`id`), UNIQUE (`hid`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}
}
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