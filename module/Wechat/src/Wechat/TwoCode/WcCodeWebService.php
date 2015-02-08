<?php

namespace Wechat\TwoCode;

use Wechat\Web\Service\BaseWebService;
use Zend\View\Model\ViewModel;
use Cly\Common\ClyLib;
use Wechat\Db\Entity\WcCode;
use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class WcCodeWebService extends BaseWebService {
	protected $options = array (
			'item_count' => 20 
	);
	public function run(\Zend\Mvc\Controller\AbstractController $controller) {
		$wcApp = $this->getWcApp ();
		$aid = $wcApp->getAid ();
		$baseUri = $controller->getRequest ()->getUri ();
		$action = $controller->params ()->fromQuery ( 'action' );
		switch ($action) {
			case 'add' :
				$codeService = $this->getService ( 'Wechat\Api\Code\CodeService' );
				$sceneId = $controller->params ()->fromPost ( 'sceneId' );
				$note = $controller->params ()->fromPost ( 'note' );
				if ($sceneId) {
					$code = $codeService->getPCode ( $sceneId );
					$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
							WcCode::SCENE_ID => $sceneId,
							WcCode::AID => $aid 
					) );
					if ($wcCode) {
						$wcCode->setCode ( $code );
						$wcCode->setType ( 'permanent' );
						$wcCode->setNote ( $note );
						$this->getTable ( WcCode::TABLE )->updateRow ( $wcCode );
					} else {
						$wcCode = new WcCode ();
						$wcCode->setAid ( $aid );
						$wcCode->setSceneId ( $sceneId );
						$wcCode->setCode ( $code );
						$wcCode->setType ( 'temporary' );
						$wcCode->setNote ( $note );
						$this->getTable ( WcCode::TABLE )->insertRow ( $wcCode );
					}
				}
				break;
			case 'count' :
				$sceneId = $controller->params ()->fromQuery ( 'sceneId' );
				if ($sceneId && $aid) {
					$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
							WcCode::SCENE_ID => $sceneId,
							WcCode::AID => $aid 
					) );
					$cid = $wcCode->getCid ();
				}
				$viewModel = new ViewModel ();
				$viewModel->setTemplate ( 'wechat/service/wc_code_active' );
				return $viewModel;
				break;
			case 'delete' :
				$sceneId = $controller->params ()->fromQuery ( 'sceneId' );
				if ($sceneId && $aid) {
					$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
							WcCode::SCENE_ID => $sceneId,
							WcCode::AID => $aid 
					) );
					$cid = $wcCode->getCid ();
					if ($wcCode) {
						$sqlStr = "delete from wc_code where sceneId=$sceneId and aid=$aid";
						$result = $this->getDb ()->queryExecute ( $sqlStr );
						if ($result && $cid) {
							$sqlStr = "delete from wc_code_active where cid=$cid";
							$result = $this->getDb ()->queryExecute ( $sqlStr );
						}
					}
				}
				break;
			case 'show' :
				$sceneId = $controller->params ()->fromQuery ( 'sceneId' );
				if ($sceneId) {
					$wcCode = $this->getTable ( WcCode::TABLE )->selectOne ( array (
							WcCode::SCENE_ID => $sceneId,
							WcCode::AID => $aid 
					) );
					if ($wcCode && $code = $wcCode->getCode ()) {
						header ( "Content-type: image/jpeg" );
						echo $code;
					}
					exit ();
				}
				break;
		}
		$sqlStr = "select * from wc_code where aid=$aid";
		$select = new Select ( WcCode::TABLE );
		$select->where ( array (
				WcCode::AID => $aid 
		) );
		$paginator = new Paginator ( new DbSelect ( $select, $this->getAdapter () ) );
		$page = $controller->params ()->fromQuery ( 'page' );
		$paginator->setCurrentPageNumber ( $page );
		$paginator->setItemCountPerPage ( $this->getOptions ( 'item_count' ) );
		$viewModel = new ViewModel ( array (
				'service' => $this,
				'addUrl' => ClyLib::addQuery ( $baseUri, array (
						'action' => 'add' 
				) ),
				'baseUri' => $baseUri,
				'paginator' => $paginator 
		) );
		$viewModel->setTemplate ( 'wechat/service/wc_code' );
		return $viewModel;
	}
}