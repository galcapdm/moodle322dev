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
 * Local capdmhelpdesk external services.
 *
 * @package    local_capdmhelpdesk
 * @copyright  2017 CAPDM Ltd - www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'local_capdmhelpdesk_get_replies' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'get_replies',
        'classpath'   => '',
        'description' => 'Return helpdesk replies.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_save_message' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'save_message',
        'classpath'   => '',
        'description' => 'Save a new helpdesk message.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_save_reply' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'save_reply',
        'classpath'   => '',
        'description' => 'Save a reply to a helpdesk message.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_reload_messages' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'reload_messages',
        'classpath'   => '',
        'description' => 'Get all messages for the current user.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_reload_messages_admin' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'reload_messages_admin',
        'classpath'   => '',
        'description' => 'Get all messages for the current admin user.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_update_message' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'update_message',
        'classpath'   => '',
        'description' => 'Update the current message e.g. close, readflag.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    ),
    'local_capdmhelpdesk_search' => array(
        'classname'   => 'local_capdmhelpdesk\external',
        'methodname'  => 'search',
        'classpath'   => '',
        'description' => 'Do a search based on the entered criteria and display the results',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => 'true',
    )
);

