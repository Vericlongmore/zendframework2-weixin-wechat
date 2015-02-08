<?php
namespace Wechat\Api\Msg;

interface ResponseMsgInterface{
    public function toResponseStr();
    public function toSendStr();
}