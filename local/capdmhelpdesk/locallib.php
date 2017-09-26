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
 * Internal library of functions for module capdmhelpdesk
 *
 * All the capdmhelpdesk specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    local
 * @subpackage capdmhelpdesk
 * @copyright  2017 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

        /**
	*	This function gets a list of courses a user is enrolled on a for the specified role.
	*
	*	@param $userid = id of a moodle user.
        *	@param $roleid = role type id (3 = Teacher).
        *
	*	@return array(obj)
	*/
	function capdmhelpdesk_get_user_enrolments_role($userid, $roleid = 3){

            global $DB;

            $courses = $DB->get_records_sql('select instanceid as id, crse.fullname from {role_assignments} ra inner join {context} ctx on ra.contextid = ctx.id
                                            inner join {course} crse on instanceid = crse.id where userid = :userid and roleid <= :role', array('userid'=>$userid, 'role'=>$roleid));

            return $courses;
        }

        /**
	*	This function gets a list of users enrolled on a course for the specified role (or all if no role provided)
        *       or depending on the roleid comparison.
	*
	*	@param $courseid = id of a moodle course.
        *	@param $roleid = role type id (0 = all roles).
        *       @param $rolecomp = comparison operator to use when matching the type of role to be returned.
        *
	*	@return array(obj)
	*/
	function capdmhelpdesk_get_course_users($courseid, $roleid = 99, $rolecomp = '<='){

            global $DB;

            $users = $DB->get_records_sql('SELECT u.id, ra.roleid, u.username, u.firstname, u.lastname, u.email
                                            FROM {course} crse
                                            JOIN (SELECT DISTINCT e.courseid, ue.userid FROM {enrol} e
                                            JOIN {user_enrolments} ue ON (ue.enrolid = e.id) ) en
                                            ON (en.courseid = crse.id)
                                            LEFT JOIN {context} ctx ON (ctx.instanceid = crse.id AND ctx.contextlevel = 50)
                                            LEFT JOIN {role_assignments} ra ON (ctx.id = ra.contextid AND en.userid = ra.userid)
                                            LEFT JOIN {user} u on en.userid = u.id
                                            WHERE crse.id = :courseid and roleid '.$rolecomp.' :roleid ORDER BY u.lastname, u.firstname ASC', array('courseid'=>$courseid, 'roleid'=>$roleid));

            return $users;

        }

        /**
	*	This function gets a list of users defined as admins for a helpdesk category.
	*
	*	@param $catid = id of the CAPDMHELPDESK category.
        *
	*	@return array(obj) (should only be a single record).
	*/
	function capdmhelpdesk_get_category_admins($catid){

            global $DB;

            $users = $DB->get_records_sql('select u.id, u.firstname, u.lastname, u.email from {capdmhelpdesk_cat} cat inner join {user} u on cat.cat_userid = u.id
                                            where cat.id = :catid', array('catid'=>$catid));

            return $users;

        }

	/**
	*	This function sends notification emails to relevant users on the submission, update or otherwise of a ticket
	*
	*	@param $objRecipient = an object of info to use for sending the message.
        *       @param $msgID = string value of the type of message to send.
        *       @param = category
	*	@return string
	*/
	function capdmhelpdesk_send_notification($objRecipient, $msgID, $category = 0){

		global $DB, $USER, $CFG, $SITE;

		$output = '';
                $admins = array();


//                $enquirer = new stdClass();
//                $enquirer->email = $objRecipient->email;
//                $enquirer->firstname = $objRecipient->firstname;
//                $enquirer->lastname = $objRecipient->lastname;
//                $enquirer->username = 'admin from the code';
//                $enquirer->id = 2;	// junk value to get the email notification to work

                // Set the sender to the core_user::get_support_user.
                $sender = core_user::get_support_user();

                // Set boolean for notifying admins to false and user to true by default.
                $notify_admins = false;
                $notify_user = true;
                $siteURL = (string)new moodle_url('/');

                switch($msgID){
                    case 'new':
                        $subject = get_string('helpdesk_new_subject_user','local_capdmhelpdesk', array('site'=>$SITE->fullname));
                        $msg = get_string('helpdesk_new_message_thanks','local_capdmhelpdesk', array('fname'=>$objRecipient->firstname, 'site'=>$SITE->fullname));
                        $msg_html = get_string('helpdesk_new_message_thanks_html','local_capdmhelpdesk', array('fname'=>$objRecipient->firstname, 'site'=>$SITE->fullname, 'sitewww'=>$siteURL));
                        // Set admins message.
                        $subject_admins = get_string('helpdesk_new_subject_admin','local_capdmhelpdesk', array('site'=>$SITE->fullname));
                        $msg_admins = get_string('helpdesk_new_message_admin','local_capdmhelpdesk', array('sender'=>$objRecipient->firstname.' '.$objRecipient->lastname, 'site'=>$SITE->fullname, 'subject'=>$objRecipient->subject, 'newmsgid'=>$objRecipient->newmsgid, 'sitewww'=>$siteURL));
                        $msg_admins_html = get_string('helpdesk_new_message_admin_html','local_capdmhelpdesk', array('sender'=>$objRecipient->firstname.' '.$objRecipient->lastname, 'site'=>$SITE->fullname, 'subject'=>$objRecipient->subject, 'newmsgid'=>$objRecipient->newmsgid, 'sitewww'=>$siteURL));
                        // As this is a new message presumably from a student then need to notify admins also.
                        $notify_admins = true;
                        break;
                    case 'reply':
                        $subject = get_string('helpdesk_reply_subject_user','local_capdmhelpdesk', array('site'=>$SITE->fullname));
                        $msg = get_string('helpdesk_reply_message_user','local_capdmhelpdesk', array('fname'=>$objRecipient->firstname, 'site'=>$SITE->fullname, 'subject'=>$objRecipient->subject));
                        break;
                    case 'replyadmin':
                        $subject_admins = get_string('helpdesk_reply_subject_admin','local_capdmhelpdesk', array('site'=>$SITE->fullname));
                        $msg_admins = get_string('helpdesk_reply_message_admin','local_capdmhelpdesk', array('site'=>$SITE->fullname, 'subject'=>$objRecipient->subject, 'msgid'=>$objRecipient->msgid));
                        // Need to notify admins also.
                        $notify_admins = true;
                        $notify_user = false;
                        break;
                }

		// Send an acknowledgement/notification messages.
                // If need to notify the original user then send a message.
                if($notify_user){
                    email_to_user($objRecipient, $sender, $subject, $msg, $msg_html);
                }

                // If need to notify admins then do it to all.
                if($notify_admins){

                    if(is_numeric($category)){
                        // Get a list of users for this course who have role id 3 or less
                        // 3 = teacher (lower numbers = higher permissions in roles).
                        $admins = capdmhelpdesk_get_course_users($category, 3);
                    } else {
                        $admins = capdmhelpdesk_get_category_admins($category);
                    }

                    // If there are no admins defined then send to system support contact as a fallback.
                    if(!$admins){
                        $admins = get_admins();
                        $msg_admins .= get_string('helpdesk_no_admin_set','local_capdmhelpdesk');
                    }

                    foreach($admins as $admin){
                        email_to_user($admin, $sender, $subject_admins, $msg_admins, $msg_admins_html);
                    }
                }

		return true;
	}

	/**
	*	This function displays messages in a formatted box
	*
	*   @param $msg = message string
	*	@return string
	*/
	function capdmhelpdesk_message($msg, $type='alert-info'){

		$output ='';

                // set the bootstrap alert message style for this message

                $type = 'alert '.$type.' alert-block fade in';

		$output .= html_writer::start_tag('div', array('class'=>$type, 'role'=>'alert'));
                $output .= html_writer::tag('button', 'x', array('class'=>'close', 'data-dismiss'=>'alert'));
                $output .= $msg;
                $output .= html_writer::end_tag('div');

		return $output;

	}

	/**
	*	This function checks the supplied userid to see if they are defined as a capdmhelpdesk admin
	*
	*   @param $userID = username being checked
	*	@return boolean
	*/
	function capdmhelpdesk_is_admin($username){

		// admin will always get in!
		if($username == 'admin'){
			return true;
		} else {
			global $DB;

			// look up the tutor for the course selected - this could be more than one!
			$admins = $DB->get_field('capdmhelpdesk_config', 'content', array('type'=>'admins'));

			$allAdmins = explode('~', $admins);

			if(in_array($username, $allAdmins)){
				return true;
			}

			return false;
		}

	}
