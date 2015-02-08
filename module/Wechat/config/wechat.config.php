<?php
return array (
		'serverUrl' => 'http://www.wechatzf2local.com/',
		'accessUrl' => 'wechat/app/index/',
		'expire_time' => 86400 * 10,
		'msg_service' => array (
				'send_url' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send' 
		),
		'token_service' => array (
				'grant_type' => 'client_credential',
				'token_url' => 'https://api.weixin.qq.com/cgi-bin/token' 
		),
		'menu_service' => array (
				'createUrl' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
				'queryUrl' => 'https://api.weixin.qq.com/cgi-bin/menu/get',
				'deleteUrl' => 'https://api.weixin.qq.com/cgi-bin/menu/delete' 
		),
		'group_service' => array (
				'createUrl' => 'https://api.weixin.qq.com/cgi-bin/groups/create',
				'queryGroupUrl' => 'https://api.weixin.qq.com/cgi-bin/groups/get',
				'queryGroupIdUrl' => 'https://api.weixin.qq.com/cgi-bin/groups/getid',
				'updateGroupNameUrl' => 'https://api.weixin.qq.com/cgi-bin/groups/update',
				'moveGroupForUserUrl' => 'https://api.weixin.qq.com/cgi-bin/groups/members/update' 
		),
		'user_service' => array (
				'infoUrl' => 'https://api.weixin.qq.com/cgi-bin/user/info',
				'subscriberUrl' => 'https://api.weixin.qq.com/cgi-bin/user/get' 
		),
		'media_service' => array (
				'uploadUrl' => 'http://file.api.weixin.qq.com/cgi-bin/media/upload',
				'getUrl' => 'http://file.api.weixin.qq.com/cgi-bin/media/get' 
		),
		'access_service' => array (
				'serverUrl' => 'http://kwdwkiss.gicp.net/',
				'accessUrl' => 'wechat/app/index/' 
		)
);