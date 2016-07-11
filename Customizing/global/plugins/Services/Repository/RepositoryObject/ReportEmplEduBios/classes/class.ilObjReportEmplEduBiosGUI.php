<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/ReportEduBio/classes/class.ilObjReportEduBio.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEmplEduBiosGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEmplEduBiosGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
* @ilCtrl_Calls ilObjReportEmplEduBiosGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEmplEduBiosGUI extends ilObjReportBaseGUI {
	protected $relevant_parameters = array();
	protected static $od_regexp;
	protected static $bd_regexp;
	public function getType() {
		return 'xeeb';
	}

	protected function afterConstructor() {
		parent::afterConstructor();
		require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
		} else {
			$date = '-';
		}
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}

		$rec = self::getODBD($rec);

		$rec["edu_bio_link"] = ilObjReportEduBio::getEduBioLinkFor($rec["user_id"]);
		
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = ilDatePresentation::formatPeriod($start,$end) ;
		} else {
			$date = '-';
		}
		if ($rec['cert_period'] != "-") {
			$rec['cert_period'] = ilDatePresentation::formatDate(new ilDate($rec['cert_period'], IL_CAL_DATE));
		}

		$rec = self::getODBD($rec);

		return parent::transformResultRowXLSX($rec);
	}

	protected static function getODBD($rec) {
		$orgus_above = array_unique(array_merge(explode(';;', $rec['org_unit_above1']), explode(';;', $rec['org_unit_above2'])));
		$od = array_filter($orgus_above, "self::filterOD");
		$bd = array_filter($orgus_above, "self::filterBD");
		$rec["od_bd"] = (count($od) > 0 ? implode(',', $od) : '-').'/'.(count($bd) > 0 ? implode(',', $bd) : '-');
		return $rec;
	}

	protected static function filterOD($orgu_title) {
		return preg_match(self::$od_regexp, $orgu_title) === 1;
	}

	protected static function filterBD($orgu_title) {
		return preg_match(self::$bd_regexp, $orgu_title) === 1;
	}
}