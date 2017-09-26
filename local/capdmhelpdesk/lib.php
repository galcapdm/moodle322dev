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
 * Library of interface functions and constants for module capdmhelpdesk
 *
 * All the core Moodle functions, neeeded to allow the plugin to work
 * integrated in Moodle should be placed here.
 * All the capdmhelpdesk specific functions, needed to implement all the plugin
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    local_capdmhelpdesk
 * @copyright  2013 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('capdmhelpdesk_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function capdmhelpdesk_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_BACKUP_MOODLE2:    return true;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the capdmhelpdesk into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $capdmhelpdesk An object from the form in mod_form.php
 * @param mod_capdmhelpdesk_mod_form $mform
 * @return int The id of the newly inserted capdmhelpdesk record
 */
function capdmhelpdesk_add_instance(stdClass $capdmhelpdesk, mod_capdmhelpdesk_mod_form $mform = null) {
    global $DB;

    $capdmhelpdesk->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('capdmhelpdesk', $capdmhelpdesk);
}

/**
 * Updates an instance of the capdmhelpdesk in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $capdmhelpdesk An object from the form in mod_form.php
 * @param mod_capdmhelpdesk_mod_form $mform
 * @return boolean Success/Fail
 */
function capdmhelpdesk_update_instance(stdClass $capdmhelpdesk, mod_capdmhelpdesk_mod_form $mform = null) {
    global $DB;

    $capdmhelpdesk->timemodified = time();
    $capdmhelpdesk->id = $capdmhelpdesk->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('capdmhelpdesk', $capdmhelpdesk);
}

/**
 * Removes an instance of the capdmhelpdesk from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function capdmhelpdesk_delete_instance($id) {
    global $DB;

    if (! $capdmhelpdesk = $DB->get_record('capdmhelpdesk', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('capdmhelpdesk', array('id' => $capdmhelpdesk->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function capdmhelpdesk_user_outline($course, $user, $mod, $capdmhelpdesk) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $capdmhelpdesk the module instance record
 * @return void, is supposed to echp directly
 */
function capdmhelpdesk_user_complete($course, $user, $mod, $capdmhelpdesk) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in capdmhelpdesk activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function capdmhelpdesk_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link capdmhelpdesk_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function capdmhelpdesk_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see capdmhelpdesk_get_recent_mod_activity()}

 * @return void
 */
