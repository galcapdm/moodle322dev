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

/**
 * Class containing data for detail_domains page
 *
 * @copyright  2017 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class header implements renderable, templatable {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
      global $DB;

        $records = $DB->get_records('user');
        $users = array();

        // Build an array of arrays representing the records returned from the query.
        foreach($records as $r){
            $thisUser = array('firstname'=>$r->firstname, 'lastname'=> $r->lastname);
            array_push($users, $thisUser);
        }

        // Define the $data object.
        $data = new stdClass();
        // Set the value for the list of users to be passed to the template
        $data->users = $users;

        return $data;
    }
}