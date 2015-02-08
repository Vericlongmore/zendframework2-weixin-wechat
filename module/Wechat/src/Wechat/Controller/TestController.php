<?php

namespace Wechat\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Wechat\Api\Menu\MenuItem;
use Wechat\Api\Menu\MenuFactory;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Db\Adapter\Adapter;
use Wechat\Travel\TravelService;
use Zend\View\Model\ViewModel;

class TestController extends AbstractActionController {
    public function testAction(){
        $a=2;
        $b=&$a;
        echo (++$a)+($a++);
        exit();
    }
	public function logAction() {
		$errorLog = $this->getServiceLocator ()->get ( 'errorLog' );
		$errorLog->debug ( 'test error log' );
		exit ();
	}
	public function viewAction() {
		$viewModel = new ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate ( 'wechat/empty' );
		return $viewModel;
	}
	public function tCodeAction() {
		$codeService = $this->getServiceLocator ()->get ( 'Wechat\Api\Code\CodeService' );
		$ticket = $codeService->createPermanentCode ( 110 );
		$code = $codeService->getCode ( $ticket );
		header ( "Content-type: image/jpeg" );
		echo $code;
		exit ();
	}
	public function codeAction() {
		$utf8_str = "你好，世界";
		$gbk_str = iconv ( 'utf-8', 'gbk//IGNORE', $utf8_str );
		$utf8_bin = bin2hex ( $utf8_str );
		$gbk_bin = bin2hex ( $gbk_str );
		$utf8_bin_decode = hex2bin ( $utf8_bin );
		$gbk_bin_decode = hex2bin ( $gbk_bin );
		echo "utf8_info:$utf8_str<br>";
		echo "gbk_info:$gbk_str<br>";
		echo '<hr>';
		echo "utf8_bin:$utf8_bin<br>";
		echo "gbk_bin:$gbk_bin<br>";
		/*
		 * var_dump ( iconv_get_encoding ( 'all' ) ); $len = iconv_strlen ( $str ); $len = iconv_strlen ( $str, 'ISO-8859-1' ); $len = iconv_strlen ( $str, 'utf-8' ); // iconv_set_encoding ( 'input_encoding', 'UTF-8' ); // iconv_set_encoding ( 'output_encoding', 'UTF-8' ); iconv_set_encoding ( 'internal_encoding', 'UTF-8' ); $len = iconv_strlen ( $str ); var_dump ( iconv_get_encoding ( 'all' ) );
		 */
		exit ();
	}
	public function pregAction() {
		$str = "nlp./image?
 size=392x220&rnd=hvdfz7gl&sig=d9c41e8f8f67b1d75ff6c790e3b97225fdf6f2b5&url=http%3A%2F
 %2Fimgsrc.baidu.com%2Fbaike%2Fpic%2Fitem%2Fd01373f082025aaff92dd92bfaedab64034f1a36.jpg";
		// 去除所有的空格和换行符
		$temp = preg_replace ( "/[\s]/", "", $str ) . '<br>';
		// 去除多余的空格和换行符，只保留一个
		echo $temp;
		echo preg_replace ( "/([\s]{2,})/", "\\1", $str );
		exit ();
	}
	public function sessionAction() {
		session_start ();
		if (isset ( $_SESSION ['views'] ))
			$_SESSION ['views'] = $_SESSION ['views'] + 1;
		else
			$_SESSION ['views'] = 1;
		echo "Views=" . $_SESSION ['views'];
		exit ();
	}
	public function clearAction() {
		session_start ();
		session_destroy ();
		exit ();
	}
	public function emptyAction() {
		$view = new ViewModel ();
		return $view->setTemplate ( 'wechat/empty.phtml' ); // ->setTerminal ( true );
	}
	public function travelAction() {
		$travelService = new TravelService ();
		var_dump ( $travelService->getSearchResult () );
		exit ();
	}
	public function dbAction() {
		$adapter = $this->getServiceLocator ()->get ( 'Adapter' );
		$resultSet = $adapter->query ( 'select * from user', Adapter::QUERY_MODE_EXECUTE );
		$data = $resultSet->current ();
		exit ();
	}
	public function tokenAction() {
		var_dump ( $this->getServiceLocator ()->get ( 'Wechat\Api\Base\TokenService' )->readToken () );
		exit ();
	}
	public function pluginAction() {
		$this->acceptableviewmodelselector ( array (
				'Zend\View\Model\JsonModel' => array (
						'application/json' 
				),
				'Zend\View\Model\FeedModel' => array (
						'application/rss+xml' 
				) 
		) );
	}
	public function menuAction() {
		$menuItem1 = new MenuItem ( 'click', '今日歌曲', 'V1001_TODAY_MUSIC' );
		$menuItem2 = new MenuItem ( 'click', '歌手简介', 'V1001_TODAY_SINGER' );
		$menuItem3 = new MenuItem ( 'view', '早报', 'http://www.xxx.com.cn' );
		$menuItem1->setSubButton ( array (
				$menuItem2,
				$menuItem3 
		) );
		$menu = MenuFactory::factory ( $menuItem1, $menuItem2, $menuItem3 );
		echo $menu->toJsonStr () . '<br><br>';
		$menu1 = MenuFactory::factory ( json_encode ( $menu->toJsonArray () ) );
		echo $menu1->toJsonStr () . '<br><br><br>';
		echo MenuFactory::factory ( $menuItem1 )->toJsonStr ();
		exit ();
	}
	public function menuQueryAction() {
		$menu = $this->getServiceLocator ()->get ( 'Wechat\Api\Menu\MenuService' )->query ();
		if ($menu)
			echo $menu->toJsonStr ();
		exit ();
	}
	public function menuCreateAction() {
		$menuStr = '{
     "button":[
     {	
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "type":"click",
           "name":"歌手简介",
           "key":"V1001_TODAY_SINGER"
      },
      {
           "name":"菜单",
           "sub_button":[
           {	
               "type":"view",
               "name":"客服",
               "url":"http://shang.qq.com/open_webaio.html?sigt=6c794c645468fa34b69899410e316360a865bdd3b9863b4157b1bb461d0772bbacf6a75a402b29ac6212b8d1e20994c0&sigu=3559546814984a5668b2bf1169a848eae0f51eda14efd097f5d535e68c34be648a7e6d983313b6ed&tuin=52508805"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }';
		$menu = MenuFactory::factory ( $menuStr );
		$result = $this->getServiceLocator ()->get ( 'Wechat\Api\Menu\MenuService' )->create ( $menu );
		echo $result;
		exit ();
	}
	public function menuDeleteAction() {
		$result = $this->getServiceLocator ()->get ( 'Wechat\Api\Menu\MenuService' )->delete ();
		echo $result;
		exit ();
	}
	public function httpAction() {
		$request = new Request ();
		$request->setUri ( 'http://www.wechatzf2local.com/wechat/test/token' );
		$client = new Client ();
		$response = $client->dispatch ( $request );
		echo $response->getContent ();
		exit ();
	}
	public function sslAction() {
		// /cgi-bin/menu/create
		$fp1 = stream_context_create ();
		$fp = stream_socket_client ( "tcp://api.weixin.qq.com:443", $errno, $errstr, 30, 4, $fp1 );
		if (! $fp) {
			die ( "Unable to connect: $errstr ($errno)" );
		}
		$result = stream_socket_enable_crypto ( $fp, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT );
		// fwrite ( $fp, "USER god\r\n" );
		// fwrite ( $fp, "PASS secret\r\n" );
		$result = stream_socket_enable_crypto ( $fp, false );
		while ( $motd = fgets ( $fp ) ) {
			echo $motd;
		}
		fclose ( $fp );
		echo 11111111111;
		exit ();
	}
	public function preg1Action() {
		$keywords = '$40 for a g3/400';
		$keywords = preg_quote ( $keywords, '/' );
		echo $keywords; // returns \$40 for a g3\/400
		echo '<br>';
		$textbody = "This book is *very* difficult to find.";
		$word = "*very*";
		$textbody = preg_replace ( "/" . preg_quote ( $word ) . "/", "<i>" . $word . "</i>", $textbody );
		echo $textbody;
		exit ();
	}
}