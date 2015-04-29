<?php

/**
 * Reconstructs EduBiographies for old courses.
 */
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Tracking/classes/class.ilLPStatus.php");

class spxBuildHisto {
	static public function run() {
		ini_set("memory_limit","2048M"); 
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		global $ilDB;
		global $ilAppEventHandler;
		
		echo "<pre>";
		
		$course_res = $ilDB->query("SELECT od.obj_id "
								  ."  FROM object_data od"
								  ."  JOIN object_reference oref"
								  ."    ON od.obj_id = oref.obj_id"
								  ." WHERE od.type = 'crs'"
								  ."   AND oref.deleted IS NULL"
								  );
		
		while ($crs_rec = $ilDB->fetchAssoc($course_res)) {
			$crs_id = $crs_rec["obj_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$crs_members_object = $crs_utils->getCourse()->getMembersObject();
			echo "Building historizing entries for course $crs_id...\n";
			
			$participants = $crs_utils->getParticipants();
			foreach ($participants as $participant) {
				if ($crs_utils->getBookingStatusOf($participant) === null) {
					$crs_members_object->delete($participant);
					if (!$crs_utils->getBookings()->bookCourse($participant)) {
						echo "    Could not book user $participant\n";
					}
					else {
						echo "    Booked user $participant\n";
					}
				}
				else {
					echo "   $participant already booked.\n";
				}
			}

			// Book users where membership was lost somehow...
			$hist_res = $ilDB->query("SELECT DISTINCT usr_id"
									."  FROM hist_usercoursestatus"
									." WHERE hist_historic = 0"
									."   AND crs_id = ".$ilDB->quote($crs_id, "integer")
									."   AND booking_status = '-empty-'"
									."   AND function NOT IN ('crs_admin', 'crs_tutor')"
									);
			
			while ($hist_rec = $ilDB->fetchAssoc($hist_res)) {
				if (!$crs_util->getBookings()->bookCourse($hist_rec["usr_id"])) {
					echo "    Could not book user ".$hist_rec["usr_id"]."\n";
				}
				else {
					echo "    Booked user ".$hist_rec["usr_id"]."\n";
				}
			}

			// purge existing history and participation status information
			$ilDB->manipulate("DELETE FROM hist_usercoursestatus WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));
			$ilDB->manipulate("DELETE FROM crs_pstatus_usr WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));

			$participants = $crs_utils->getParticipants();
			foreach ($participants as $participant) {
				// Fake Tracking event to create participation status
				$params = array
					( "obj_id" => $crs_id
					, "usr_id" => $participant
					, "status" => ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
					, "evil_hack" => true
					);
				$ilAppEventHandler->raise("Services/Tracking", "updateStatus", $params);
				
				// Fake Tracking event to create participation status
				$params = array
					( "obj_id" => $crs_id
					, "usr_id" => $participant
					, "status" => ilLPStatus::_lookupStatus($crs_id, $participant)
					, "evil_hack" => true
					);
				$ilAppEventHandler->raise("Services/Tracking", "updateStatus", $params);
			}
		}
		
		// reinsert certificates...
		$cert_res = $ilDB->query("SELECT DISTINCT usr_id, crs_id, certificate"
								."  FROM hist_usercoursestatus"
								." WHERE certificate != -1 AND certificate != 0"
								);
		while($rec = $ilDB->fetchAssoc($cert_res)) {
			$ilDB->manipulate("UPDATE hist_usercoursestatus"
							 ."   SET certificate = ".$ilDB->quote($rec["certificate"], "integer")
							 ." WHERE hist_historic = 0 "
							 ."   AND usr_id = ".$ilDB->quote($rec["usr_id"], "integer")
							 ."   AND crs_id = ".$ilDB->quote($rec["crs_id"], "integer")
							 );
		}
		
		echo "</pre>";
	}
}

?>