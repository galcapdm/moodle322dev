<?php

/**
 * Classes for rendering HTML output for Moodle.
 *
 * Included in this file are the primary renderer classes:
 *     - dwb_renderer_base:     The DWB renderer outline class
 *
 * @package corecapdmdwb
 * @copyright  CAPDM 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define("_CAPDMDWB", "capdmdwb");  // Use this as the DWB Groupings name
define("_DWBFORUM", "dwb-forum"); // the guaranteed forum name
define("_DWBSTRLEN", 255);        // Maximum length of preamble, etc, output

/**
 * Simple base class for DWB renderers.
 */

abstract class dwb_type {
    /** @var object capdmdwb of the dwb in the database. */
    public $capdmdwb;

    /** @var the course. */
    public $course;

    /** @var integer cm for the course. */
    public $cm;

    /** @var integer sid of a student. */
    public $sid;

    /** @var integer tid of a topic. */
    public $tid;

    /** @var integer tabid of the current tab. */
    public $tabid;

    /** @var object dwb. */
    private $dwb;


    /**
     * Constructor
     *
     * @param $capdmdwb is the DWB in question
     * @param $course the course we are doing output for.
     * @param $cm is the context marker
     * @param $sid may be null, or a specific student id
     * @param $tid may be null, or a specific topic id
     */
    public function __construct($capdmdwb, $course, $cm, $sid, $tid, $tabid) {
    	$this->capdmdwb = $capdmdwb;
        $this->course   = $course;
        $this->cm       = $cm;
        $this->sid      = $sid;
        $this->tid      = $tid;
        $this->tabid    = $tabid;

        $dwbo = "dwb_".$capdmdwb->role_id;  // Role specifies the type
    }

    /**
     * Accessor common to all
     */
    function getDwb() {
    	return $dwb;
    }

    /**
     * Layout the DWB
     */
    abstract function render();

}
