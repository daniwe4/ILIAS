<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\SignatureList;

class Printer
{
	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	/**
	 * SignatureList constructor.
	 * @param \ilLanguage $lng
	 */
	public function __construct(\ilLanguage $lng)
	{
		$this->lng = $lng;
		$this->lng->loadLanguageModule('trac');
	}

	public function printListFor(Document $document)
	{
		$ref_id = array_shift(\ilObject::_getAllReferences($document->getCrsId()));
		/** @var \ilObjCourse $crs */
		$crs = new \ilObjCourse((int)$ref_id);

		/** @var \ilCourseMemberPlugin $xcmb */
		$xcmb = \ilPluginAdmin::getPluginObjectById('xcmb');
		$list = $xcmb->initAttendanceListFor($crs, $document->getTemplateId());
		$member_data = $this->getPrintMemberData($crs);
		$list->addNonMemberUserData($member_data);
		$list->getFullscreenHTML($member_data);
		exit();
	}

	/**
	 * Function is copied from the original AttendenceList printing mechanism
	 * see Modules/Course/classes/class.ilCourseMembershipGUI.php
	 */
	protected function getPrintMemberData(\ilObjCourse $crs)
	{
		$crs_id = $crs->getId();
		$is_admin = true;
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = \ilPrivacySettings::_getInstance();

		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = \ilLearningProgress::_lookupProgressByObjId($crs_id);
		}

		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		$show_tracking =
			(\ilObjUserTracking::_enabledLearningProgress() and \ilObjUserTracking::_enabledUserRelatedData());
		if($show_tracking)
		{
			include_once('./Services/Object/classes/class.ilObjectLP.php');
			$olp = \ilObjectLP::getInstance($crs_id);
			$show_tracking = $olp->isActive();
		}

		if($show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = \ilLPStatusWrapper::_lookupCompletedForObject($crs_id);
			$in_progress = \ilLPStatusWrapper::_lookupInProgressForObject($crs_id);
			$failed = \ilLPStatusWrapper::_lookupFailedForObject($crs_id);
		}

		$members_object = $crs->getMembersObject();
		$members = $members_object->getParticipants();
		$print_member = [];
		$profile_data = \ilObjUser::_readUsersProfileData($members);

		// course defined fields
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
		$cdfs = \ilCourseUserData::_getValuesByObjId($crs_id);

		foreach($members as $member_id)
		{
			// GET USER OBJ
			if($tmp_obj = \ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				// udf
				include_once './Services/User/classes/class.ilUserDefinedData.php';
				$udf_data = new \ilUserDefinedData($member_id);
				foreach($udf_data->getAll() as $field => $value)
				{
					list($f,$field_id) = explode('_', $field);
					$print_member[$member_id]['udf_'.$field_id] = (string) $value;
				}

				foreach((array) $cdfs[$member_id] as $cdf_field => $cdf_value)
				{
					$print_member[$member_id]['cdf_'.$cdf_field] = (string) $cdf_value;
				}

				foreach((array) $profile_data[$member_id] as $field => $value)
				{
					$print_member[$member_id][$field] = $value;
				}

				$print_member[$member_id]['login'] = $tmp_obj->getLogin();
				$print_member[$member_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();

				if($members_object->isAdmin($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_admin");
				}
				elseif($members_object->isTutor($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_tutor");
				}
				elseif($members_object->isMember($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_member");
				}
				if($members_object->isAdmin($member_id) or $members_object->isTutor($member_id))
				{
					if($members_object->isNotificationEnabled($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_notify");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_no_notify");
					}
				}
				else
				{
					if($members_object->isBlocked($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_blocked");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_unblocked");
					}
				}

				if($is_admin)
				{
					$print_member[$member_id]['passed'] = $members_object->hasPassed($member_id) ?
						$this->lng->txt('crs_member_passed') :
						$this->lng->txt('crs_member_not_passed');

				}
				if($privacy->enabledCourseAccessTimes())
				{
					if(isset($progress[$member_id]['ts']) and $progress[$member_id]['ts'])
					{
						\ilDatePresentation::setUseRelativeDates(false);
						$print_member[$member_id]['access'] = \ilDatePresentation::formatDate(new \ilDateTime($progress[$member_id]['ts'],IL_CAL_UNIX));
						\ilDatePresentation::setUseRelativeDates(true);
					}
					else
					{
						$print_member[$member_id]['access'] = $this->lng->txt('no_date');
					}
				}
				if($show_tracking)
				{
					if(in_array($member_id,$completed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(\ilLPStatus::LP_STATUS_COMPLETED);
					}
					elseif(in_array($member_id,$in_progress))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(\ilLPStatus::LP_STATUS_IN_PROGRESS);
					}
					elseif(in_array($member_id,$failed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(\ilLPStatus::LP_STATUS_FAILED);
					}
					else
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(\ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
					}
				}

			}

			// cat-tms-patch start #3295
			$print_member[$member_id]["org_units"] = \ilObjUser::lookupOrgUnitsRepresentation($member_id);
			// cat-tms-patch end
		}
		return \ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order'], false, true);
	}
}