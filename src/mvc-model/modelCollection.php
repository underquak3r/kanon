<?php
require_once dirname(__FILE__).'/modelResultSet.php';
require_once dirname(__FILE__).'/modelField.php';
class modelCollection implements ArrayAccess{
	private static $_instances = array();
	protected $_modelName = null; // helper
	//protected $_helper = null; // model instance
	protected $_uniqueId = null;
	protected $_filters = array();
	protected $_filtersEnabled = true;
	protected $_defaultValues = array();
	protected $_joinOn = array();
	public function exists(){
		$exists = false;
		$this->getStorage()->getDriver()->disableAutoRepair();
		// "select * from tablename where 1=2"
		if ($this->q('SELECT * FROM "'.$this->getTableName().'" WHERE 1=2')){
			$exists = true;
		}
		$this->getStorage()->getDriver()->enableAutoRepair();
		return $exists;
	}
	public function &setJoinOn($table2, $on = ''){
		if (strlen($on)){
			$this->_joinOn[$table2->getUniqueId()] = $on;
		}
		return $this;
	}
	public function getJoinOn($table2){
		return isset($this->_joinOn[$table2->getUniqueId()])?$this->_joinOn[$table2->getUniqueId()]:null;
	}
	public function setDefaultFieldValue(){
		// @todo
	}
	public function q($sql){
		return $this->getStorage()->query($sql);
	}
	public function addFilter($filter){
		$this->_filters[] = $filter;
		return $this;
	}
	public function resetFilters(){
		$this->_filters = array();
		return $this;
	}
	public function disableFilters(){
		$this->_filtersEnabled = false;
	}
	public function enableFilters(){
		$this->_filtersEnabled = true;
	}
	public function e($string){
		return $this->getStorage()->quote($string);
	}
	public function getFilters(){
		if ($this->_filtersEnabled){
			return $this->_filters;
		}
		return array();
	}
	public function getModelClass(){
		return $this->_modelName;
	}
	public function getCreateSql(){
		return $this->getHelper()->getCreateSql();
	}
	public function offsetExists($offset){
		return in_array($offset, $this->getFieldNames());
	}
	public function __toString(){
		return $this->getUniqueId();
	}
	public function getTableName(){
		$tableName = storageRegistry::getInstance()->modelSettings[$this->_modelName]['table'];
		return is_string($tableName)?$tableName:false;
	}
	public function offsetGet($offset){
		return new modelField($this, $offset);
	}
	public function offsetSet($offset, $value){

	}
	public function offsetUnset($offset){

	}
	public function __get($name){
		$fields = $this->getHelper()->getFieldNames();
		if (isset($fields[$name])){
			return new modelField($this, $fields[$name]);
		}
		return null;
	}
	public function __set($name, $value){

	}
	public function select(){
		$args = func_get_args();
		$fields = false;
		foreach ($args as $arg){
			if ($arg instanceof modelField){
				if ($arg->getCollection()->getTableName() == $this->getTableName()){
					$fields = true;
				}
			}
		}
		if (!$fields) array_unshift($args, $this);
		$result = new modelResultSet();
		call_user_func_array(array($result, 'select'), $args);
		return $result;
	}
	public function getFieldNames(){
		return $this->getHelper()->getFieldNames();
	}
	public function getForeignKeys(){
		return $this->getHelper()->getForeignKeys();
	}
	public function getStorage(){
		return $this->getHelper()->getStorage();
	}
	public function getConnection(){
		return $this->getStorage()->getConnection();
	}
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	private function __construct($modelName){
		$this->_modelName = $modelName;
	}
	/**
	 * @return model
	 */
	public function getHelper(){
		return new $this->_modelName;
		if ($this->_helper === null){
			$this->_helper = new $this->_modelName;
		}
		return $this->_helper;
	}
	public function getPrimaryKey(){
		return $this->getHelper()->getPrimaryKey();
	}
	public function findOne(){
		$args = func_get_args();
		$list = call_user_func_array(array($this, 'find'), $args);
		return $list->fetch();
	}
	public function find(){
		$args = func_get_args();
		$pk = $this->getPrimaryKey();
		$pkValues = array();
		$expressions = array();
		foreach ($args as $arg){
			if ($arg instanceof modelExpression){
				$expressions[] = $arg;
			}else{
				$pkValues[] = $arg;
			}
		}
		$list = $this->select();
		foreach ($pk as $fieldName){
			$list->where($this->{$fieldName}->is(array_shift($pkValues)));
		}
		foreach ($expressions as $expression){
			$list->where($expression);
		}
		return $list;
	}
	public static function &getInstance($modelName){
		if (!isset(self::$_instances[$modelName])){
			self::$_instances[$modelName] = new self($modelName);
		}
		return self::$_instances[$modelName];
	}
}