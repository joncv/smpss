<?php
/**
 * 转换ecshop数据插件
 * @author 齐迹  email:smpss2012@gmail.com
 */
class m_ecshop {
	public $_db;
	protected $_time;
	protected $_error = array ();
	
	function __construct() {
		$this->_dbConfig = SDb::getConfig ( "ecshop" );
		$this->_db = SDb::getDbEngine ( "pdo_mysql" );
		$this->_db->init ( $this->_dbConfig );
		$this->_time = time ();
	}
	
	public function __destruct() {
		$this->_db = null;
	}
	
	/**
	 * 写入错误信息
	 * @param int $code
	 * @param string $msg
	 */
	protected function setError($code = 0, $msg = "") {
		$this->_error ["code"] = $code;
		$this->_error ["msg"] = $msg;
	}
	/**
	 * 获取错误信息
	 * @param string $type
	 */
	public function getError($type = "msg") {
		return $this->_error [$type];
	}
}