function capdmhelpdesk_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function local_capdmhelpdesk_cron () {

        global $DB;

        $sql = 'update {capdmhelpdesk_requests} set status = 1 where (unix_timestamp() - case updatedate when 0 then submitdate else updatedate end) > 86400 and status = -1';
        $ret = $DB->execute($sql);

	return true;
/*
	global $DB, $USER;

	// Turn this on to send email messages
	define("SEND_EMAIL", FALSE);

	//first, check to see if there are any scheduled prompts needing to be run
	mtrace(get_string('capdmhelpdesk_check_for_prompts','local_capdmhelpdesk'));

	// set a value for the current time and use this throughout
	$runtime = time();

	try{
		$prompts = $DB->get_records_sql('select p.id, p.name, p.sqlstatement, p.freq, p.period, p.repeatjob, p.msgid, p.lastrun, m.subject, m.message from mdl_capdmhelpdesk p inner join mdl_capdmhelpdesk_message m on p.msgid = m.id');
		if($prompts){
			foreach($prompts as $p){
				// find out if this is a days prompt or a months prompt
				switch($p->period){
					case 'd':
							$period = 'day';
							break;
					case 'm':
							$period = 'month';
							break;
				}

				// Now see if there are any registered users who fall in to this category
//				$users = $DB->get_records_sql('select u.id, u.lastname, u.firstname, u.username, u.email, timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) as days_since_last_login, u.lastlogin from mdl_user u left join mdl_capdmhelpdesk_selected sel on u.username = sel.username where u.lastlogin > 0 and timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) >= '.$p->freq.' and sel.username is null');

//				$users = $DB->get_records_sql('select u.id, u.lastname, u.firstname, u.username, u.email, u.mailformat, timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) as days_since_last_login, u.lastlogin from mdl_user u where u.lastlogin > 0 and timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) >= '.$p->freq);

//				mtrace('... SQL statement used - select u.id, u.lastname, u.firstname, u.username, u.email, timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) as days_since_last_login, u.lastlogin from mdl_user u where u.lastlogin > 0 and timestampdiff('.$period.', from_unixtime(u.lastlogin), now()) >= '.$p->freq);

				$sqlstatement = str_replace('~~PERIOD~~', $period, $p->sqlstatement);
				$sqlstatement = str_replace('~~FREQ~~', $p->freq, $sqlstatement);
				mtrace(get_string('capdmhelpdesk_sql_statement_for','local_capdmhelpdesk').$p->id." - ".$sqlstatement);
				$users = $DB->get_records_sql($sqlstatement);

				// if ther are any then add them to the capdmhelpdesk_selected table so they can be reported on
				mtrace('number of records chosen - '.sizeof($users));
				if($users){
					foreach($users as $u){
						$record = new stdClass();
						$record->userid = $u->userid;
						$record->username = $u->username;
						$record->email = $u->email;
						$record->lastlogin = $u->lastlogin;
						$record->rundate = $runtime;
						$record->period = $p->period;
						if(capdmhelpdesk_in_object('idnumber', $u)){
							$record->prompt_id = $p->id.'~~'.$u->idnumber;
							$record->course_id = $u->courseid;
						} else {
							$record->prompt_id = $p->id;
						}

						$record->msgid = $p->msgid;
						try{
							$res = $DB->insert_record('capdmhelpdesk_selected', $record);
							mtrace(get_string('capdmhelpdesk_added','local_capdmhelpdesk').$u->email.get_string('capdmhelpdesk_notify_list','local_capdmhelpdesk').$p->period.')');
							// build the $to object
							$to = new stdClass();
							$to->email = $u->email;
							$to->firstname = $u->firstname;
							$to->lastname = $u->lastname;
							$to->mailformat = $u->mailformat;	// use the user setting for the type of message they want to receive i.e. text or HTML
							// build the message
							$msg = str_replace('~~FULLNAME~~',$u->firstname." ".$u->lastname, $p->message);
							$msg = str_replace('~~HTTP~~','http://', $msg);
							// only do this if there is a fullname field in the recordset
							if(in_object('fullname', $u)){
								$msg = str_replace('~~COURSENAME~~',$u->fullname, $msg);
							}

							if(SEND_EMAIL){
								if(email_to_user($to, get_admin(), $p->subject, strip_tags($msg), build_html_msg($msg),'','',FALSE)){
									$update = new stdClass();
									$update->id = $res;
									$update->sent = 1;
									$DB->update_record('capdmhelpdesk_selected', $update);
									mtrace(get_string('capdmhelpdesk_message_sent_to','local_capdmhelpdesk').$to->email);
								} else {
									mtrace(get_string('capdmhelpdesk_email_fail','local_capdmhelpdesk').$to->email);
								}
							} else {
								mtrace('...EMAIL FUNCTIONALITY HAS BEEN TURNED OFF...');
							}
						} catch(Exception $ex){

							if(strpos(strtolower($ex->error), 'Duplicate entry') > -1){
								continue;
							} else {
								mtrace(get_string('capdmhelpdesk_exception','local_capdmhelpdesk').$ex->error);
							}
						}
					}
				} else {
					mtrace(get_string('capdmhelpdesk_none_selected','local_capdmhelpdesk'));
				}

				//update the lastrun date for this prompt
				$update = new stdClass();
				$update->id = $p->id;
				$update->lastrun = $runtime;
				$DB->update_record('capdmhelpdesk', $update, true);
			}


//			return true;
		} else {
				mtrace(get_string('capdmhelpdesk_no_prompts_to_run','local_capdmhelpdesk'));
		}
	}
	catch(Exception $ex){
			mtrace(get_string('capdmhelpdesk_exception','local_capdmhelpdesk').$ex);

	}
*/
}


  // capdmhelpdesk_in_object method
  // to check if a value in an object exists.
  function capdmhelpdesk_in_object($value,$object) {
    if (is_object($object)) {
      foreach($object as $key => $item) {
        if ($value==$key) return true;
      }
    }
    return false;
  }



/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function capdmhelpdesk_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function capdmhelpdesk_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for capdmhelpdesk file areas
 *
 * @package mod_capdmhelpdesk
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function capdmhelpdesk_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the capdmhelpdesk file areas
 *
 * @package mod_capdmhelpdesk
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the capdmhelpdesk's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function capdmhelpdesk_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding capdmhelpdesk nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the capdmhelpdesk module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function capdmhelpdesk_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the capdmhelpdesk settings
 *
 * This function is called when the context for the page is a capdmhelpdesk module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $capdmhelpdesknode {@link navigation_node}
 */
function capdmhelpdesk_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $capdmhelpdesknode=null) {
}