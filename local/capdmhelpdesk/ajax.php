<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Interacts with the database to save/reset font size settings        (1)
 *
 * This file handles all the blocks database interaction. If saving,
 * it will check if the current user already has a saved setting, and
 * create/update it as appropriate. If resetting, it will delete the
 * user's setting from the database. If responding to AJAX, it responds
 * with suitable HTTP error codes. Otherwise, it sets a message to
 * display, and redirects the user back to where they came from.       (2)
 *
 * @package   block_accessibility                                      (3)
 * @copyright 2009 &copy; Taunton's College                            (4)
 * @author Mark Johnson <mark.johnson@taunton.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (5)
 */
define('AJAX_SCRIPT', true);

global $CFG, $DB;

require_once('../../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

defined('MOODLE_INTERNAL') || die();

//require_login();
isloggedin();

$op = required_param('op', PARAM_TEXT);
$value = optional_param('value', 0, PARAM_TEXT);

$isadmin = false;
$warning = '';

ob_start();
unset($arrResult);
$arrResult = array();


	switch ($op) {
	    case 'helpdesk':
		$result = $DB->get_records_sql("select t.id, subject, status, readflag, t.submitdate, FROM_UNIXTIME((t.submitdate), '%e %b %Y @ %H:%i') AS 'formatted_submitdate', updatedate, FROM_UNIXTIME((t.updatedate), '%e %b %Y @ %H:%i') AS 'formatted_updatedate',concat(u.firstname, ' ', u.lastname) as repliername from {capdmhelpdesk_tickets} t left join {capdmhelpdesk_replies} r on t.id = r.replyto left join {user} u on r.replierid = u.id where userid = :userid", array('userid'=>$uid));
		$res = json_encode($result);
		$arrResult['data'] = $res;
		$arrResult['result'] = 'success';
		break;
	    case 'helpdeskmsg':

		$params = json_decode($parameters);
                $helpdeskCat = false;

		switch($type){
			case 'new':
				$record = new stdClass();
				$record->userid = $params->userid;
				$record->category =$params->cat;
				$record->subject = $params->subject;
				$record->message = $params->msg;
				$record->submitdate = time();
				$record->updatedate = time();
				$record->updateby = $params->userid;
				$record->status = 0;
				$record->readflag = 1;	// default readflag to 1 = "read" as this is a new message so it is believed you have read it
				$record->params = 'aaaa';
				$res = $DB->insert_record('capdmhelpdesk_requests', $record);

				$newRecID = $res;
/*
				if($res){
					// get the orig user details so they can be included in the message
                                        $user = $DB->get_record('user', array('id'=>$params->userid));

                                        // send confirmation and notification messages
                                        $admin = get_admin();
                                        $subj = get_string('capdmuserhelpdesk_new_tutor_alert_subj','local_capdmuser', array('site'=>$CFG->wwwroot));

					$arrAdmins = array();
                                        $arrCourseAdmins = array();
                                        // get all helpdesk admins. The General enquires option will not find a course in mdl_course so will fail the check
					// so...only check for course ID's above 0
					if(is_numeric($params->cat_value)){
                                                $arrAdmins = capdmuser_get_user_enrolments($params->cat_value, 0, 3, 1);
					} else {
                                            // this will return a record as an object
                                            // will need to add this object to the $arrAdmins array
                                            $catUser = $DB->get_record_sql('select u.* from {user} u inner join {capdmhelpdesk_cat} hcat on u.id = hcat.cat_userid where hcat.id = ?', array('id'=>$params->cat_value));
                                            $arrAdmins[$arrCat[0]] = $catUser;
                                        }

                                        // if there are no admins defined for a course then fallback to the site admins
                                        if(!$arrAdmins){
                                            $arrAdmins = get_admins();
                                            $warning = get_string('helpdesk_no_admin_set','local_capdmuser');
                                        }

                                        // send a notification to all the admins - now in a single array of users
                                        foreach($arrAdmins as $helpdeskAdmin){
                                            $body = get_string('capdmuserhelpdesk_new_tutor_alert_body','local_capdmuser', array('subject'=>$params->subject,  'origuser'=>$user->firstname.' '.$user->lastname, 'site'=>$CFG->wwwroot, 'fullname'=>$helpdeskAdmin->firstname.' '.$helpdeskAdmin->lastname, 'newRecID'=>$newRecID)).$warning;
                                            $emailRes = email_to_user($helpdeskAdmin, $admin, $subj, $body);
                                        }

                                        // send confirmation to user
                                        $subj = get_string('capdmuserhelpdesk_new_user_alert_subj','local_capdmuser', array('site'=>$CFG->wwwroot));
                                        $body = get_string('capdmuserhelpdesk_new_user_alert_body','local_capdmuser', array('site'=>$CFG->wwwroot, 'fullname'=>$user->firstname.' '.$user->lastname));
                                        $emailRes = email_to_user($user, $admin, $subj, $body);
                                }
*/
				$arrResult['data'] = $res;
				$arrResult['result'] = 'success';

				break;
			case 'close':
				$record = new stdClass();
				$record->id = $params->msgid;
				$record->status = 1;

				$res = $DB->update_record('capdmhelpdesk_tickets', $record);

				$arrResult['result'] = 'success'.$params->msgid;

				break;
			case 'reply':
                                $record = new stdClass();
                                $record->userid = $params->userid;
                                $record->replyto = $params->msgid;
                                $record->message = $params->msg;
                                $record->submitdate = time();
                                $record->replierid = $params->userid;
                                $res = $DB->insert_record('capdmhelpdesk_replies', $record);

				// also need to update the orig record with an updated time and who by value
				$rec = new stdClass();
				$rec->id = $params->msgid;
				$rec->updatedate = time();
				$rec->updateby = $params->userid;
                                $rec->status = 0;   // set to open as if it is closed and reply is posted then it will auto open again!
				$res = $DB->update_record('capdmhelpdesk_tickets', $rec);

				$admin = get_admin();

				if($res){
					if($params->origuserid == $params->userid){
						// this is the original user adding a reply so just notify the admins
						// get all helpdesk admins
						$arrAdmins = array();
	                                        // get all helpdesk admins. The General enquires option will not find a course in mdl_course so will fail the check
	                                        // Only check for course ID's above 0 but set the arrAdmins first so it passes the check in the next if below
	                                        if($params->cat_value > 0){
	                                                //$arrAdmins = capdmuser_get_role_admins($params->cat_value, 'local/capdmuser:helpdeskadmin');
                                                        $arrAdmins = capdmuser_get_user_enrolments($params->cat_value, 0, 3, 1);
	                                        }

                                                // if there are no admins defined for a course then fallback to the site admins
                                                if(!$arrAdmins){
                                                    $arrAdmins = get_admins();
                                                    $warning = get_string('helpdesk_no_admin_set','local_capdmuser');
                                                }

						// need to look up the orig message
						$msg = $DB->get_record_sql('select t.id, t.subject, u.firstname, u.lastname from {capdmhelpdesk_tickets} t inner join {user} u on t.userid = u.id where t.id = :id', array('id'=>$params->msgid));
						$subj = get_string('helpdesk_reply_subject_tutor','local_capdmuser', array('site'=>$CFG->wwwroot));

						if($params->cat_value == 0 || sizeof($arrAdmins) == 0){ // General enquiry so contact the site admin
					               $body = get_string('helpdesk_reply_body','local_capdmuser', array('subject'=>$msg->subject,  'origuser'=>$msg->firstname.' '.$msg->lastname, 'site'=>$CFG->wwwroot, 'fullname'=>$admin->firstname.' '.$admin->lastname, 'newRecID'=>$params->msgid)).$warning;
        		                               $emailRes = email_to_user($admin, $admin, $subj, $body);
						} else {
		                                        foreach($arrAdmins as $helpdeskAdmin){
		                                                $body = get_string('helpdesk_reply_body','local_capdmuser', array('subject'=>$msg->subject,  'origuser'=>$msg->firstname.' '.$msg->lastname, 'site'=>$CFG->wwwroot, 'fullname'=>$helpdeskAdmin->firstname.' '.$helpdeskAdmin->lastname, 'newRecID'=>$params->msgid)).$warning;
        		                                        $emailRes = email_to_user($helpdeskAdmin, $admin, $subj, $body);
	        	                                }
						}
					} else {
	                                        // send confirmation to owner of the message to nofity them a reply has been posted
	                                        $user = $DB->get_record('user', array('id'=>$params->origuserid));
	                                        $subj = get_string('helpdesk_reply_subject_user','local_capdmuser', array('site'=>$CFG->wwwroot));
	                                        $body = get_string('helpdesk_reply_body_user','local_capdmuser', array('site'=>$CFG->wwwroot, 'fname'=>$user->firstname));
	                                        $emailRes = email_to_user($user, $admin, $subj, $body);
					}
                                }



                                $arrResult['data'] = $res;
                                $arrResult['result'] = 'success';
                                break;
			case 'ticket':
//				$result = $DB->get_record('capdmhelpdesk_tickets', array('id'=>$params->messageID));
				$result = $DB->get_record_sql('select t.id, t.userid, u.firstname, u.lastname, t.category, crse.fullname, t.subject, t.message, t.submitdate, FROM_UNIXTIME((submitdate), \'%e %b %Y @ %H:%i:%s\') AS \'formatted_submitdate\', t.updatedate, FROM_UNIXTIME((updatedate), \'%e %b %Y @ %H:%i:%s\') AS \'formatted_updatedate\', t.updateby, concat(u2.firstname, \' \' , u2.lastname) as updatedbyname, case status when 0 then \'Open\' when 1 then \'Closed\' end as statusdesc, t.status, t.readflag from {capdmhelpdesk_tickets} t inner join {user} u on t.userid = u.id inner join {user} as u2 on t.updateby = u2.id inner join (select id, name as fullname from {capdmhelpdesk_cat} union select id, fullname from {course}) crse on t.category = crse.id where t.id = :id', array('id'=>$params->messageID));
		                $res = json_encode($result);
		                $arrResult['data'] = $res;
				if($result){
			                $arrResult['result'] = 'success';
				} else {
					$arrResult['result'] = 'norecords';
				}
				break;
			case 'replies':
				$result = $DB->get_records_sql('select r.message, r.submitdate, u.firstname, u.lastname from {capdmhelpdesk_replies} r left join {user} u on r.replierid = u.id where replyto = ?', array('replyto'=>$params->messageID));
                                $res = json_encode($result);
                                $arrResult['data'] = $res;
                                if($result){
                                        $arrResult['result'] = 'success';
                                } else {
                                        $arrResult['result'] = 'norecords';
                                }
				break;
			case 'unread':
				$rec = new stdClass();
				$rec->id = $params->id;
				$rec->readflag = 0;
				$result = $DB->update_record('capdmhelpdesk_tickets', $rec, false);
				if($result){
					$arrResult['result'] = 'success'.$params->messageID;
				} else {
					$arrResult['result'] = 'update error';
				}
				break;
		}
		break;

	    default:
		$arrResult['result'] = '403';
		break;
	}
echo json_encode($arrResult);
ob_end_flush();
