<?php

namespace Cly\Common;

class ClyLib {
	public static function getValuesFromArrayByKeys($msg, $keys) {
		$keys = ( array ) $keys;
		$array = array ();
		foreach ( $keys as $key ) {
			$msg [$key] = $msg [$key] ?  : '';
			array_push ( $array, $msg [$key] );
		}
		return $array;
	}
	public static function is_assoc($arr) {
		return array_keys ( $arr ) !== range ( 0, count ( $arr ) - 1 );
	}
	public static function array_delete($array, $value) {
		$key = array_search ( $value, $array );
		if ($key === false) {
		} elseif (is_numeric ( $key )) {
			array_splice ( $array, $key, 1 );
		} elseif (is_string ( $key )) {
			unset ( $array [$key] );
		} else {
			throw new \Exception ( __METHOD__ );
		}
		return $array;
	}
	public static function mergeArray($array1, $array2) {
		foreach ( $array1 as $key => $value ) {
			if (is_array ( $array1 [$key] )) {
				if (isset ( $array2 [$key] ) && is_array ( $array2 [$key] ))
					$array2 [$key] = $this->mergeArray ( $array1 [$key], $array2 [$key] );
			}
		}
		if ($this->is_assoc ( $array1 ))
			return array_unique ( array_merge ( $array1, $array2 ) );
	}
	public static function addQuery($uri, $queryArray) {
		$newUri = clone $uri;
		$querys = $newUri->getQueryAsArray ();
		$querys = array_merge ( $querys, $queryArray );
		$newUri->setQuery ( http_build_query ( $querys ) );
		return $newUri->getPath () . '?' . $newUri->getQuery ();
	}
	public static function toUriStr($uri) {
		$path = $uri->getPath ();
		$queryStr = $uri->getQuery ();
		if ($queryStr)
			return $path . '?' . $queryStr;
		return $path;
	}
	public static function log($filePath, $content) {
		@mkdir ( dirname ( $filePath ), 0777, true );
		static::writeFile ( $filePath, $content );
	}
	public static function writeFile($filePath, $content, $mode = 0777) {
		if (! $filePath || ! $content)
			return false;
		$dir = dirname ( $filePath );
		if (! file_exists ( $dir )) {
			mkdir ( dirname ( $filePath ), $mode, true );
		}
		if ($fp = fopen ( $filePath, "a" )) {
			if (@fwrite ( $fp, $content )) {
				fclose ( $fp );
				return true;
			} else {
				fclose ( $fp );
				return false;
			}
		}
		return false;
	}
	public static function microtime_float() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}
	public static function makeDir($dir, $mode = 0777) {
		if (file_exists ( $dir ))
			return true;
		if (! static::makeDir ( dirname ( $dir ), $mode ))
			return false;
		return mkdir ( $dir, $mode );
	}
	public static function makeDirFromFile($filePath, $mode = 0777) {
		$dir = dirname ( $filePath );
		if (file_exists ( $dir ))
			return true;
		return static::makeDir ( $dir, $mode );
	}
	function StrToBin($str){
		//1.列出每个字符
		$arr = preg_split('/(?<!^)(?!$)/u', $str);
		//2.unpack字符
		foreach($arr as &$v){
			$temp = unpack('H*', $v);
			$v = base_convert($temp[1], 16, 2);
			unset($temp);
		}
	
		return join(' ',$arr);
	}
	function BinToStr($str){
		$arr = explode(' ', $str);
		foreach($arr as &$v){
			$v = pack("H".strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
		}
	
		return join('', $arr);
	}
}