<?php

namespace Wechat\Api\User;

use Wechat\Api\Base\HttpService;
use Wechat\Api\User\GroupItem;
use Wechat\Db\Entity\WcApp;

class GroupService extends HttpService {
	public function create($name) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'createUrl' ) );
		$dataArray = array (
				GroupItem::GROUP => array (
						GroupItem::NAME => $name 
				) 
		);
		$content = $this->encode ( $dataArray );
		if ($result = $this->httpPost ( $url, $content )) {
			if (! $this->isError ( $result ))
				return new GroupItem ( json_decode ( $result, true ) );
		}
		return false;
	}
	public function queryGroup() {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'queryGroupUrl' ) );
		if ($result = $this->httpGet ( $url )) {
			if (! $this->isError ( $result )) {
				return json_decode ( $result, true );
			}
		}
		return false;
	}
	public function queryGroupId($openId) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'queryGroupIdUrl' ) );
		$dataString = array (
				GroupItem::OPEN_ID => $openId 
		);
		$content = $this->encode ( $dataString );
		if ($result = $this->httpPost ( $url, $content )) {
			if (! $this->isError ( $result ))
				return $json->{GroupItem::GROUP_ID};
		}
		return false;
	}
	public function updateGroupName($groupId, $name) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'updateGroupNameUrl' ) );
		$dataString = array (
				GroupItem::GROUP => array (
						GroupItem::ID => $groupId,
						GroupItem::NAME => $name 
				) 
		);
		$content = $this->encode ( $dataString );
		if ($result = $this->httpPost ( $url, $content )) {
			if (! $this->isError ( $result ))
				return true;
		}
		return false;
	}
	public function moveGroupForUser($toGroupId, $openId) {
		$url = $this->buildTokenUrl ( $this->getOptions ( 'moveGroupForUserUrl' ) );
		$dataString = array (
				GroupItem::OPEN_ID => $openId,
				GroupItem::TO_GROUP_ID => $toGroupId 
		);
		$content = $this->encode ( $dataString );
		if ($result = $this->httpPost ( $url, $content )) {
			if (! $this->isError ( $result ))
				return true;
		}
		return false;
	}
	public function getGroupList($refresh = null) {
		$wcApp = $this->getWcApp ();
		$group = $wcApp->getGroupList ();
		$now = time ();
		if ($refresh || ! $group || $now - $group ['time'] >= 3600 * 24) {
			$group = $this->queryGroup ();
			$wcApp->setGroupList ( $group );
			$this->getTable ( WcApp::TABLE )->updateRow ( $wcApp );
		}
		return $group ['groups'];
	}
}
?>