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
 * This is the external API for this plugin.
 *
 * @package    local_capdmhelpdesk
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_capdmhelpdesk;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/webservice/externallib.php");
require_once(__DIR__ . '/../locallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;
use external_warnings;
use stdClass;
use context_system;
use core_user;
use DateTime;

/**
 * This is the external API for this plugin.
 *
 * @copyright  2017 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /*
     *  Get message replies - START.
     */

    /**
     * Describes the parameters for get_replies.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function get_replies_parameters() {
        return new external_function_parameters (
            array(
                'replyto' => new external_value(PARAM_INT, 'The ID of the original message', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
    * Expose to AJAX. This allows this code to be called using AJAX
    * @return boolean
    */
//    public static function get_replies_is_allowed_from_ajax() {
//        return true;
//    }

    /**
     * Returns the list of replies to the supplied message ID.
     * This is where most of the data logic is performed.
     *
     * @param   int     $msgid          Original message id
     * @return  array                   Array containing warnings and the orig message replies.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function get_replies($replyto = 0) {
        global $DB, $PAGE;

        $PAGE->set_context(context_system::instance());

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // get_replies_parameters to ensure they are of the correct type.
        $params = array(
            'replyto' => $replyto,
        );
        $params = self::validate_parameters(self::get_replies_parameters(), $params);

        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['replies'] = array();       // This is an array to hold the replies records.  An array of arrays!
        $result['replyto'] = $replyto;      // The original message ID.
        $result['status'] = $status;        // The status of the current message i.e. open, closed.
        $result['origmessage'] = $status;   // Text of the original message.
        $result['warnings'] = $warnings;    // Any warnings issued.

        $replies = $DB->get_records_sql('select r.id, replyto, r.message, req.message as origmessage, from_unixtime(r.submitdate, \'%D %M %Y %H:%i\') as submitdate, username, case when username is null then \'You\' else concat(firstname, \' \', lastname) end as fullname, case replierid when req.userid then \'You\' else \'other\' end as \'originator\', status from {capdmhelpdesk_replies} r left join {user} u on r.replierid = u.id inner join {capdmhelpdesk_requests} req on r.replyto = req.id where replyto = :replyto order by r.submitdate desc', array('replyto'=>$replyto));

        foreach ($replies as $r) {
            $result['replies'][] = array(
                'replyto' => $r->replyto,
                'message' => nl2br($r->message),
                'submitdate' => $r->submitdate,
                'username' => $r->username,
                'originator' => $r->originator,
                'fullname' => $r->fullname,
            );
            $result['status'] = $r->status;
            $result['origmessage'] = $r->origmessage;
        }

        // Now return the result array of values.
        return $result;
    }

    /**
     * Describes the get_replies return value.
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_replies_returns() {
        return new external_single_structure(
            array(
                'replies' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'replyto' => new external_value(PARAM_INT, 'ID of the original message.'),
                            'message' => new external_value(PARAM_RAW, 'Detail of the reply.'),
                            'submitdate' => new external_value(PARAM_TEXT, 'Date the reply was submitted converted by the MySQL to a date for consistency.'),
                            'username' => new external_value(PARAM_TEXT, 'Username of the replier.'),
                            'originator' => new external_value(PARAM_TEXT, 'Who is the orignator of this message.'),
                            'fullname' => new external_value(PARAM_TEXT, 'Who is the orignator of this message.  Some logic sorted in the SQL statement.'),
                        )
                    )
                ),
                'replyto' => new external_value(PARAM_INT, 'ID of the original message these replies belong to.'),
                'status' => new external_value(PARAM_INT, 'Status of the parent message.'),
                'origmessage' => new external_value(PARAM_TEXT, 'Text of the parent message.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Get message replies - END.
     */

    /*  ############################################################################################
     *  Save new message - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for get_replies.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function save_message_parameters() {
        return new external_function_parameters (
            array(
                'userid' => new external_value(PARAM_TEXT, 'The users moodle ID'),
                'category' => new external_value(PARAM_TEXT, 'Category ID for this message'),
                'subject' => new external_value(PARAM_TEXT, 'Subject of the message.'),
                'message' => new external_value(PARAM_TEXT, 'Text of the message.'),
                'updateby' => new external_value(PARAM_TEXT, 'ID of who last updated the message.'),
                'status' => new external_value(PARAM_TEXT, 'Open/close status.'),
                'readflag' => new external_value(PARAM_TEXT, 'Readflag status.'),
                'params' => new external_value(PARAM_TEXT, 'Parameters with this message.'),
            )
        );
    }

    /**
     * Returns the list of replies to the supplied message ID.
     * This is where most of the data logic is performed.
     *
     * @param   int     $msgid          Original message id
     * @return  array                   Array containing warnings and the orig message replies.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function save_message($userid, $category, $subject, $message, $updateby, $status, $readflag, $params) {
        global $DB, $PAGE;

        // Need this for email notifications to work!
        $PAGE->set_context(context_system::instance());

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // get_replies_parameters to ensure they are of the correct type.
        $params = array(
            'userid' => $userid,
            'category' => $category,
            'subject' => $subject,
            'message' => $message,
            'updateby' => $updateby,
            'status' => $status,
            'readflag' => $readflag,
            'params' => $params,
        );
        $params = self::validate_parameters(self::save_message_parameters(), $params);


        // This should be an object but using new stdClass() here causes an error.
        // So...built as an array and then cast as an object when submitting to the DB.
        $record = new stdClass();
        $record->userid = $userid;
        $record->category = $category;
        $record->subject = $subject;
        $record->message = $message;
        $record->submitdate = time();
        $record->updateby = $updateby;
        $record->status = 0;
        $record->readflag = 1;
        $record->params = $params;

        // Insert the record into the database table.
        $ret = $DB->insert_record('capdmhelpdesk_requests', $record);

        // If successfully saved then send confirmation email to user and notify email to tutors/admins.
        if($ret){
            // Need to look up the user details of who posted the message.
            $user = $DB->get_record('user', array('id'=>$userid));
            // Add extra parameters to the $user object to pass into the email process.
            $user->subject = $subject;
            $user->newmsgid = $ret;
            $ret = capdmhelpdesk_send_notification($user, 'new', $category);
        }

        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['messages'] = array();       // This is an array to hold the replies records.  An array of arrays!
        $result['status'] = $status;        // The status of the current message i.e. open, closed.
        $result['warnings'] = $warnings;    // Any warnings issued.

        $messages = $DB->get_records_sql('select * from {capdmhelpdesk_requests} where userid = :userid order by submitdate desc', array('userid' => $userid));

        foreach ($messages as $m) {
            $dateSub = date(DATE_RFC2822, $m->submitdate);
            $result['messages'][] = array(
                'id' => $m->id,
                'userid' => $m->userid,
                'subject' => $m->subject,
                'message' => $m->message,
                'submitdate' => $dateSub,
                'updatedate' => $m->updatedate,
                'updateby' => $m->updateby,
                'status' => $m->status,
                'readflag' => $m->readflag,
                'params' => $m->params,
            );
            $result['newmsgid'] = $ret;
        }

        // Now return the result array of values.
        return $result;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function save_message_returns() {
        return new external_single_structure(
            array(
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'ID of the message.'),
                            'userid' => new external_value(PARAM_TEXT, 'Userid of the user posting the message.'),
                            'subject' => new external_value(PARAM_TEXT, 'Subject of the message.'),
                            'message' => new external_value(PARAM_TEXT, 'Text of the message.'),
                            'submitdate' => new external_value(PARAM_TEXT, 'Date message was created.'),
                            'updatedate' => new external_value(PARAM_TEXT, 'Date message was updated.'),
                            'updateby' => new external_value(PARAM_TEXT, 'Who last updated the message.'),
                            'status' => new external_value(PARAM_TEXT, 'Message status.'),
                            'readflag' => new external_value(PARAM_TEXT, 'Readflag status.'),
                            'params' => new external_value(PARAM_TEXT, 'Parameters with this message.'),
                        )
                    )
                ),
                'newmsgid' => new external_value(PARAM_INT, 'ID of the newly inserted message.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Save new message - END.
     */
























































    /*  ############################################################################################
     *  Save reply to message - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for save_reply.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function save_reply_parameters() {
        return new external_function_parameters (
            array(
                'replyto' => new external_value(PARAM_INT, 'The ID of the message this reply relates to.'),
                'message' => new external_value(PARAM_TEXT, 'Text of the reply message.'),
                'replierid' => new external_value(PARAM_INT, 'Moodle ID of who replied.'),
                'notify' => new external_value(PARAM_TEXT, 'Indicator of who to notify of this reply.'),
                'owner' => new external_value(PARAM_INT, 'Indicator of who to notify of this reply.'),
                'subject' => new external_value(PARAM_TEXT, 'The subject of this message to use in the email reply.'),
                'status' => new external_value(PARAM_INT, 'The value for the status of this message. Works in hand with autoclose.'),
            )
        );
    }

    /**
     * Saves a reply to an existing message.
     *
     * @param   int     $msgid          Original message id.
     * @param   text    $message        Text of the message.
     * @param   test    $submitdate     Unix timestamp value.
     * @param   int     $replierid      Moodle ID of who replied.
     * @return  array                   Array containing warnings and the orig message replies.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function save_reply($replyto, $message, $replierid, $notify = 'NA', $owner, $subject, $status = 0) {
        global $DB, $USER, $PAGE;

        $PAGE->set_context(context_system::instance());

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // get_replies_parameters to ensure they are of the correct type.
        $params = array(
            'replyto' => $replyto,
            'message' => $message,
            'replierid' => $replierid,
            'notify' => $notify,
            'owner' => $owner,
            'subject' => $subject,
            'status' => $status,
        );
        $params = self::validate_parameters(self::save_reply_parameters(), $params);


        // An object when submitting to the DB.
        $record = new stdClass();
        $record->replyto = $replyto;
        $record->message = $message;
        $record->submitdate = time();
        $record->replierid = $replierid;

        // Insert the record into the database table.
        $ret = $DB->insert_record('capdmhelpdesk_replies', $record);

        // Now update the parent record.
        unset($record);

        $record = new stdClass();
        $record->id = $replyto;
        $record->status = $status;
        $record->updatedate = time();
        // Only update the readflag if the owner is the replier.
        if($replierid != $owner){
            $record->readflag = 0;
        }
        $record->updateby = $replierid;

        $ret = $DB->update_record('capdmhelpdesk_requests', $record);

        // If successfully saved then send notify email to the relevant person.
        // If the reply has come from the student then notify the admins/tutors.
        // If the reply has come the admin/tutors then notify the student.
        if($ret){
            // Need to look up the user details of who posted the message.
            switch ($notify){
                case 'student':
                    $user = $DB->get_record('user', array('id'=>$owner));
                    $msg = 'reply';
                    break;
                case 'admin':
                    // Need to modify the $user object to indicate this is an admin reponse
                    // so will require looking up who are the admins so they can all be informed.
                    $user = new stdClass();
                    $user->id = -1;
                    $user->msgid = $replyto;
                    $msg = 'replyadmin';
                    break;
                default:
                    // Send a message to the site support user (fallback is the site admin) as there is a problem then need to know about.
                    $user = core_user::get_support_user();
                    $msg = 'error_001';
                    break;
            }
            // Add some additional parameters to the $user object to use later.
            $user->subject = $subject;
            $ret = capdmhelpdesk_send_notification($user, $msg);
        }


        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['newmsgid'] = $ret;
        $result['warnings'] = $warnings;    // Any warnings issued.

        // Now return the result array of values.
        return $result;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function save_reply_returns() {
        return new external_single_structure(
            array(
                'newmsgid' => new external_value(PARAM_INT, 'ID of the newly inserted message.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Save reply to message - END.
     */

    /*  ############################################################################################
     *  Reloead messages - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for get_replies.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function reload_messages_parameters() {
        return new external_function_parameters (
            array(
                'userid' => new external_value(PARAM_INT, 'The users moodle ID'),
            )
        );
    }

    /**
     * Returns the list of messages for supplied message ID.
     * This is where most of the data logic is performed.
     *
     * @param   int     $userid         Moodle ID of the user to get records for.
     * @return  array                   Array containing warnings and the orig message replies.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function reload_messages($userid = 0) {
        global $DB, $OUTPUT, $PAGE;

        $PAGE->set_context(context_system::instance());

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // get_replies_parameters to ensure they are of the correct type.
        $params = array(
            'userid' => $userid,
        );
        $params = self::validate_parameters(self::reload_messages_parameters(), $params);

        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['messages'] = array();       // This is an array to hold the replies records.  An array of arrays!
        $result['status'] = $status;        // The status of the current message i.e. open, closed.
        $result['warnings'] = $warnings;    // Any warnings issued.

        $messages = $DB->get_records_sql('select r.id, r.userid, r.category, r.subject, cat.cat_name, r.message, r.submitdate, r.updatedate, r.updateby,
                                        case r.status when -1 then 0 else r.status end as status,
                                        case r.readflag when 0 then \'unread\' else \'read\' end as readflag, u.firstname, u.lastname
                                        from {capdmhelpdesk_requests} r
                                        inner join (
                                        select cat.id, cat.name as cat_name, cat.cat_userid, cat.cat_order as sortorder
                                        from {capdmhelpdesk_cat} cat
                                        union
                                        select id, fullname as cat_name, 0 as cat_userid, sortorder from {course} c where c.id > 1 order by sortorder
                                        ) cat on r.category = cat.id
                                        left join {user} u on r.updateby = u.id
                                        where userid = :userid order by submitdate desc', array('userid'=>$userid));

        foreach ($messages as $m) {
            $dateSub = date(DATE_RFC2822, $m->submitdate);
            if($m->updatedate != 'NA'){
                $dateUp = date(DATE_RFC2822, $m->updatedate);
            } else {
                $dateUp = '';
            }

            $result['messages'][] = array(
                'id' => $m->id,
                'userid' => $m->userid,
                'subject' => $m->subject,
                'message' => $m->message,
                'submitdate' => $dateSub,
                'updatedate' => $dateUp,
                'updateby' => $m->updateby,
                'status' => $m->status,
                'readflag' => $m->readflag,
                'params' => $m->params,
                'firstname' => $m->firstname,
                'lastname' => $m->lastname,
                'autoclosehelp' => $OUTPUT->help_icon('autoclose', 'local_capdmhelpdesk'),
            );
        }

        // Now return the result array of values.
        return $result;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function reload_messages_returns() {
        return new external_single_structure(
            array(
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'ID of the message.'),
                            'userid' => new external_value(PARAM_TEXT, 'Userid of the user posting the message.'),
                            'subject' => new external_value(PARAM_TEXT, 'Subject of the message.'),
                            'message' => new external_value(PARAM_TEXT, 'Text of the message.'),
                            'submitdate' => new external_value(PARAM_TEXT, 'Date message was created.'),
                            'updatedate' => new external_value(PARAM_TEXT, 'Date message was updated.'),
                            'updateby' => new external_value(PARAM_TEXT, 'Who last updated the message.'),
                            'status' => new external_value(PARAM_TEXT, 'Message status.'),
                            'readflag' => new external_value(PARAM_TEXT, 'Readflag status.'),
                            'params' => new external_value(PARAM_TEXT, 'Parameters with this message.'),
                            'autoclosehelp' => new external_value(PARAM_RAW, 'HTML for the autoclose help.'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Reloead messages - END.
     */

    /*  ############################################################################################
     *  Search messages - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for search.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function search_parameters() {
        return new external_function_parameters (
            array(
                'param' => new external_value(PARAM_TEXT, 'The search string'),
            )
        );
    }

    /**
     * Returns the list of messages for supplied search string.
     * This is where most of the data logic is performed.
     *
     * @param   text     $param         String of text to search with.
     * @return  array                   Array containing warnings and the found records.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function search($param = '') {
        global $DB, $OUTPUT, $PAGE;

        $PAGE->set_context(context_system::instance());

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // search_parameters to ensure they are of the correct type.
        $params = array(
            'param' => $param,
        );
        $params = self::validate_parameters(self::search_parameters(), $params);

        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['messages'] = array();      // This is an array to hold the replies records.  An array of arrays!
        $result['warnings'] = $warnings;    // Any warnings issued.
        $result['success'] = 0;             // Status if records found. 0 = no records found, 1 = records found.

        $messages = $DB->get_records_sql('select r.id, r.category, r.subject, from_unixtime(submitdate) as submitdate, case updatedate when 0  then \'--\' else from_unixtime(updatedate) end as updatedate, u.firstname, u.lastname, u.email from {capdmhelpdesk_requests} r join {user} u on r.userid = u.id where (lastname like :par1) or (firstname like :par2) or (r.id = :par3)', array('par1'=>$params['param'], 'par2'=>$params['param'], 'par3'=>$params['param']));

        if(!$messages){
            $result['results'][] = array(
                'id' => 0,
                'userid' => '',
                'subject' => '',
                'firstname' => '',
                'lastname' => '',
                'submitdate' => '',
                'updatedate' => '',
            );
        } else {
            $result['success'] = 1;
            foreach ($messages as $m) {
                $result['results'][] = array(
                    'id' => $m->id,
                    'userid' => $m->userid,
                    'subject' => $m->subject,
                    'firstname' => $m->firstname,
                    'lastname' => $m->lastname,
                    'submitdate' => $m->submitdate,
                    'updatedate' => $m->updatedate,
                );
            }
        }

        // Now return the result array of values.
        return $result;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function search_returns() {
        return new external_single_structure(
            array(
                'results' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'ID of the message.'),
                            'userid' => new external_value(PARAM_TEXT, 'Userid of the user posting the message.'),
                            'subject' => new external_value(PARAM_TEXT, 'Subject of the message.'),
                            'firstname' => new external_value(PARAM_TEXT, 'User firstname.'),
                            'lastname' => new external_value(PARAM_TEXT, 'User lastname.'),
                            'submitdate' => new external_value(PARAM_TEXT, 'Date submitted.'),
                            'updatedate' => new external_value(PARAM_TEXT, 'Date last updated.'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
                'success' => new external_value(PARAM_INT, 'Value indicating search success - 0 = no records found.'),
            )
        );
    }

    /*
     *  Search messages - END.
     */




























    /*  ############################################################################################
     *  Reload messages admin - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for get_replies.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function reload_messages_admin_parameters() {
        return new external_function_parameters (
            array(
                'userid' => new external_value(PARAM_INT, 'The users moodle ID'),
            )
        );
    }

    /**
     * Returns the list of messages for supplied message ID.
     * This is where most of the data logic is performed.
     *
     * @param   int     $userid         Moodle ID of the user to get records for.
     * @return  array                   Array containing warnings and the orig message replies.
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function reload_messages_admin($userid) {
        global $DB, $USER, $OUTPUT, $PAGE;

        $PAGE->set_context(context_system::instance());

        $userid = $USER->id;

        // Define the $data object that is going to be returned plus any common arrays/objects being used.
        $data = new stdClass();
        $messages = array();

        // Get a list of CAPDMHELPDESK categories this user has access to.
        // If they are a site admin then they see all.
        $arrCatCoursesList = array();
        if(array_key_exists($USER->id, get_admins())){
            $catfilter = ' ';

            // Add a couple of arrays to hold info.
            $cats = array();
            $cat = array();

            $categories = $DB->get_records_sql('select cat.id, cat.name as cat_name, cat.cat_userid, cat.cat_order as sortorder from {capdmhelpdesk_cat} cat union select id, fullname as cat_name, 0 as cat_userid, sortorder from {course} c where c.id > 1 order by sortorder');

            // Admins get to see all categories e.g. courses and helpdesk categories
            foreach($categories as $c){
                    $arrCourseAdmin['id'] = $c->id;
                    $arrCourseAdmin['fullname'] = $c->cat_name;
                    array_push($arrCatCoursesList, $arrCourseAdmin);
            }


        } else {
            // Limit this CAPDMHELPDESK admin to only view their tickets.
            $arrCatCourses = capdmhelpdesk_get_user_enrolments_role($USER->id);

            $crsfilter = '';
            if($arrCatCourses){
                foreach($arrCatCourses as $c){
                    $crsfilter .= $c->id.', ';
                    $arrCourseAdmin['id'] = $c->id;
                    $arrCourseAdmin['fullname'] = $c->fullname;
                    array_push($arrCatCoursesList, $arrCourseAdmin);
                }
            }
            $catfilter = ' and r.category in ('.substr($crsfilter, 0, strlen($crsfilter)-2).') ';
        }

        // Get the list of helpdesk requests for the supplied userid.
        $records = $DB->get_records_sql('select r.id, r.userid, r.category, r.subject, cat.cat_name, r.message, r.submitdate, case r.updatedate when 0 then \'NA\' else r.updatedate end
                                        as updatedate, r.updateby, r.status, case r.readflag when 0 then \'unread\' else \'read\' end as readflag,
                                        u.firstname, u.lastname, coalesce(replies, 0) as replies
                                        from {capdmhelpdesk_requests} r
                                        inner join (
                                        select cat.id, cat.name as cat_name, cat.cat_userid, cat.cat_order as sortorder
                                        from {capdmhelpdesk_cat} cat
                                        union
                                        select id, fullname as cat_name, 0 as cat_userid, sortorder from {course} c where c.id > 1 order by sortorder
                                        ) cat on r.category = cat.id
                                        left join {user} u on r.updateby = u.id
                                        left join (
                                        select replyto, count(id) as replies from {capdmhelpdesk_replies} group by replyto
                                        ) replies on r.id = replies.replyto
                                        where (status = 0 or status = -1)'.$catfilter.'order by submitdate desc');

        // Get some stats for the helpdesk for the supplied userid.
        $stats = $DB->get_records_sql('select 1 as id, userid, \'open\' as status, count(id) as totalStatus from {capdmhelpdesk_requests} where status = 0 group by status union select 2 as id, userid,  \'closed\' as status, count(id) as totalStatus from {capdmhelpdesk_requests} where status = 1 group by status union select 3 as id, userid, \'unread\' as status, count(id) as totalStatus from {capdmhelpdesk_requests} where readflag = 0 group by readflag');

        $open = 0;
        $closed = 0;
        $unread = 0;

        foreach($stats as $s){
            switch($s->status){
                case 'open':
                    $open = $s->totalstatus;
                    break;
                case 'closed':
                    $closed = $s->totalstatus;
                    break;
                case 'unread':
                    $unread = $s->totalstatus;
                    break;
            }
        }

        $message = array();

        // Build an array of arrays representing the records returned from the query.
        if($records){
            $updated = false;
            // Now loop through the records to build the data->messages array.
            foreach($records as $r){
                $dateSub = date(DATE_RFC2822, $r->submitdate);

                // Figure out how old this request is.
                $submitDate = new DateTime($dateSub);
                $dateNow = new DateTime("now");
                $msgAge = $submitDate->diff($dateNow);
                $days = $msgAge->format('%a');
                $hrs = $msgAge->format('%h');
                $mins = $msgAge->format('%I');
                if($days > 0){
                    $age = $days.' '.get_string('days', 'local_capdmhelpdesk').' '.$hrs.get_string('hrs', 'local_capdmhelpdesk').' '.$mins.get_string('mins', 'local_capdmhelpdesk');
                    if($days > 1){
                        $ageStatus = '25';
                    } else {
                        $ageStatus = '24';
                    }
                } elseif($hrs > 0) {
                    $age = $hrs.' '.get_string('hrs', 'local_capdmhelpdesk').' '.$mins.get_string('mins', 'local_capdmhelpdesk');
                    if($hrs > 11){
                        $ageStatus = '24';
                    } else if($hrs > 7){
                        $ageStatus = '12';
                    } else if($hrs > 3){
                        $ageStatus = '8';
                    } else {
                        $ageStatus = '4';
                    }
                } else {
                    $age = $mins.' '.get_string('mins', 'local_capdmhelpdesk');
                    $ageStatus = '4';
                }

                $message['id'] = $r->id;
                $message['owner'] = $r->userid;
                $message['category'] = $r->cat_name;
                $message['categoryid'] = $r->category;
                $message['subject'] = $r->subject;
                $message['message'] = $r->message;
                $message['status'] = $r->status;
                $message['readflag'] = $r->readflag;
                $message['submitdate'] = $dateSub;
                $message['replies'] = $r->replies;
                $message['firstname'] = $r->firstname;
                $message['lastname'] = $r->lastname;
                $message['age'] = $age;
                $message['agestatus'] = $ageStatus;
                $message['userid'] = $userid;
                if($r->updatedate != 'NA'){
                    $dateUp = date(DATE_RFC2822, $r->updatedate);
                    $message['updatedate'] = $dateUp;
                } else {
                    $message['updatedate'] = '';
                }
                $message['autoclosehelp'] = $OUTPUT->help_icon('autoclose', 'local_capdmhelpdesk');
                array_push($messages, $message);
                // Need to unset the array as not always the same values being set.
                // If you don't unset then the next record keeps the last value.
                unset($message);
            }

        }

        // Set the value for various items sent to the template.
        //$data->cats = $cats;
        $data->messages = $messages;

        return $data;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function reload_messages_admin_returns() {
        return new external_single_structure(
            array(
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'ID of the message.'),
                            'owner' => new external_value(PARAM_TEXT, 'UserID of the message owner.'),
                            'category' => new external_value(PARAM_TEXT, 'Category this message belongs to.'),
                            'categoryid' => new external_value(PARAM_TEXT, 'Category ID this message belongs to.'),
                            'subject' => new external_value(PARAM_TEXT, 'Subject of the message.'),
                            'message' => new external_value(PARAM_TEXT, 'Text of the message.'),
                            'status' => new external_value(PARAM_INT, 'Message status.'),
                            'readflag' => new external_value(PARAM_TEXT, 'Message readflag status.'),
                            'submitdate' => new external_value(PARAM_TEXT, 'Date message was created.'),
                            'replies' => new external_value(PARAM_INT, 'Number of replies to this message.'),
                            'firstname' => new external_value(PARAM_TEXT, 'First name of the message orginator.'),
                            'lastname' => new external_value(PARAM_TEXT, 'Last name of the message orginator.'),
                            'age' => new external_value(PARAM_TEXT, 'Age of the message.'),
                            'agestatus' => new external_value(PARAM_INT, 'Age of the message.'),
                            'userid' => new external_value(PARAM_TEXT, 'Userid of the user posting the message.'),
                            'updatedate' => new external_value(PARAM_TEXT, 'Date message was updated.'),
                            'autoclosehelp' => new external_value(PARAM_RAW, 'Help icon for autoclose.'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Reloead messages admin - END.
     */

































    /*  ############################################################################################
     *  Update a message - START.
     *  ############################################################################################
     */

    /**
     * Describes the parameters for update_message.
     *
     * @return external_function_parameters
     * @since Moodle 3.1
     */
    public static function update_message_parameters() {
        return new external_function_parameters (
            array(
                'msgid' => new external_value(PARAM_INT, 'The ID of the record to update.'),
                'field' => new external_value(PARAM_TEXT, 'The name of the field to update.'),
                'val' => new external_value(PARAM_TEXT, 'Value of the field. As it is variable then must be sent as TEXT.'),
            )
        );
    }

    /**
     * Updates an existing message.  This can be status or readflag or anything else.
     *
     * @param   int     $msgid          ID of the message to update.
     * @param   text    $field          String value of the field to update.
     * @param   text    $val            Value for the field.
     * @return  bool                    Boolean value of update status
     * @since   Moodle 3.1
     * @throws  moodle_exception
     */
    public static function update_message($msgid, $field, $val) {
        global $DB;

        // Build an array for any warnings.
        $warnings = array();

        // Build an array of the input parameters so they can be checked using
        // update_message_parameters to ensure they are of the correct type.
        $params = array(
            'msgid' => $msgid,
            'field' => $field,
            'val' => $val,
        );
        $params = self::validate_parameters(self::update_message_parameters(), $params);

        // Need to build as an array but then cast as an object to allow
        // for variable field names.
        $record = array();
        $record['id'] = $msgid;
        $record[$field] = $val;

        // Update the record into the database table.
        $ret = $DB->update_record('capdmhelpdesk_requests', (object)$record, false);

        // Build an array to hold the itmes to be returned to the template.
        $result = array();
        $result['update'] = $ret;
        $result['warnings'] = $warnings;    // Any warnings issued.

        // Now return the result array of values.
        return $result;
    }

    /**
     * This checks the data type of the returned
     * values to make sure they are what is expected.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function update_message_returns() {
        return new external_single_structure(
            array(
                'update' => new external_value(PARAM_BOOL, 'Boolean value of the update status.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     *  Update message - END.
     */


}
