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
 * This file keeps track of upgrades to the capdmdwb module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage capdmdwb
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute capdmdwb upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_capdmdwb_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    if ($oldversion < 2015110101) {	// DWB 3.0
        $table = new xmldb_table('capdmdwb_wrapper');
        $field = new xmldb_field('group_id', XMLDB_TYPE_CHAR, '64', XMLDB_UNSIGNED, null, null, '', 'role_id');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        $table = new xmldb_table('capdmdwb_wrapper');
        $field = new xmldb_field('run_order', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'preamble');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        $table = new xmldb_table('capdmdwb_activity');
        $field = new xmldb_field('role_id', XMLDB_TYPE_CHAR, '64', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 'reflection', 'data_type');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        $table = new xmldb_table('capdmdwb_activity');
        $field = new xmldb_field('markval', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'run_order');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        $table = new xmldb_table('capdmdwb_response');
        $field = new xmldb_field('markval', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'data_explanation');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        $table = new xmldb_table('capdmdwb_response');
        $field = new xmldb_field('correct', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'markval');

        if ($dbman->field_exists($table, $field)) {
			$dbman->change_field_precision($table, $field);
        }

        upgrade_mod_savepoint(true, 2015110101, 'capdmdwb');
    }

    if ($oldversion < 2016062102) {
        echo("<p>Upgrading to 2016062102");

	// Define field wrapper_id to be added to capdmdwb_wrapper.
        $table = new xmldb_table('capdmdwb_wrapper');
        $field = new xmldb_field('wrapper_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'course');

        // Conditionally launch add field wrapper_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field wrapper_id to be added to capdmdwb_activity.
        $table = new xmldb_table('capdmdwb_activity');
        $field = new xmldb_field('wrapper_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'course');

        // Conditionally launch add field wrapper_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('activity_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'wrapper_id');

        // Conditionally launch add field activity_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field data_id to be added to capdmdwb_response.
        $table = new xmldb_table('capdmdwb_response');
        $field = new xmldb_field('data_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'mod_id');

        // Conditionally launch add field data_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Define field taskticks to be dropped from capdmdwb.
        $table = new xmldb_table('capdmdwb');
        $field = new xmldb_field('taskticks');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('certificate');
        // Conditionally launch drop field certificate.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('validate');
        // Conditionally launch drop field validate.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // New FORM fields
        // Define field role_id to be added to capdmdwb.
        $table = new xmldb_table('capdmdwb');
        $field = new xmldb_field('role_id', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, 'reflection', 'reference');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	    
        $field = new xmldb_field('repserver', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('rsuser', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('rspass', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('rsform', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('rsop', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Capdmdwb savepoint reached.
        upgrade_mod_savepoint(true, 2016062102, 'capdmdwb');
    }	

    if ($oldversion < 2016071801) {
        echo("<p>Upgrading to 2016071801");

	$table = new xmldb_table('capdmdwb');
        $field = new xmldb_field('completionenabled', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Capdmdwb savepoint reached.
        upgrade_mod_savepoint(true, 2016071801, 'capdmdwb');
    }	

    if ($oldversion < 2017022101) {

        // Changing precision of field wrapper_id on table capdmdwb_activity to (255).
        $table = new xmldb_table('capdmdwb_activity');
        $field = new xmldb_field('wrapper_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'course');

        // Launch change of precision for field wrapper_id.
        $dbman->change_field_precision($table, $field);

        // Changing precision of field activity_id on table capdmdwb_activity to (255).
        $table = new xmldb_table('capdmdwb_activity');
        $field = new xmldb_field('activity_id', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'wrapper_id');

        // Launch change of precision for field activity_id.
        $dbman->change_field_precision($table, $field);

        // Capdmdwb savepoint reached.
        upgrade_mod_savepoint(true, 2017022101, 'capdmdwb');
    }

    // And that's all. Please, examine and understand the 3 example blocks above. Also
    // it's interesting to look how other modules are using this script. Remember that
    // the basic idea is to have "blocks" of code (each one being executed only once,
    // when the module version (version.php) is updated.

    // Lines above (this included) MUST BE DELETED once you get the first version of
    // yout module working. Each time you need to modify something in the module (DB
    // related, you'll raise the version and add one upgrade block here.

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
