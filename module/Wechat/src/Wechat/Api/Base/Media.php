<?php

namespace Wechat\Api\Base;

/**
 *
 * @author kwdwkiss
 *         图片（image）: 128K，支持JPG格式
 *         语音（voice）：256K，播放长度不超过60s，支持AMR\MP3格式
 *         视频（video）：1MB，支持MP4格式
 *         缩略图（thumb）：64KB，支持JPG格式
 * @see 公众号在使用接口时，对多媒体文件、多媒体消息的获取和调用等操作， 是通过media_id来进行的。通过本接口，公众号可以上传或下载多媒体文件。
 *      但请注意，每个多媒体文件（media_id）会在上传、用户发送到微信服务器3天后自动删除，以节省服务器资源。
 */
class Media extends BaseArrayObject {
	const TYPE_IMAGE = 'image';
	const TYPE_VOICE = 'voice';
	const TYPE_VIDEO = 'video';
	const TYPE_THUMB = 'thumb';
	/**
	 */
	const TYPE = 'type';
	const MEDIA_ID = 'meida_id';
	const MEDIA = 'media';
	const CREATE_AT = 'create_at';
	const FILE_PATH = 'filePath';
	public function getType() {
		return $this [static::TYPE];
	}
	public function getFilePath() {
		return $this [static::FILE_PATH];
	}
	public function setFilePath($filePath) {
		$this [static::FILE_PATH] = $filePath;
	}
	public function getMediaId() {
		return $this [static::MEDIA_ID];
	}
	public function getCreateAt() {
		return $this [static::CREATE_AT];
	}
	public function isExpired() {
		return time () - $this->getCreateAt () >= 3 * 86400 ?  : false;
	}
}