<?php
/**
 * 基本验证类
 */
class base_Utils {
	
	/**
	 * 安全过滤数据
	 * @param string	$str		需要处理的字符
	 * @param string	$type		返回的字符类型，支持，string,int,float,html
	 * @param maxid		$default	当出现错误或无数据时默认返回值
	 * @return 		mixed		当出现错误或无数据时默认返回值
	 */
	public static function getStr($str, $type = 'string', $default = '') {
		if ($str === '')
			return $default;
		switch ($type) {
			case 'string' : //字符处理
				$_str = strip_tags ( $str );
				$_str = str_replace ( "'", '&#39;', $_str );
				$_str = str_replace ( "\"", '&quot;', $_str );
				$_str = str_replace ( "\\", '', $_str );
				$_str = str_replace ( "\/", '', $_str );
				break;
			case 'int' : //获取整形数据
				$_str = ( int ) $str;
				break;
			case 'float' : //获浮点形数据
				$_str = ( float ) $str;
				break;
			case 'html' : //获取HTML，防止XSS攻击
				$_str = self::reMoveXss ( $str );
				break;
			default : //默认当做字符处理
				$_str = strip_tags ( $str );
		}
		return $_str;
	}
	
	/**
	 * 取消HTML特殊字符 防止XSS
	 * @param $string 可以为字符或者数组
	 * @return $string 可以为字符或者数组
	 */
	public static function shtmlspecialchars($string) {
		if (is_array ( $string )) {
			foreach ( $string as $key => $val ) {
				$string [$key] = self::shtmlspecialchars ( $val );
			}
		} else {
			$string = self::getStr($string,'html');
			//$string = preg_replace ( '/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1', str_replace ( array ('&', '"', '<', '>' ), array ('&amp;', '&quot;', '&lt;', '&gt;' ), $string ) );
		}
		return $string;
	}
	
	/**
	 * 取消HTML特殊字符 防止XSS
	 * @param $array 可以为字符或者数组
	 * @return $array 可以为字符或者数组
	 */
	public static function specialhtml($array) {
		if (is_array ( $array )) {
			foreach ( $array as $key => $value ) {
				if (! is_array ( $value )) {
					$array [$key] = htmlspecialchars ( $value );
				} else {
					self::specialhtml ( $array [$key] );
				}
			}
		} else {
			$array = htmlspecialchars ( $array );
		}
	}
	
