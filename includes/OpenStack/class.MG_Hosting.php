<?php

//Decoded by SoarTeam SoarTeam
class MG_Hosting
{
	public $id = 0;
	public $product_details = array();
	public $custom_fields = array();
	public $configurable_options = array();
	public $server_details = array();
	public function __construct($id)
	{
		$this->id = $id;
		$this->load();
	}
	public function updateDetails(array $values)
	{
		$sets = array();
		foreach ($values as $k => $v) {
			$v = is_numeric($v) ? $v : '"' . \MGModule\OpenStack\PdoWrapper::realEscapeString($v) . '"';
			$sets[] = $k . '=' . $v;
		}
		return \MGModule\OpenStack\PdoWrapper::query('UPDATE tblhosting SET ' . implode(',', $sets) . ' WHERE id = ' . (int) $this->id);
	}
	public function load()
	{
		$q = \MGModule\OpenStack\PdoWrapper::fetchAssoc(\MGModule\OpenStack\PdoWrapper::query('SELECT * FROM tblhosting WHERE id = ?', array($this->id)));
		foreach ($q as $key => &$val) {
			$this->hosting_details[$key] = $val;
		}
		$this->hosting_details['password'] = decrypt($this->hosting_details['password']);
		$q = \MGModule\OpenStack\PdoWrapper::query("SELECT cf.id, cf.fieldname, cfv.value FROM tblcustomfields AS cf JOIN tblcustomfieldsvalues AS cfv ON cfv.fieldid = cf.id WHERE cf.type = \"product\" AND cfv.relid = " . (int) $this->id);
		$q = \MGModule\OpenStack\PdoWrapper::fetchArray($q);
		foreach ($q as $key => &$val) {
			if (strpos($val['fieldname'], '|')) {
				$this->custom_fields[substr($val['fieldname'], 0, strpos($val['fieldname'], '|'))] = $val['value'];
			} else {
				$this->custom_fields[$val['fieldname']] = $val['value'];
			}
		}
		if ($this->hosting_details['server']) {
			$q = \MGModule\OpenStack\PdoWrapper::fetchAssoc(\MGModule\OpenStack\PdoWrapper::query('SELECT * FROM tblservers WHERE id = ?', array($this->hosting_details['server'])));
			foreach ($q as $key => &$val) {
				$this->server_details[$key] = $val;
			}
		}
		$config_options = \MGModule\OpenStack\PdoWrapper::query(" SELECT co.optionname AS config_option_name, cos.optionname AS option_name, coh.qty, co.optiontype FROM tblhostingconfigoptions AS coh JOIN tblproductconfigoptions AS co ON coh.configid = co.id JOIN tblproductconfigoptionssub AS cos ON cos.id = coh.optionid JOIN tblproductconfiggroups AS cog ON cog.id = co.gid JOIN tblproductconfiglinks AS col ON cog.id = col.gid  WHERE coh.relid = ? AND   col.pid = ? ", array($this->id, $this->hosting_details['packageid']));
		$config_options = \MGModule\OpenStack\PdoWrapper::fetchArray($config_options);
		foreach ($config_options as $key => $val) {
			$this->configurable_options[self::getFirstAndLastName($val['config_option_name'])] = in_array($val['optiontype'], array(3, 4)) ? $val['qty'] : self::getFirstAndLastName($val['option_name']);
		}
	}
	/**
	 * Get Hosting Details
	 * @param type $key
	 * @return boolean
	 */
	public function getDetails($key = NULL)
	{
		if (isset($this->hosting_details[$key])) {
			return $this->hosting_details[$key];
		}
		return false;
	}
	/**
	 * Get Custom Field
	 * @param type $key
	 * @return boolean
	 */
	public function getCustomField($key)
	{
		if (isset($this->custom_fields[$key])) {
			return $this->custom_fields[$key];
		}
		return false;
	}
	public function setCustomField($fieldname, $value)
	{
		$customField = \MGModule\OpenStack\PdoWrapper::fetchAssoc(\MGModule\OpenStack\PdoWrapper::query("SELECT f.id FROM tblcustomfields AS f JOIN tblproducts AS p ON f.type = \"product\" AND f.relid = p.id WHERE p.id = ? AND (f.fieldname = ? OR f.fieldname LIKE ?)", array($this->hosting_details['packageid'], $fieldname, $fieldname . '|%')));
		if (empty($customField)) {
			return false;
		}
		\MGModule\OpenStack\PdoWrapper::query('DELETE FROM tblcustomfieldsvalues WHERE fieldid = ? AND relid = ?', array($customField['id'], $this->id));
		return \MGModule\OpenStack\PdoWrapper::query('INSERT INTO tblcustomfieldsvalues(fieldid,relid,value) VALUES(?,?,?)', array($customField['id'], $this->id, $value));
	}
	public function getCustomFields($fieldname = NULL)
	{
		$fields = array();
		$q = \MGModule\OpenStack\PdoWrapper::query("SELECT cf.id, cf.fieldname, cfv.value FROM tblcustomfields AS cf JOIN tblcustomfieldsvalues AS cfv ON cfv.fieldid = cf.id WHERE cf.type = \"product\" AND cfv.relid = " . (int) $this->id . "");
		while ($row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($q)) {
			$fields[] = $row;
		}
		return $fields;
	}
	public static function getFirstAndLastName($str, $first = true)
	{
		$pos = strpos($str, '|');
		if ($pos) {
			return $first ? substr($str, 0, $pos) : substr($str, $pos);
		}
		return $str;
	}
}