<?php

/**
 * Add page to admin menu.
 *
 * @copyright 2013 CAPDM Limited
 * @package local
 * @subpackage capdmhelpdesk
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('root', new admin_category('capdmhelpdesk', get_string('pluginname','local_capdmhelpdesk')));
$ADMIN->add('capdmhelpdesk', new admin_externalpage('capdmhelpdesksetup', get_string('capdmhelpdesksetup', 'local_capdmhelpdesk'), new moodle_url('/local/capdmhelpdesk/')));