	/**
	 * 获取当前在线IP地址
	 * @param $format
	 * @return $format = 0 返回IP地址：127.0.0.1
	 * $format = 1 返回IP长整形：2130706433
	 */
	public static function getIp($format = 0) {
		global $_SGLOBAL;
		if (empty ( $_SGLOBAL ['onlineip'] )) {
			if (getenv ( 'HTTP_CLIENT_IP' ) && strcasecmp ( getenv ( 'HTTP_CLIENT_IP' ), 'unknown' )) {
				$onlineip = getenv ( 'HTTP_CLIENT_IP' );
			} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' ) && strcasecmp ( getenv ( 'HTTP_X_FORWARDED_FOR' ), 'unknown' )) {
				$onlineip = getenv ( 'HTTP_X_FORWARDED_FOR' );
			} elseif (getenv ( 'REMOTE_ADDR' ) && strcasecmp ( getenv ( 'REMOTE_ADDR' ), 'unknown' )) {
				$onlineip = getenv ( 'REMOTE_ADDR' );
			} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], 'unknown' )) {
				$onlineip = $_SERVER ['REMOTE_ADDR'];
			}
			preg_match ( "/[\d\.]{7,15}/", $onlineip, $onlineipmatches );
			$_SGLOBAL ['onlineip'] = $onlineipmatches [0] ? $onlineipmatches [0] : 'unknown';
		}
		if (! $format) {
			return $_SGLOBAL ['onlineip'];
		} else {
			return sprintf ( '%u', ip2long ( $_SGLOBAL ['onlineip'] ) );
		}
	}
	
	/**
	 * 产生随机数
	 * @param $length 产生随机数长度
	 * @param $type 返回字符串类型
	 * @param $hash  是否由前缀，默认为空. 如:$hash = 'zz-'  结果zz-823klis
	 * @return 随机字符串 $type = 0：数字+字母
	 $type = 1：数字
	 $type = 2：字符
	 */
	public static function random($length, $type = 0, $hash = '') {
		if ($type == 0) {
			$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		} else if ($type == 1) {
			$chars = '0123456789';
		} else if ($type == 2) {
			$chars = 'abcdefghijklmnopqrstuvwxyz';
		}
		$max = strlen ( $chars ) - 1;
		mt_srand ( ( double ) microtime () * 1000000 );
		for($i = 0; $i < $length; $i ++) {
			$hash .= $chars [mt_rand ( 0, $max )];
		}
		return $hash;
	}
	
	/**
	 * 获取时间差
	 * @param $begin_time 开始时间
	 * @param $end_time 结束时间
	 * @return 数组
	 */
	public static function timediff($begin_time, $end_time) {
		if ($begin_time > $end_time) {
			return '-1'; //time is wrong
		} else {
			$timediff = $end_time - $begin_time;
			$days = intval ( $timediff / 86400 );
			$remain = $timediff % 86400;
			$hours = intval ( $remain / 3600 );
			$remain = $remain % 3600;
			$mins = intval ( $remain / 60 );
			$secs = $remain % 60;
			$res = array ("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
			return $res;
		}
	}
	
	/**
	 * cookie设置
	 * @param $var 设置的cookie名
	 * @param $value 设置的cookie值
	 * @param $life 设置的过期时间：为整型，单位秒 如60表示60秒后过期
	 * @param $path 设置的cookie作用路径
	 * @param $domain 设置的cookie作用域名
	 */
	public static function ssetcookie($array, $life = 0, $path = '/', $domain = '') {
		global $_SERVER;
		$_cookName_ary = array_keys ( $array );
		for($i = 0; $i < count ( $array ); $i ++) {
			setcookie ( $_cookName_ary [$i], $array [$_cookName_ary [$i]], $life ? (time () + $life) : 0, $path, $domain, $_SERVER ['SERVER_PORT'] == 443 ? 1 : 0 );
		}
	}
	
	/**
	 * 截取字符函数
	 * @param $string 要截取的字符串
	 * @param $len 截取长度
	 * @param $code 字符编码
	 * @param $prefix 新截取字符的前缀
	 * @param $add 处理后字符串加的后缀,如'...'
	 */
	public static function cutstr($string, $len, $code = 'utf-8', $prefix = '', $add = '') {
		if (is_array ( $string )) {
			foreach ( $string as $key => $val ) {
				if (mb_strlen ( $val, $code ) > $len) {
					$key = $prefix . $key;
					$string [$key] = mb_substr ( $val, 0, $len, $code );
					$string [$key] .= $add;
				} else {
					$key = $prefix . $key;
					$string [$key] = $val;
				}
			}
		} else {
			if (mb_strlen ( $string, $code ) > $len) {
				$string = mb_substr ( $string, 0, $len, $code );
				$string .= $add;
			}
		}
		return $string;
	}
	
	//过滤XSS攻击
	function reMoveXss($val) {
		$val = preg_replace ( '/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val );
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for($i = 0; $i < strlen ( $search ); $i ++) {
			$val = preg_replace ( '/(&#[xX]0{0,8}' . dechex ( ord ( $search [$i] ) ) . ';?)/i', $search [$i], $val ); // with a ;
			$val = preg_replace ( '/(&#0{0,8}' . ord ( $search [$i] ) . ';?)/', $search [$i], $val ); // with a ;
		}
		$ra1 = Array ('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base' );
		$ra2 = Array ('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload' );
		$ra = array_merge ( $ra1, $ra2 );
		$found = true; // keep replacing as long as the previous round replaced something
		while ( $found == true ) {
			$val_before = $val;
			for($i = 0; $i < sizeof ( $ra ); $i ++) {
				$pattern = '/';
				for($j = 0; $j < strlen ( $ra [$i] ); $j ++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra [$i] [$j];
				}
				$pattern .= '/i';
				$replacement = substr ( $ra [$i], 0, 2 ) . '<x>' . substr ( $ra [$i], 2 ); // add in <> to nerf the tag
				$val = preg_replace ( $pattern, $replacement, $val ); // filter out the hex tags
				if ($val_before == $val) {
					$found = false;
				}
			}
		}
		return $val;
	}
	
	/** 
	 * IsUsername函数:检测是否符合用户名格式 
	 * $Argv是要检测的用户名参数 
	 * $RegExp是要进行检测的正则语句 
	 * 返回值:符合用户名格式返回用户名,不是返回false 
	 */
	public static function IsUsername($Argv) {
		$RegExp = '/^[a-z0-9_]{4,16}$/'; //由大小写字母跟数字下划线组成并且长度在4-16字符直接
		$stara = substr ( $Argv, 0, 1 );
		$sRegExp = '/^\d*$/'; //判断首字符是否为字母
		return preg_match ( $RegExp, $Argv ) && ! preg_match ( $sRegExp, $stara ) ? true : false;
	}
	
	/** 
	 * IsMail函数:检测是否为正确的邮件格式 
	 * 返回值:是正确的邮件格式返回邮件,不是返回false 
	 */
	public static function IsMail($Argv) {
		$RegExp = '/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/';
		if (preg_match ( $RegExp, $Argv )) {
			if (strlen ( $Argv ) >= 50) {
				return 2; //长度操作50
			}
			if (strlen ( $Argv ) > 4) {
				$lvMailPostfix = strtolower ( substr ( $Argv, strlen ( $Argv ) - 4, strlen ( $Argv ) ) );
				$lvMailPostfix1 = strtolower ( substr ( $Argv, strlen ( $Argv ) - 3, strlen ( $Argv ) ) );
				if ($lvMailPostfix == ".com" || $lvMailPostfix == ".net" || $lvMailPostfix == ".org" || $lvMailPostfix1 == ".cn") {
					return 0;
				} else {
					return 3; //后缀不正确
				}
			} else {
				return 4; //不允许的邮件格式
			}
		} else {
			return 1; // 格式不正确 
		}
	}
	
	/** 
	 * IsQQ函数:检测参数的值是否符合QQ号码的格式 
	 * 返回值:是正确的QQ号码返回QQ号码,不是返回false 
	 */
	public static function IsQQ($Argv) {
		$RegExp = '/^[1-9][0-9]{5,11}$/';
		return preg_match ( $RegExp, $Argv ) ? $Argv : false;
	}
	
	/** 
	 * IsMobile函数:检测参数的值是否为正确的中国手机号码格式 
	 * 返回值:是正确的手机号码返回手机号码,不是返回false 
	 */
	public static function IsMobile($Argv) {
		$RegExp = '/^(?:13|15|18|14)[0-9]\d{8}$/';
		return preg_match ( $RegExp, $Argv ) ? true : false;
	}
	
	//验证身份证
	public static function validation_filter_id_card($id_card) {
		if (strlen ( $id_card ) == 18) {
			return self::idcard_checksum18 ( $id_card );
		} elseif ((strlen ( $id_card ) == 15)) {
			$id_card = self::idcard_15to18 ( $id_card );
			return self::idcard_checksum18 ( $id_card );
		} elseif ($id_card == 'S7935588G') { //台湾
			return true;
		} elseif ((strlen ( $id_card )) == 10) {
			return self::hkidcard ( $id_card );
		} elseif ($id_card == '0442268402(B)') {
			return true;
		} else {
			return false;
		}
	}
	
	// 计算身份证校验码，根据国家标准GB 11643-1999
	function idcard_verify_number($idcard_base) {
		if (strlen ( $idcard_base ) != 17) {
			return false;
		}
		//加权因子
		$factor = array (7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 );
		//校验码对应值
		$verify_number_list = array ('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2' );
		$checksum = 0;
		for($i = 0; $i < strlen ( $idcard_base ); $i ++) {
			$checksum += substr ( $idcard_base, $i, 1 ) * $factor [$i];
		}
		$mod = $checksum % 11;
		$verify_number = $verify_number_list [$mod];
		return $verify_number;
	}
	
	// 将15位身份证升级到18位
	function idcard_15to18($idcard) {
		if (strlen ( $idcard ) != 15) {
			return false;
		} else {
			// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
			if (array_search ( substr ( $idcard, 12, 3 ), array ('996', '997', '998', '999' ) ) !== false) {
				$idcard = substr ( $idcard, 0, 6 ) . '18' . substr ( $idcard, 6, 9 );
			} else {
				$idcard = substr ( $idcard, 0, 6 ) . '19' . substr ( $idcard, 6, 9 );
			}
		}
		$idcard = $idcard . self::idcard_verify_number ( $idcard );
		return $idcard;
	}
	
	// 18位身份证校验码有效性检查
	function idcard_checksum18($idcard) {
		if (strlen ( $idcard ) != 18) {
			return false;
		}
		$idcard_base = substr ( $idcard, 0, 17 );
		if (self::idcard_verify_number ( $idcard_base ) != strtoupper ( substr ( $idcard, 17, 1 ) )) {
			return false;
		} else {
			return true;
		}
	}
	
	//香港身份证号
	function hkidcard($idcard) {
		$firstStr = substr ( $idcard, 0, 1 );
		$middleStr = substr ( $idcard, 1, - 3 );
		$length = strlen ( $middleStr );
		$rightSecondStr = substr ( $idcard, - 2, 1 );
		$left = substr ( $idcard, - 3, 1 );
		$right = substr ( $idcard, - 1, 1 );
		$ord_firstStr = ord ( $firstStr );
		$ord_rightSecondStr = ord ( $rightSecondStr );
		$ord_left = ord ( $left );
		$ord_right = ord ( $right );
		if (($ord_firstStr > 90) || ($ord_firstStr < 65)) {
			return false;
		} else if (($ord_left != 40) or ($ord_right != 41)) {
			return false;
		} else if ($ord_rightSecondStr < 48 || $ord_rightSecondStr > 57) {
			return false;
		} else if (! is_numeric ( $middleStr )) {
			return false;
		} else if ($length != 6) {
			return false;
		} else {
			return true;
		}
	}
	
	//验证组织机构代码证
	public static function checkOrgCode($str) {
		$pattern = "/^[A-Za-z0-9]{8}-[A-Za-z0-9]{1}/"; //组织机构代码，8位数字或字母加上一个"-"再加一位数字或字母
		if (preg_match ( $pattern, $str )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 时间差转为X天X小时X分X秒等形式
	 * @param $int $intervalTime
	 * @param $accuracy  day精确到天 hour精确到小时 minute精确到分 second精确到秒,max精确在最大一个有数据的值
	 */
	public static function intervalTime2str($intervalTime, $accuracy = "hour") {
		$intervalTime = $intervalTime > 0 ? $intervalTime : 0;
		$day = floor ( $intervalTime / 86400 );
		$hour = floor ( ($intervalTime - 86400 * $day) / 3600 );
		$minute = floor ( (($intervalTime - 86400 * $day) - 3600 * $hour) / 60 );
		$second = floor ( (($intervalTime - 86400 * $day) - 3600 * $hour) - 60 * $minute );
		$str = "";
		$s_day = ($day > 0) ? $day . "天" : "";
		$s_hour = ($hour > 0) ? $hour . "小时" : "";
		$s_minute = ($minute > 0) ? $minute . "分" : "";
		$s_second = ($second > 0) ? $second . "秒" : "";
		if ($accuracy == "day") {
			return $s_day;
		}
		if ($accuracy == "hour") {
			return $s_day . $s_hour;
		}
		if ($accuracy == "minute") {
			return $s_day . $s_hour . $s_minute;
		}
		if ($accuracy == "second") {
			return $s_day . $s_hour . $s_minute . $s_second;
		}
		if ($accuracy == "max") {
			if ($s_day != "")
				return $s_day;
			if ($s_hour != "")
				return $s_hour;
			if ($s_minute != "")
				return $s_minute;
			if ($s_second != "")
				return $s_second;
		}
	}
	
	function isNumber($val) {
		if (preg_match ( "/^[0-9]+$/", $val ))
			return true;
		return false;
	}
	function isPhone($val) {
		if (preg_match ( "/^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/", $val ))
			return true;
		return false;
	}
	function isPostcode($val) {
		if (preg_match ( "/^[0-9]{4,6}$/", $val ))
			return true;
		return false;
	}
	
	public static function isMoney($val) {
		if (preg_match ( "/^[0-9]{1,}$/", $val ))
			return true;
		if (preg_match ( "/^[0-9]{1,}\.[0-9]{1,2}$/", $val ))
			return true;
		return false;
	}
	
	public static function isIp($val) {
		return ( bool ) ip2long ( $val );
	}
	
	/**
	 * 去掉UBB标签,返回指定长度字符
	 */
	public function getUbbStr($string, $strlen) {
		$string = str_replace ( "\n", "", $string );
		$string = str_replace ( "\r", "", $string );
		$string = preg_replace ( "/\[.*\](.*)\[.*\]/i", "$1", $string );
		return self::cutstr ( $string, $strlen ) . '...';
	}
	
	/**
	 * 验证验证码
	 */
	public function checkCatcha($s) {
		session_start ();
		$oSC = new SCaptcha ();
		return $oSC->check ( $s );
	}
}
