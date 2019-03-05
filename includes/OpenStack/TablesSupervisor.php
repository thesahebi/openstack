<?php

//dezend by http://www.yunlu99.com/ QQ:270656184
class TablesSupervisor
{
	private $model = array();
	private $curTable = false;
	public $sqlLogs = array();
	private $debugOnly = false;
	private $verboseMode = false;
	public function setModel(array $model)
	{
		$this->model = $model;
	}
	public function addTable($name, $engine = 'MyISAM')
	{
		$this->model[$name] = array('name' => $name, 'columns' => array(), 'keys' => array(), 'engine' => $engine);
		$this->curTable = $name;
	}
	public function addColumn($name, $type, $default = NULL, $size = 0)
	{
		$this->model[$this->curTable]['columns'][$name] = array('name' => $name, 'type' => $type, 'size' => $size, 'default' => $default === NULL ? 'NULL' : $default);
	}
	public function addKey($column, $type = 'primary')
	{
		$this->model[$this->curTable]['keys'][$column] = array('name' => $column, 'type' => $type);
	}
	public function getCurrentTables()
	{
		$q = \MGModule\OpenStack\PdoWrapper::query('SHOW Tables');
		$tables = array();
		while ($row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($q)) {
			$tables[] = $row['name'];
		}
		return $tables;
	}
	public function parseColumn($column)
	{
		switch (strtolower($column['type'])) {
			case 'varchar':
				$rows = '`' . $column['name'] . '` ' . sprintf('VARCHAR(%d)', $column['size']) . ' DEFAULT ' . $column['default'];
				break;
			case 'int':
				$rows = '`' . $column['name'] . '` INT(11) DEFAULT ' . $column['default'];
				break;
			case 'text':
				$rows = '`' . $column['name'] . '` TEXT DEFAULT ' . $column['default'];
				break;
			case 'bool':
				$rows = '`' . $column['name'] . '` TINYINT(1) DEFAULT ' . $column['default'];
				break;
			case 'date':
				$rows = '`' . $column['name'] . '` DATETIME DEFAULT ' . $column['default'];
				break;
			case 'a/i':
				$rows = '`' . $column['name'] . '` int(11) NOT NULL AUTO_INCREMENT';
				break;
			default:
				$rows = '`' . $column['name'] . '` ' . $column['type'];
		}
		return $rows;
	}
	public function createTable($name)
	{
		if (empty($this->model[$name]['columns'])) {
			$this->sqlLogs[] = 'Cannot find column model for table: ' . $name;
			return false;
		}
		$sql = 'CREATE TABLE IF NOT EXISTS `' . $name . "` ( \n";
		$rows = array();
		foreach ($this->model[$name]['columns'] as $column) {
			$rows[] = $this->parseColumn($column);
		}
		foreach ($this->model[$name]['keys'] as $key) {
			switch (strtolower($key['type'])) {
				case 'primary':
					$rows[] = 'PRIMARY KEY (`' . $key['name'] . '`)';
					break;
				case 'key':
					$rows[] = ' KEY (`' . $key['name'] . '`)';
					break;
				default:
					$rows[] = $key['type'] . ' (`' . $key['name'] . '`)';
			}
		}
		$sql .= implode(",\n", $rows);
		$sql .= "\n ) ENGINE=" . $this->model[$name]['engine'] . ' DEFAULT CHARSET=utf8 ;';
		if ($this->debugOnly) {
			$this->sqlLogs[] = $sql;
		} else {
			if ($this->verboseMode) {
			}
			\MGModule\OpenStack\PdoWrapper::query($sql);
			if ($this->verboseMode) {
			}
		}
	}
	public function upgradeTable($name)
	{
		$query = 'SHOW COLUMNS IN `' . $name . '`';
		$q = \MGModule\OpenStack\PdoWrapper::query($query);
		if (empty($q)) {
			return false;
		}
		$records = array();
		while ($row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($q)) {
			$records[] = $row;
		}
		if ($records) {
			$old_fields = array();
			foreach ($records as &$record) {
				$old_fields[] = $record['Field'];
			}
			$new_fields = array_keys($this->model[$name]['columns']);
			$create = array_diff($new_fields, $old_fields);
			$delete = array_diff($old_fields, $new_fields);
			foreach ($create as &$field) {
				$sql = 'ALTER TABLE `' . $name . '` ADD COLUMN ' . $this->parseColumn($this->model[$name]['columns'][$field]);
				if ($this->debugOnly) {
					$this->sqlLogs[] = $sql;
				} else {
					if ($this->verboseMode) {
					}
					\MGModule\OpenStack\PdoWrapper::query($sql);
					if ($this->verboseMode) {
					}
				}
			}
		} else {
			$this->createTable($name);
		}
	}
	public function int($name, $default = NULL)
	{
		$this->addColumn($name, 'int', $default);
	}
	public function ai($name, $default = NULL)
	{
		$this->addColumn($name, 'a/i', $default);
		$this->addKey($name);
	}
	public function text($name, $default = NULL)
	{
		$this->addColumn($name, 'text', $default);
	}
	public function varchar($name, $size)
	{
		$this->addColumn($name, 'varchar', NULL, $size);
	}
	public function date($name)
	{
		$this->addColumn($name, 'date');
	}
	public function bool($name, $default)
	{
		$default = $default ? 'true' : 'false';
		$this->addColumn($name, 'bool', $default);
	}
	public function installModel()
	{
		foreach ($this->model as $table) {
			$this->createTable($table['name']);
		}
	}
	public function updateModel()
	{
		foreach ($this->model as $table) {
			$this->upgradeTable($table['name']);
		}
	}
	public function __destruct()
	{
		if ($this->verboseMode) {
		} else {
			if ($this->sqlLogs) {
				logModuleCall('Addon Module', 'SQL Update', $this->sqlLogs, 'Errors');
			}
		}
	}
}