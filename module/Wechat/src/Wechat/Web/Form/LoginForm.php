<?php

namespace Wechat\Web\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class LoginForm extends BaseForm {
	public function __construct($formName = null, $action = '', $method = 'post') {
		parent::__construct ( $formName, $action, $method );
		$this->add ( array (
				'name' => 'username',
				'type' => 'Text',
				'options' => array (
						'label' => '用户名' 
				),
				'attributes' => array (
						'id' => 'username' 
				) 
		) );
		$this->add ( array (
				'name' => 'password',
				'type' => 'Password',
				'options' => array (
						'label' => '密码' 
				),
				'attributes' => array (
						'id' => 'password' 
				) 
		) );
		$this->add ( array (
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array (
						'value' => '登录' 
				) 
		) );
		$this->add ( array (
				'name' => 'register',
				'type' => 'Button',
				'options' => array (
						'label' => '注册'
				),
				'attributes' => array (
						'type' => 'submit',
						'formaction' => '/wechat/index/register'
				)
		) );
		$inputFilter = new InputFilter ();
		$factory = new InputFactory ();
		$inputFilter->add ( $factory->createInput ( array (
				'name' => 'username',
				'required' => true,
				'filters' => array (
						array (
								'name' => 'StripTags' 
						),
						array (
								'name' => 'StringTrim' 
						) 
				) 
		) ) );
		
		$inputFilter->add ( $factory->createInput ( array (
				'name' => 'password',
				'required' => true,
				'filters' => array (
						array (
								'name' => 'StripTags' 
						),
						array (
								'name' => 'StringTrim' 
						) 
				) 
		) ) );
		$this->setInputFilter ( $inputFilter );
	}
}