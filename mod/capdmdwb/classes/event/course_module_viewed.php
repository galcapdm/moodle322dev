<?php

/**
 * The mod_capdmdwb course module viewed event.
 *
 * @package    mod_capdmdwb
 * @copyright  2016 Ken Currie, CAPDM Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_capdmdwb\event;

defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'capdmdwb';
    }
}
