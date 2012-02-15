<?php
/**
 * 通用异步请求控制器
 * @author 齐迹  email:smpss2012@gmail.com
 */
class c_ajax extends base_c {
	
	public function pagegetregion() {
		$aJson = array ();
		$regionObj = new m_region ();
		$iPid = ( int ) $_REQUEST ['parent_id'];
		$exce = ( int ) $_REQUEST ['exce'];
		$aRegions = $regionObj->select ( array ("parent_id" => $iPid ), '*', '', 'order by region_id asc' )->items;
		if (empty ( $iPid )) {
			echo json_encode ( array () );
			exit ();
		}
		if ($aRegions) {
			foreach ( $aRegions as $aRegion ) {
				$r = array ('region_id' => $aRegion ['region_id'], 'region_name' => $aRegion ['region_name'] );
				$aJson [] = $r;
			}
			echo json_encode ( $aJson );
		} else {
			if ($exce) {
				$aRegions = $regionObj->select ( array ("parent_id" => $iPid ), '*', '', 'order by region_id asc' )->items;
				if (! empty ( $aRegions )) {
					$r = array ('region_id' => $aRegions ['region_id'], 'region_name' => $aRegions ['region_name'] );
					echo json_encode ( array ($r ) );
				} else {
					echo json_encode ( array () );
				}
			} else {
				echo json_encode ( array () );
			}
		}
	}

}