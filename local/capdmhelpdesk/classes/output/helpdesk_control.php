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

class helpdesk_control implements renderable, templatable {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
      global $DB, $USER;

        $userid = $USER->id;

        // Define the $data object that is going to be returned plus any common arrays/objects being used.
        $data = new stdClass();
        $messages = array();

        // Add a couple of arrays to hold info.
        $cats = array();
        $cat = array();

        $categories = $DB->get_records_sql('select cat.id, cat.name as cat_name, cat.cat_userid, cat.cat_order as sortorder from {capdmhelpdesk_cat} cat union select id, fullname as cat_name, 0 as cat_userid, sortorder from {course} c where c.id > 1 order by sortorder');

        // Get an array of courses this uers is enrolled on.
        $usercourses = enrol_get_users_courses($USER->id);

        // Now check to see if they are enrolled on any of the available courses when building alist list available helpdesk categories for them.
        // All CAPDMHELPDESK categories are included regardless.
        foreach($categories as $c){
            if(!is_numeric($c->id) || array_key_exists($c->id, $usercourses)){
                $cat['id'] = $c->id;
                $cat['value'] = $c->cat_name;
                array_push($cats, $cat);
            }
        }

        // Get the list of helpdesk requests for the supplied userid.
        $records = $DB->get_records_sql('select r.id, r.userid, r.category, r.subject, cat.cat_name, r.message, r.submitdate, case r.updatedate when 0 then \'NA\' else r.updatedate end
                                        as updatedate, r.updateby, case r.status when -1 then 0 else r.status end as status, case r.readflag when 0 then \'unread\' else \'read\' end
                                        as readflag,
                                        u.firstname, u.lastname
                                        from {capdmhelpdesk_requests} r
                                        inner join (
                                        select cat.id, cat.name as cat_name, cat.cat_userid, cat.cat_order as sortorder
                                        from {capdmhelpdesk_cat} cat
                                        union
                                        select id, fullname as cat_name, 0 as cat_userid, sortorder from {course} c where c.id > 1 order by sortorder
                                        ) cat on r.category = cat.id
                                        left join {user} u on r.updateby = u.id
                                        where userid = :userid order by submitdate desc', array('userid'=>$USER->id));

        // Get some stats for the helpdesk for the supplied userid.
        $stats = $DB->get_records_sql('select 1 as id, userid, \'open\' as status, count(case status when -1 then 0 else status end) as totalStatus
from {capdmhelpdesk_requests} where userid = 3 and (status = 0 or status = -1) group by userid union select 2 as id, userid,  \'closed\' as status, count(id) as totalStatus from {capdmhelpdesk_requests} where userid = :userid2 and status = 1 group by status union select 3 as id, userid, \'unread\' as status, count(id) as totalStatus from {capdmhelpdesk_requests} where userid = :userid3 and readflag = 0 group by readflag', array('userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid));

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

                if($r->updatedate === 'NA'){
                    $message['id'] = $r->id;
                    $message['category'] = $r->category;
                    $message['subject'] = $r->subject;
                    $message['message'] = nl2br($r->message);
                    $message['status'] = $r->status;
                    $message['readflag'] = $r->readflag;
                    $message['submitdate'] = $dateSub;
                } else {
                    $dateUp = date(DATE_RFC2822, $r->updatedate);
                    $message['id'] = $r->id;
                    $message['category'] = $r->category;
                    $message['subject'] = $r->subject;
                    $message['message'] = nl2br($r->message);
                    $message['status'] = $r->status;
                    $message['readflag'] = $r->readflag;
                    $message['submitdate'] = $dateSub;
                    $message['updatedate'] = $dateUp;
                    $message['firstname'] = $r->firstname;
                    $message['lastname'] = $r->lastname;
                }
                array_push($messages, $message);
                // Need to unset the array as not always the same values being set.
                // If you don't unset then the next record keeps the last value.
                unset($message);
            }

        }

        // Set the value for various items sent to the template.
        $data->cats = $cats;
        $data->messages = $messages;
        $data->userid = $USER->id;
        $data->open = $open;
        $data->closed = $closed;
        $data->unread = $unread;

        return $data;
    }
}