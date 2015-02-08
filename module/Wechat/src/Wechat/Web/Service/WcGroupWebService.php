<?php

namespace Wechat\Web\Service;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

class WcGroupWebService extends BaseWebService {
	protected $options = array (
			'item_count' => '20' 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$uri = $controller->getRequest ()->getUri ();
		$page = $controller->params ()->fromRoute ( 'id' );
		$action = $controller->params ()->fromQuery ( 'action' );
		$groupId = $controller->params ()->fromPost ( 'groupId' );
		$groupName = $controller->params ()->fromPost ( 'groupName' );
		switch ($action) {
			case 'add' :
				if ($groupName) {
					if ($this->getService ( 'Wechat\Api\User\GroupService' )->create ( $groupName ))
						$refresh = true;
				}
				break;
			case 'modify' :
				if ($groupId && $groupName) {
					if ($this->getService ( 'Wechat\Api\User\GroupService' )->updateGroupName ( $groupId, $groupName ))
						$refresh = true;
				}
				break;
			case 'delete' :
				break;
		}
		$gourpArray = $this->getService ( 'Wechat\Api\User\GroupService' )->getGroupList ( $refresh );
		$paginator = new Paginator ( new ArrayAdapter ( ( array ) $gourpArray ) );
		$paginator->setCurrentPageNumber ( $page );
		$paginator->setItemCountPerPage ( $this->getOptions ( 'item_count' ) );
		$viewModel = new ViewModel ( array (
				'baseUri' => $uri,
				'paginator' => $paginator 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_group' );
		return $viewModel;
	}
}