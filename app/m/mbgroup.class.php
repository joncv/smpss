<?php
/**
 * 会员组主表数据模型
 * @author 齐迹  email:smpss2012@gmail.com
 */
class m_mbgroup extends base_m {
	public function primarykey() {
		return 'mgid';
	}
	public function tableName() {
		return base_Constant::TABLE_PREFIX . 'mbgroup';
	}
	public function relations() {
		return array ();
	}
	public function getOne($mgid) {
		$cacheName = "member_group_" . $mgid;
		$cache = SCache::getCacheEngine ( 'file' );
		$cache->init ( array ("dir" => SlightPHP::$appDir . "/cache", "depth" => 3 ) );
		$rs = unserialize ( $cache->get ( $cacheName ) );
		if (! $rs) {
			$this->setPkid ( $mgid );
			$rs = $this->get ();
			$cache->set ( $cacheName, serialize ( $rs ) );
		}
		return $rs;
	}
}