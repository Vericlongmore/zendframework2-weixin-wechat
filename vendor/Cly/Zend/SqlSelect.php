<?php

namespace Cly\Zend;

use Zend\Paginator\Adapter\AdapterInterface;

class SqlSelect implements AdapterInterface {
	public function getItems($offset, $itemCountPerPage) {
		$select = clone $this->select;
		$select->offset ( $offset );
		$select->limit ( $itemCountPerPage );
		
		$statement = $this->sql->prepareStatementForSqlObject ( $select );
		$result = $statement->execute ();
		
		$resultSet = clone $this->resultSetPrototype;
		$resultSet->initialize ( $result );
		
		return $resultSet;
	}
	public function count() {
		if ($this->rowCount !== null) {
			return $this->rowCount;
		}
		
		$select = clone $this->select;
		$select->reset ( Select::LIMIT );
		$select->reset ( Select::OFFSET );
		$select->reset ( Select::ORDER );
		
		$countSelect = new Select ();
		$countSelect->columns ( array (
				'c' => new Expression ( 'COUNT(1)' ) 
		) );
		$countSelect->from ( array (
				'original_select' => $select 
		) );
		
		$statement = $this->sql->prepareStatementForSqlObject ( $countSelect );
		$result = $statement->execute ();
		$row = $result->current ();
		
		$this->rowCount = $row ['c'];
		
		return $this->rowCount;
	}
}