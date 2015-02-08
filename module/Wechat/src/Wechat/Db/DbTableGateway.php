<?php

namespace Wechat\Db;

use Zend\Db\TableGateway\TableGateway;

class DbTableGateway extends TableGateway {
	protected $dbService;
	public function setDbService($dbService) {
		$this->dbService = $dbService;
	}
	public function getDbService() {
		return $this->dbService;
	}
	protected function getRowGateway($primaryKeyColumn) {
		return $this->getDbService ()->getRow ( $primaryKeyColumn, $this->dbTable );
	}
	public function selectOne($whereArray, $one = true) {
		$resultSet = $this->select ( $whereArray );
		if ($resultSet->count () == 1) {
			return $resultSet->current ();
		} elseif ($resultSet->count () > 1) {
			throw new \Exception ( __METHOD__ . ':record repeat!' . $whereArray ['openId'] );
		}
	}
	public function insertRow(DbArrayObject $entity, $primaryKeyColumn = null) {
		return $this->saveRow ( $entity, $primaryKeyColumn, false );
	}
	public function updateRow(DbArrayObject $entity, $primaryKeyColumn = null) {
		return $this->saveRow ( $entity, $primaryKeyColumn, true );
	}
	public function saveRow(DbArrayObject $entity, $primaryKeyColumn = null, $rowExistsInDatabase = false) {
		if (! $primaryKeyColumn) {
			$primaryKeyColumn = $entity->getPrimaryKey ();
		}
		if (! $primaryKeyColumn) {
			$primaryKeyColumn = $this->getDbService ()->getPrimaryKey ( $this->dbTable );
		}
		$rowGateway = $this->getRowGateway ( $primaryKeyColumn );
		$rowGateway->populate ( $entity->getArrayCopy (), $rowExistsInDatabase );
		$rowGateway->save ();
		$class = get_class ( $entity );
		return new $class ( $rowGateway->toArray () );
	}
}