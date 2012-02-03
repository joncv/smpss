<?php
/**
 * 会员主表数据模型
 * @author 齐迹  email:smpssadmin@gmail.com
 */
class m_member extends base_m{
	public function primarykey(){
		return 'mid';
	}
	public function tableName(){
		return base_Constant::TABLE_PREFIX.'member';
	}
	public function relations(){
		return array();
	}
	public function create($data){
		if(!$data['realname'] and !$data['membercardid']){
			$this->setError(0,"缺少必要参数");
			return false;
		}
		if(!$data['mid']) {
			if($this->get("membercardid = '{$data['membercardid']}'")){
				$this->setError(0,"会员卡卡号重复");
				return false;
			}
			$this->set('lastdateline',0);
		}
		if(is_array($data)) {
			foreach($data as $key=>$val) {
				if($this->set($key, $val) === false) {
					$this->setError(0,"设置字段{$key}失败 ".$this->getError());
					return false;
				}
			}
		}
		$regionObj = new m_region();
		if($data['prov_id']){
			$rs = $regionObj->get("region_id = {$data['prov_id']}",'region_name');
			$this->set('prov_name',$rs['region_name']);
		}
		if($data['city_id']){
			$rs = $regionObj->get("region_id = {$data['city_id']}",'region_name');
			$this->set('city_name',$rs['region_name']);
		}
		$this->set('regdateymd',date('Y-m-d',$this->_time));
		$this->set('regdateline',$this->_time);
		$rs = $this->save($data['mid']);
		if($rs) return $rs;
		$this->setError(0,"保存数据失败".$this->getError());
		return false;
	}
}