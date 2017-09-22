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
 * Library of interface functions and constants for module newmodule
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage newmodule
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('NEWMODULE_ULTIMATE_ANSWER', 42);

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
function capdmdwb_supports($feature) {
    switch($feature) {
        
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;  // Added Aug 17, KWC
        case FEATURE_GRADE_OUTCOMES:          return false;        
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_BACKUP_MOODLE2:          return true; 
	case FEATURE_COMPLETION_HAS_RULES:    return true;  // Added July 16, KWC
        default:                              return null;
    }
}

/**
 * Saves a new instance of the capdmdwb into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $capdmdwb An object from the form in mod_form.php
 * @param mod_capdmdwb_mod_form $mform
 * @return int The id of the newly inserted capdmdwb record
 */
function capdmdwb_add_instance(stdClass $capdmdwb, mod_capdmdwb_mod_form $mform = null) {
    global $DB;

    $capdmdwb->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('capdmdwb', $capdmdwb);
}

/**
 * Updates an instance of the capdmdwb in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $capdmdwb An object from the form in mod_form.php
 * @param mod_capdmdwb_mod_form $mform
 * @return boolean Success/Fail
 */
function capdmdwb_update_instance(stdClass $capdmdwb, mod_capdmdwb_mod_form $mform = null) {
    global $DB;

    $capdmdwb->timemodified = time();
    $capdmdwb->id = $capdmdwb->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('capdmdwb', $capdmdwb);
}

/**
 * Removes an instance of the capdmdwb from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function capdmdwb_delete_instance($id) {
    global $DB;

    if (! $capdmdwb = $DB->get_record('capdmdwb', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('capdmdwb', array('id' => $capdmdwb->id));
    $DB->delete_records('capdmdwb_wrapper', array('course' => $capdmdwb->course));
    $DB->delete_records('capdmdwb_activity', array('course' => $capdmdwb->course));

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
function capdmdwb_user_outline($course, $user, $mod, $capdmdwb) {

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
 * @param stdClass $capdmdwb the module instance record
 * @return void, is supposed to echp directly
 */
function capdmdwb_user_complete($course, $user, $mod, $capdmdwb) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in capdmdwb activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function capdmdwb_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link capdmdwb_print_recent_mod_activity()}.
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
function capdmdwb_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see capdmdwb_get_recent_mod_activity()}

 * @return void
 */
function capdmdwb_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function capdmdwb_cron () {
    return true;
}

/**
 * Returns an array of users who are participanting in this capdmdwb
 *
 * Must return an array of users who are participants for a given instance
 * of capdmdwb. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $capdmdwbid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function capdmdwb_get_participants($capdmdwbid) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function capdmdwb_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of capdmdwb?
 *
 * This function returns if a scale is being used by one capdmdwb
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $capdmdwbid ID of an instance of this module
 * @return bool true if the scale is used by the given capdmdwb instance
 */
function capdmdwb_scale_used($capdmdwbid, $scaleid) {

	return false;
/*
    global $DB;

    if ($scaleid and $DB->record_exists('capdmdwb', array('id' => $capdmdwbid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
*/
}

/**
 * Checks if scale is being used by any instance of capdmdwb.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any capdmdwb instance
 */
function capdmdwb_scale_used_anywhere($scaleid) {
	return false;
/*

    global $DB;

    if ($scaleid and $DB->record_exists('capdmdwb', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
*/
}

/**
 * Creates or updates grade item for the give capdmdwb instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $capdmdwb instance object with extra cmidnumber and modname property
 * @return void
 */
function capdmdwb_grade_item_update(stdClass $capdmdwb, $grades=NULL) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Build the data array.  Note the actual fields updated are rawgrade and rawgrademax!!
    $item = array();
    $item['itemname'] = clean_param($capdmdwb->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    $item['grademax'] = $grades['grademax'];    $item['rawgrade'] = $grades['rawgrade'];

    grade_update('mod/capdmdwb', $capdmdwb->course, 'mod', 'capdmdwb', $capdmdwb->id, 0, $grades, $item);
}

/**
 * Update capdmdwb grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $capdmdwb instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function capdmdwb_update_grades(stdClass $capdmdwb, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!capdmdwb_supports(FEATURE_GRADE_HAS_GRADE)) return;  // Only do anything if it is supported

    /** @example */
    // Query what's been done
    $rs = $DB->get_record_sql("SELECT a.course, dwbacts, dwbdone FROM 
                                  (SELECT a.course, COUNT(a.id) AS dwbacts
                                   FROM mdl_capdmdwb_activity a
                                   GROUP BY a.course) a
                           LEFT JOIN (SELECT r.course, r.user_id, COUNT(DISTINCT r.data_id) AS dwbdone FROM mdl_capdmdwb_response r 
                                      JOIN mdl_capdmdwb_activity a ON a.activity_id = r.data_id
                                      WHERE r.user_id = 2 OR r.user_id IS NULL
                                      GROUP BY r.course) r 
                           ON a.course = r.course", array('user' => $userid));

    $grades = array(); // populate array of grade objects indexed by userid
    $grades['userid'] = $userid;
    $grades['rawgrade'] = $rs->dwbdone;   $grades['grademax'] = $rs->dwbacts;

    capdmdwb_grade_item_update($capdmdwb, $grades);
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
function capdmdwb_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the capdmdwb file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function capdmdwb_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload) {
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
 * Extends the global navigation tree by adding capdmdwb nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the capdmdwb module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function capdmdwb_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the capdmdwb settings
 *
 * This function is called when the context for the page is a capdmdwb module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $capdmdwbnode {@link navigation_node}
 */
function capdmdwb_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $capdmdwbnode=null) {
}

////////////////////////////////////////////////////////////////////////////////
// Completion Tracking API                                                    //
////////////////////////////////////////////////////////////////////////////////

/**
 * Obtains the automatic completion state for this DWB based on all completed
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function capdmdwb_get_completion_state($course, $cm, $userid, $type) 
// -----------------------------------------------------------------
{
    global $CFG,$DB;

    // Get forum details
    if (!($capdmdwb = $DB->get_record('capdmdwb' ,array('id' => $cm->instance)))) {
        throw new Exception("Can't find DWB {$cm->instance}");
    }
    
    // If completion option is enabled, evaluate it and return true/false 
    if ($capdmdwb->completionenabled) {
        $counts = $DB->get_record_sql("SELECT a.course, COUNT(a.activity_id) AS dwbacts, COUNT(r.id) AS dwbdone
                                       FROM (SELECT a.* FROM mdl_capdmdwb_wrapper w
                                             JOIN mdl_capdmdwb_activity a ON w.wrapper_id=a.wrapper_id
                                             WHERE w.course=".$course->id." AND w.role_id='".$capdmdwb->role_id."')  a
                                       LEFT JOIN mdl_capdmdwb_response  r ON a.course=r.course AND a.activity_id = r.data_id
                                       WHERE (r.user_id = ".$userid." OR r.user_id IS NULL)
                                       GROUP BY a.course;");
	
        if (!$counts) return false;
        else {
	    return ($counts->dwbacts == $counts->dwbdone) ? true : false;  // Are the counts of actual and done equal?
        }
    }
    else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}