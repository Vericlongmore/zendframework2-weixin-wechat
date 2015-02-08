<?php

namespace Wechat\Api\Menu;

use Wechat\Api\Base\HttpService;

class MenuService extends HttpService {
	public function query() {
		return MenuFactory::factory ($this->queryToJsonStr());
	}
	public function queryToJsonStr(){
	    $url = $this->buildTokenUrl ( $this->getOptions ('queryUrl') );
	    if ($result = $this->httpGet ( $url )) {
	    	if (! $this->isError ( $result )) {
	    		return $result;
	    	}
	    }
	    return false;
	}
	public function create(Menu $menu) {
		return $this->createByJsonStr($menu->toJsonStr());
	}
	public function createByJsonStr($jsonStr) {
	    $url = $this->buildTokenUrl ( $this->getOptions ('createUrl') );
	    if ($result = $this->httpPost ( $url, $jsonStr )) {
	    	if (! $this->isError ( $result ))
	    		return true;
	    }
	    return false;
	}
	public function delete() {
		$url = $this->buildTokenUrl ( $this->getOptions ('deleteUrl') );
		if ($result = $this->httpGet ( $url )) {
			if (! $this->isError ( $result ))
				return true;
		}
		return false;
	}
}