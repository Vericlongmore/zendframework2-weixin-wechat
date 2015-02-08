<?php

namespace Wechat\Db;

use Wechat\Api\Base\BaseService;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;

class DbService extends BaseService {
	protected $options = array (
			'entity_namespace' => 'Entity',
			'table_namespace' => 'Table',
			'view_namespace' => 'View' 
	);
	public static function dbNameToClass($dbTable) {
		$dbTable = str_replace ( '_', ' ', $dbTable );
		$dbTable = ucwords ( $dbTable );
		return str_replace ( ' ', '', $dbTable );
	}
	public function getRow($primaryKeyColumn, $table, $adapterOrSql = null) {
		if (! $adapterOrSql)
			$adapterOrSql = $this->getAdapter ();
		return new DbRowGateway ( $primaryKeyColumn, $table, $adapterOrSql );
	}
	public function getTable($dbTable, $features = null, $sql = null) {
		$tableClass = $this->getTableClass ( $dbTable );
		$protoType = $this->getProtoType ( $dbTable );
		if (! class_exists ( $protoType )) {
			$protoType = 'Wechat\Db\DbArrayObject';
		}
		$resultSetProtoType = new HydratingResultSet ( null, new $protoType () );
		if (! class_exists ( $tableClass )) {
			$tableClass = 'Wechat\Db\DbTableGateway';
		}
		$table = new $tableClass ( $dbTable, $this->getAdapter (), $features, $resultSetProtoType, $sql );
		$table->setDbService ( $this );
		return $table;
	}
	public function getPrimaryKey($dbTable) {
		$sql = sprintf ( "SHOW COLUMNS FROM %s WHERE `Key`='PRI'", $dbTable );
		$resultSet = $this->queryExecute ( $sql );
		if (! $resultSet)
			throw new \Exception ( __METHOD__ );
		$row = $resultSet->current ();
		return $row ['Field'];
	}
	protected function getProtoType($dbTable) {
		$array [] = __NAMESPACE__;
		$array [] = $this->getOptions ( 'entity_namespace' );
		$array [] = static::dbNameToClass ( $dbTable );
		return implode ( '\\', $array );
	}
	protected function getTableClass($dbTable) {
		$array [] = __NAMESPACE__;
		$array [] = $this->getOptions ( 'table_namespace' );
		$array [] = static::dbNameToClass ( $dbTable ) . 'Table';
		return implode ( '\\', $array );
	}
	protected function getViewClass($dbTable) {
		$array [] = __NAMESPACE__;
		$array [] = $this->getOptions ( 'view_namespace' );
		$array [] = static::dbNameToClass ( $dbTable ) . 'View';
		return implode ( '\\', $array );
	}
	public function queryExecute($sql) {
		return $this->getAdapter ()->query ( $sql, Adapter::QUERY_MODE_EXECUTE );
	}
	public function queryPrepare($sql, $params) {
		return $this->getAdapter ()->query ( $sql, $params );
	}
}