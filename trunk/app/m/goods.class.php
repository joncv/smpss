<?php
/**
 * 商品表数据模型
 * @author 齐迹  email:smpssadmin@gmail.com
 */
class m_goods extends base_m {
	public function primarykey() {
		return 'goods_id';
	}
	public function tableName() {
		return base_Constant::TABLE_PREFIX . 'goods';
	}
	public function relations() {
		return array ();
	}
	
	public function getGoodsList($condition=''){
		$goodsTableName = $this->tableName();
		$cateTableName = base_Constant::TABLE_PREFIX . 'category';
		$rs = $this->select($condition,"{$goodsTableName}.*,{$cateTableName}.cat_name","","",array("{$cateTableName}"=>"{$goodsTableName}.cat_id={$cateTableName}.cat_id"))->items;
		if($rs) return $rs;
		return array();
	}
	
	public function create($data) {
		if (! $data ['goods_name'] or ! $data ['goods_sn'] or ! $data ['in_price'] or ! $data ['out_price']) {
			$this->setError ( 0, "缺少必要参数" );
			return false;
		}
		$snRs = $this->get("goods_sn='{$data ['goods_sn']}'",'goods_id');
		if($snRs){
			$this->setError ( 0, "条形码重复" );
			return false;
		}
		$data ['market_price'] = $data ['market_price'] ? $data ['market_price'] : $data ['out_price'] * 1.2;
		$this->set ( "cat_id", (int)$data ['cat_id'] );
		$this->set ( "goods_name", $data ['goods_name'] );
		$this->set ( "goods_sn", $data ['goods_sn'] );
		$this->set ( "weight", $data ['weight'] );
		$this->set ( "unit", $data ['unit'] );
		$this->set ( "warn_stock", (int)$data ['warn_stock'] );
		$this->set ( "in_price", $data ['in_price'] );
		$this->set ( "out_price", $data ['out_price'] );
		$this->set ( "market_price", $data ['market_price'] );
		$this->set ( "promote_price", $data ['promote_price'] );
		$this->set ( "ispromote", $data ['ispromote'] );
		$this->set ( "promote_start_date", $data ['promote_start_date'] );
		$this->set ( "promote_end_date", $data ['promote_end_date'] );
		$this->set ( "ismemberprice", $data ['ismemberprice'] );
		$this->set ( "creatymd", date('Y-m-d',$this->_time) );
		$this->set ( "creatdateline", $this->_time );
		$rs = $this->save ( $data ['goods_id'] );
		if ($rs)
			return $rs;
		$this->setError ( 0, "保存数据失败" . $this->getError () );
		return false;
	}
}