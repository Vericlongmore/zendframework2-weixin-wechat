<?php

namespace Wechat\Web\Form;

use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractController;

abstract class BaseForm extends Form {
	public function __construct($formName = null, $action = '', $method = 'post') {
		parent::__construct ( $formName );
		if ($action instanceof AbstractController) {
			$action = $action->getRequest ()->getRequestUri ();
		}
		$this->setAttribute ( 'action', $action );
		$this->setAttribute ( 'method', $method );
	}
}