<?php

namespace Wechat\Api\Base;

class MediaService extends HttpService {
	protected $options = array (
			'attachmentPath' => 'data/attachment',
			'extension' => array (
					'image' => '.jpg',
					'voice' => '.mp3',
					'video' => '.mp4',
					'thumb' => '.jpg' 
			) 
	);
	public function upload($filePath, $type) {
		$url = $this->buildUploadUrl ( $type );
		if ($result = $this->httpPost ( $url, '', array (
				'media' => $filePath 
		) )) {
			if (! $this->isError ( $result )) {
				$media = new Media ( json_decode ( $result, true ) );
				$media->setFilePath ( $filePath );
				return $media;
			}
		}
		return false;
	}
	public function download($mediaId) {
		$url = $this->buildDownloadUrl ( $mediaId );
		if ($result = $this->httpPost ( $url )) {
			if (! $this->isError ( $result )) {
				return $result;
			}
		}
		return false;
	}
	public function saveFile($content, $filePath) {
		@mkdir ( dirname ( $filePath ), 0777, true );
		return file_put_contents ( $filePath, $content );
	}
	public function getFilePath($fileName, $appUser = 'common', $extension = '') {
		// not use DIRECTORY_SEPARATOR,because windows and linux switch,this save in db
		$path = $this->getOptions ( 'attachmentPath' );
		$path .= '/' . $appUser;
		$path .= '/' . date ( "ymd", time () );
		return $path . '/' . $fileName . $extension;
	}
	public function getExtension($mediaType) {
		$map = $this->getOptions ( 'extension' );
		if (isset ( $map [$mediaType] )) {
			return $map [$mediaType];
		}
		return '';
	}
	protected function buildUploadUrl($type) {
		return $this->buildTokenUrl ( $this->getOptions ( 'uploadUrl' ) ) . '&type=' . $type;
	}
	protected function buildDownloadUrl($mediaId) {
		return $this->buildTokenUrl ( $this->getOptions ( 'getUrl' ) ) . '&media_id=' . $mediaId;
	}
}
?>