<?php
/**
 * 管理员主表数据模型
 */
class m_group extends base_m{
	public function primarykey(){
		return 'gid';
	}
	public function tableName(){
		return base_Constant::TABLE_PREFIX.'group';
	}
	public function relations(){
		return array();
	}
}