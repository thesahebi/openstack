<?php

//Decoded by SoarTeam SoarTeam
if (!class_exists('ModulesGardenTaskManagerException')) {
	class ModulesGardenTaskManagerException extends Exception
	{
		public function __construct($message, $code = NULL)
		{
			parent::__construct($message, $code);
		}
	}
}
if (!class_exists('ModulesGardenSQLException')) {
	class ModulesGardenSQLException extends Exception
	{
		public $query;
		public $params;
		public function __construct($message, $query = NULL, $params = array())
		{
			parent::__construct($message);
			$this->query = $query;
			$this->params = $params;
		}
	}
}
if (!class_exists('ModulesGardenTask')) {
	class ModulesGardenTask
	{
		public $UUID;
		public $hostingID;
		public $VMUUID;
		public $itemID;
		public $createDate;
		public $action;
		public $configs;
		public $attempt;
		public $lastAttemptDate;
		public $message;
		public $locked;
		public $finished;
		public $module;
		public function __construct(array $params = array())
		{
			$this->load($params);
			if (empty($this->createDate)) {
				$this->createDate = date('Y:m:d H:i:s');
			}
		}
		public function load(array $params)
		{
			foreach ($params as $propertyName => $propertyValue) {
				if (property_exists($this, $propertyName)) {
					$this->{$propertyName} = $propertyValue;
				}
			}
			$this->configs = unserialize($this->configs);
		}
		public function create()
		{
			if (empty($this->module)) {
				throw new ModulesGardenTaskManagerException('Setup module at first');
			}
			$columns = array();
			$values = array();
			$questioner = array();
			$this->locked = 0;
			$this->finished = 0;
			$this->attempt = 0;
			$toSave = clone $this;
			$toSave->configs = serialize($toSave->configs);
			$toSave->UUID = md5($this->hostingID . $this->VMUUID . $this->action . $this->itemID . time());
			foreach ($toSave as $propertyName => $propertyValue) {
				if (property_exists($this, $propertyName) && !in_array($propertyName, array('module'))) {
					$columns[] = $propertyName;
					$values[] = $propertyValue;
					$questioner[] = '?';
				}
			}
			$query = 'INSERT ' . $this->module . 'Tasks (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $questioner) . ')';
			\MGModule\OpenStack\PdoWrapper::query($query, $values);
		}
		public function update()
		{
			if (empty($this->module)) {
				throw new ModulesGardenTaskManagerException('Setup module at first');
			}
			$columns = array();
			$values = array();
			$toSave = clone $this;
			$toSave->configs = serialize($toSave->configs);
			foreach ($toSave as $propertyName => $propertyValue) {
				if (property_exists($this, $propertyName) && !in_array($propertyName, array('UUID', 'module'))) {
					$columns[] = '`' . $propertyName . '` = ? ';
					$values[] = $propertyValue;
				}
			}
			$values[] = $this->UUID;
			$query = 'UPDATE ' . $this->module . 'Tasks SET ' . implode(',', $columns) . ' WHERE `UUID` = ?';
			\MGModule\OpenStack\PdoWrapper::query($query, $values);
		}
		public function delete()
		{
			if (empty($this->UUID)) {
				return false;
			}
			$query = 'DELETE FROM ' . $this->module . 'Tasks WHERE `UUID` = ?';
			$values = array($this->UUID);
			\MGModule\OpenStack\PdoWrapper::query($query, $values);
		}
		public function setFinished()
		{
			$this->finished = true;
			$this->update();
		}
		public function lock()
		{
			$this->locked = true;
			$this->lastAttemptDate = date('Y:m:d H:i:s');
			$this->update();
		}
		public function unlock()
		{
			$this->locked = false;
			$this->update();
		}
	}
}
if (!class_exists('ModulesGardenTaskManager')) {
	abstract class ModulesGardenTaskManager
	{
		private $module;
		private $currentTask = false;
		private $processedTasks = array();
		private $maxMinutes = 30;
		private $hostingID;
		private $VMUUID;
		public $maxAttempt = 20;
		public function __construct()
		{
			$this->module = $this->moduleName();
		}
		protected abstract function moduleName();
		public function updateDB()
		{
			require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TablesSupervisor.php';
			$super = new TablesSupervisor();
			$super->addTable($this->module . 'Tasks');
			$super->varchar('UUID', 32);
			$super->int('hostingID');
			$super->varchar('VMUUID', 50);
			$super->varchar('itemID', 50);
			$super->varchar('action', 50);
			$super->date('createDate');
			$super->addColumn('configs', 'blob');
			$super->int('attempt', 0);
			$super->date('lastAttemptDate');
			$super->text('message');
			$super->bool('locked', false);
			$super->bool('finished', false);
			$super->addKey('UUID');
			$super->installModel();
			$super->updateModel();
		}
		private function loadWHMCSParams(ModulesGardenTask $task)
		{
			if (function_exists('ModuleBuildParams')) {
				return ModuleBuildParams($task->hostingID);
			}
			$return = array('serviceid' => $task->hostingID);
			$query = "SELECT \r\n                        `server`      AS serverid\r\n                        ,`packageid`  AS packageid\r\n                  FROM \r\n                        tblhosting \r\n                  WHERE \r\n                        id = ?\r\n                 ";
			$values = array($task->hostingID);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $values);
			$hosting = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			if (empty($hosting)) {
				throw new ModulesGardenTaskManagerException('Cant find hosting:#' . $task->hostingID . '#');
			}
			$return = array_merge($return, $hosting);
			$query = "\r\n                    SELECT \r\n                        `ipaddress`   AS serverip\r\n                        ,`hostname`   AS serverhostname\r\n                        ,`username`   AS serverusername\r\n                        ,`password`   AS serverpassword\r\n                        ,`accesshash` AS serveraccesshash\r\n                    FROM \r\n                        tblservers\r\n                    WHERE \r\n                        id = ?\r\n                 ";
			$values = array($hosting['serverid']);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $values);
			$server = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			$return = array_merge($return, $server);
			$query = "\r\n                    SELECT\r\n                        `fieldid`\r\n                        ,`value`\r\n                    FROM\r\n                        `tblcustomfieldsvalues`\r\n                    WHERE\r\n                        `relid` = ?\r\n        ";
			$values = array($task->hostingID);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $values);
			$customFieldsValues = array();
			while ($row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result)) {
				$customFieldsValues[$row['fieldid']] = $row['value'];
			}
			$query = "\r\n                    SELECT\r\n                        `id`\r\n                        ,`fieldname`\r\n                    FROM\r\n                        `tblcustomfields`\r\n                    WHERE\r\n                        `type` = 'product'\r\n                        AND `relid` = ?\r\n        ";
			$values = array($hosting['packageid']);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $values);
			$customFields = array();
			while ($row = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result)) {
				$name = substr($row['fieldname'], 0, strpos($row['fieldname'], '|'));
				$customFields[$name] = isset($customFieldsValues[$row['id']]) ? $customFieldsValues[$row['id']] : 0;
			}
			$return['customFields'] = $customFields;
			return $return;
		}
		private function takeTaskFromStack()
		{
			$query = "\r\n                  SELECT \r\n                        * \r\n                  FROM \r\n                        " . $this->module . "Tasks \r\n                  WHERE \r\n                        `finished` = 0 \r\n                        AND `locked` = 0  \r\n                        AND UUID not in ('" . implode('\',\'', $this->processedTasks) . "')\r\n                        AND attempt <= ?\r\n                  ORDER BY \r\n                        `attempt`,`createDate` \r\n                   ASC LIMIT 1";
			$result = \MGModule\OpenStack\PdoWrapper::query($query, array($this->maxAttempt));
			$task = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			if ($task) {
				$task['module'] = $this->module;
				$this->currentTask = new ModulesGardenTask($task);
				$this->currentTask->locked = true;
				$this->currentTask->attempt++;
				$this->currentTask->lastAttemptDate = date('Y:m:d H:i:s');
				$this->currentTask->update();
				return true;
			}
			return false;
		}
		protected function prepareSettings(ModulesGardenTask $task, array $params)
		{
		}
		private function processCurrentTask()
		{
			$this->processedTasks[] = $this->currentTask->UUID;
			$methodName = $this->currentTask->action . 'Process';
			if (!method_exists($this, $methodName)) {
				throw new ModulesGardenTaskManagerException('Cant find method "' . $methodName . '" to process task');
			}
			$whmcsparams = (array) $this->loadWHMCSParams($this->currentTask);
			$this->prepareSettings($this->currentTask, $whmcsparams);
			$response = $this->{$methodName}($this->currentTask, $whmcsparams);
			$resultState = $response === true;
			if ($response === 'success') {
				$resultState = true;
			}
			if (is_array($response)) {
				if (!empty($response['result'])) {
					$resultState = true;
				}
			}
			if ($resultState) {
				$this->currentTask->finished = true;
				if (is_array($response)) {
					$this->currentTask->message = $response['message'];
				}
			} else {
				$this->currentTask->locked = false;
				if (is_array($response)) {
					$this->currentTask->message = $response['message'];
				} else {
					$this->currentTask->message = $response;
				}
			}
			$this->currentTask->update();
		}
		public function executeTasks()
		{
			$start = time();
			$diff = 0;
			$maxSeconds = $this->maxMinutes * 60;
			while ($this->takeTaskFromStack()) {
				$this->processCurrentTask();
				$diff = time() - $start;
			}
		}
		public function setTaskContext($hostingID, $VMUUID = NULL)
		{
			$this->hostingID = $hostingID;
			$this->VMUUID = $VMUUID;
		}
		public function addTask($name, array $config = array(), $itemID = NULL)
		{
			if (empty($this->hostingID)) {
				throw new Exception('Setup Context Previous');
			}
			if ($this->existActiveTask($name, $itemID)) {
				throw new Exception('Action "' . $name . '" currently exists on task list');
			}
			$task = new ModulesGardenTask();
			$task->module = $this->module;
			$task->action = $name;
			$task->configs = $config;
			$task->hostingID = $this->hostingID;
			$task->VMUUID = $this->VMUUID;
			$task->itemID = $itemID;
			$task->create();
		}
		public function deleteTaskByUUID($UUID)
		{
			$query = "\r\n                SELECT \r\n                      * \r\n                FROM \r\n                    " . $this->module . "Tasks \r\n                WHERE \r\n                    `UUID`  = ?\r\n                ORDER BY \r\n                    `createDate`,`attempt` ASC \r\n                LIMIT 1";
			$params = array($UUID);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $params);
			$data = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			$data['module'] = $this->module;
			$task = new ModulesGardenTask($data);
			$task->delete();
		}
		public function unlockTaskByUUID($UUID)
		{
			$query = "\r\n                SELECT \r\n                      * \r\n                FROM \r\n                    " . $this->module . "Tasks \r\n                WHERE \r\n                    `UUID`  = ?\r\n                ORDER BY \r\n                    `createDate`,`attempt` ASC \r\n                LIMIT 1";
			$params = array($UUID);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $params);
			$data = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			$data['module'] = $this->module;
			$task = new ModulesGardenTask($data);
			$task->attempt = 0;
			$task->unlock();
		}
		public function getLastTask($name, $itemID = 0)
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			$query = "\r\n                SELECT \r\n                      * \r\n                FROM \r\n                    " . $this->module . "Tasks \r\n                WHERE \r\n                    `hostingID` = ?\r\n                    AND `VMUUID`   = ?\r\n                    AND `itemID`   = ?\r\n                    AND `action`   = ?\r\n                ORDER BY \r\n                    attempt,createDate ASC \r\n                LIMIT 1";
			$params = array($this->hostingID, $this->VMUUID, $itemID, $name);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $params);
			$data = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result);
			if ($data) {
				$data['module'] = $this->module;
				return new ModulesGardenTask($data);
			}
		}
		public function checkActionFinished($action, $itemID = false)
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			if ($task = $this->getLastTask($action, $itemID)) {
				return $task->finished;
			}
			return false;
		}
		public function checkActionNotExists($action, $itemID = false)
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			if ($task = $this->getLastTask($action, $itemID)) {
				if ($task->finished) {
					return true;
				}
				return false;
			}
			return true;
		}
		public function existActiveTask($action, $itemID = NULL)
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			if ($task = $this->getLastTask($action, $itemID)) {
				if ($task->finished) {
					return false;
				}
				return true;
			}
			return false;
		}
		public function listActiveTasks()
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			if ($this->VMUUID) {
				$query = "\r\n                    SELECT \r\n                          * \r\n                    FROM \r\n                        " . $this->module . "Tasks \r\n                    WHERE \r\n                        `hostingID` = ?\r\n                        AND `VMUUID`   = ?\r\n                        AND `finished` = 0\r\n                    ORDER BY \r\n                        createDate,attempt ASC ";
				$params = array($this->hostingID, $this->VMUUID);
			} else {
				$query = "\r\n                    SELECT \r\n                          * \r\n                    FROM \r\n                        " . $this->module . "Tasks \r\n                    WHERE \r\n                        `hostingID` = ?\r\n                        AND `finished` = 0\r\n                    ORDER BY \r\n                        createDate,attempt ASC ";
				$params = array($this->hostingID);
			}
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $params);
			$tasks = array();
			while ($data = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result)) {
				$data['module'] = $this->module;
				$tasks[] = new ModulesGardenTask($data);
			}
			return $tasks;
		}
		public function deleteActiveTasks()
		{
			if (empty($this->hostingID)) {
				throw new ModulesGardenTaskManagerException('Setup Context Previous');
			}
			$query = "\r\n                SELECT \r\n                      * \r\n                FROM \r\n                    " . $this->module . "Tasks \r\n                WHERE \r\n                    `hostingID` = ?\r\n                    AND `VMUUID`   = ?\r\n                ORDER BY \r\n                    createDate,attempt ASC ";
			$params = array($this->hostingID, $this->VMUUID);
			$result = \MGModule\OpenStack\PdoWrapper::query($query, $params);
			$tasks = array();
			while ($data = \MGModule\OpenStack\PdoWrapper::fetchAssoc($result)) {
				$data['module'] = $this->module;
				$tasks = new ModulesGardenTask($data);
				$tasks->delete();
			}
		}
	}
}