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
 * Resource module admin settings and defaults
 *
 * @package    mod_capdmdwb
 * @copyright  CAPDM Ltd, www.capdm.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('capdmdwb/headerimg',
        get_string('headerimg', 'capdmdwb'), get_string('headerimgdesc', 'capdmdwb'), '', PARAM_TEXT, 80));

    $settings->add(new admin_setting_configtext('capdmdwb/frontpageimg',
        get_string('frontpageimg', 'capdmdwb'), get_string('frontpagedesc', 'capdmdwb'), '', PARAM_TEXT, 80));

    $settings->add(new admin_setting_configtext('capdmdwb/errorimg',
        get_string('errorimg', 'capdmdwb'), get_string('errorimgdesc', 'capdmdwb'), '', PARAM_TEXT, 80));

    $settings->add(new admin_setting_configcheckbox('capdmdwb/frontpageprintdate',
        get_string('frontpageprintdate', 'capdmdwb'), get_string('frontpageprintdatedesc', 'capdmdwb'), 1));

    $settings->add(new admin_setting_configcheckbox('capdmdwb/footerprintdate',
        get_string('footerprintdate', 'capdmdwb'), get_string('footerprintdatedesc', 'capdmdwb'), 1));

    $settings->add(new admin_setting_configcheckbox('capdmdwb/frontpageinfobox',
        get_string('frontpageinfobox', 'capdmdwb'), get_string('frontpageinfoboxdesc', 'capdmdwb'), 0));

    $settings->add(new admin_setting_configtextarea('capdmdwb/customcss',
        get_string('customcss', 'capdmdwb'), get_string('customcssdesc', 'capdmdwb'), '', PARAM_TEXT, 80));
}
