<?php

namespace Wechat\Api\Msg;

class ResponseNewsMsg extends ResponseMsg {
	private $msgTpl = "<ArticleCount>%s</ArticleCount>";
	private $itemTpl = "<item>
                        <Title><![CDATA[%s]]></Title> 
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                        </item>";
	public function __construct(array $values = null) {
		parent::__construct ( $values );
		$this ['MsgType'] = 'news';
	}
	protected function fillTpl() {
		$str = parent::fillTpl ();
		$str .= sprintf ( $this->msgTpl, $this->getArticleCount () );
		$str .= '<Articles>' . $this->fillItemTpl () . '<Articles>';
		return $str;
	}
	protected function fillItemTpl() {
		$news = $this->getNews ();
		$itemsStr = '';
		foreach ( $news as $item ) {
			$itemsStr .= sprintf ( $this->itemTpl, $item ['Title'], $item ['Description'], $item ['PicUrl'], $item ['Url'] );
		}
		return $itemsStr;
	}
	protected function toSendArray() {
		$news = array ();
		foreach ( $this->getNews () as $item ) {
			$news [] = array (
					'title' => $item ['Title'],
					'description' => $item ['Description'],
					'url' => $item ['Url'],
					'picurl' => $item ['PicUrl'] 
			);
		}
		$sendArray = array_merge ( parent::toSendArray (), array (
				'msgtype' => $this ['MsgType'],
				'news' => array (
						'articles' => $news 
				) 
		) );
		return $sendArray;
	}
	public function getArticleCount() {
		return $this ['ArticleCount'] ?  : 0;
	}
	public function getNews() {
		return $this ['news'] ?  : array ();
	}
	public function getPopulateArray() {
		$array = parent::getPopulateArray ();
		$array ['news'] = serialize ( $array ['news'] );
		return $array;
	}
	public function addItems(array $items) {
		$count = $this->getArticleCount ();
		$items = array_splice ( $items, 0, 10 - $count );
		$news = $this->getNews ();
		$news = array_merge ( $news, $items );
		$this->setArticleCount ( count ( $news ) );
		$this ['news'] = $news;
	}
	public function __toString() {
		$array = $this->getArrayCopy ();
		$str = '';
		foreach ( $array ['news'] as $k => $v ) {
			$str .= $v ['Title'] . "\n";
		}
		$array ['news'] = $str;
		return var_export ( $array, true );
	}
}