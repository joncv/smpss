<?php
/**
 * 销售管理
 * @author 齐迹  email:smpssadmin@gmail.com
 *
 */
class c_sale extends base_c {
	function __construct($inPath) {
		parent::__construct ();
		if (self::isLogin () === false) {
			$this->ShowMsg ( "请先登录！", $this->createUrl ( "/main/index" ) );
		}
		if (self::checkRights ( $inPath ) === false) {
			//$this->ShowMsg("您无权操作！",$this->createUrl("/system/index"));
		}
	}
	
	function pageindex($inPath) {
		$url = $this->getUrlParams ( $inPath );
		return $this->render ( 'sale/index.html', $this->params );
	}
	
}