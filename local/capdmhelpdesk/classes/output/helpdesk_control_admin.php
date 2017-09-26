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
 * Class containing data for list_domains page
 *
 * @package    local_capdmhelpdesk
 * @copyright  2017 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_capdmhelpdesk\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use DateTime;

class helpdesk_control_admin implements renderable, templatable {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
      global $DB, $USER, $OUTPUT;

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
            // Get the helpdesk categories this user is listed for.
            $categories = $DB->get_records_sql('select cat.id from {capdmhelpdesk_cat} cat where cat_userid = :userid order by cat_order', array('userid'=>$USER->id));

            // Admins get to see all categories e.g. courses and helpdesk categories
            $strCats = '';
            foreach($categories as $c){
                $strCats .= "'".$c->id."', ";
            }

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
            $catfilter = ' and r.category in ('.$strCats.substr($crsfilter, 0, strlen($crsfilter)-2).') ';
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
                    if($days >= 1 ){
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
                if($r->updatedate != 'NA'){
                    $dateUp = date(DATE_RFC2822, $r->updatedate);
                    $message['updatedate'] = $dateUp;
                }
                array_push($messages, $message);
                // Need to unset the array as not always the same values being set.
                // If you don't unset then the next record keeps the last value.
                unset($message);
            }

        }

        // Set the value for various items sent to the template.
        //$data->cats = $cats;
        $data->messages = $messages;
        $data->admincategories = $arrCatCoursesList;
        $data->userid = $USER->id;
        $data->open = $open;
        $data->closed = $closed;
        $data->unread = $unread;
        // Pass in the rendered help HTML.
        $data->autoclosehelp = $OUTPUT->help_icon('autoclose', 'local_capdmhelpdesk');

        return $data;
    }
}