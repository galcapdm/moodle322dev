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

/**
 * Simple base class for DWB renderers.
 */

class dwb_renderer_base {

    /**
     * Constructor
     *
     * The constructor takes two arguments. The first is the page that the renderer
     * has been created to assist with, and the second is the target.
     * The target is an additional identifier that can be used to load different
     * renderers for different options.
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        $this->opencontainers = $page->opencontainers;
        $this->page = $page;
        $this->target = $target;
    }

    /**
     * Returns rendered widget.
     *
     * The provided widget needs to be an object that extends the renderable
     * interface.
     * If will then be rendered by a method based upon the classname for the widget.
     * For instance a widget of class `crazywidget` will be rendered by a protected
     * render_crazywidget method of this renderer.
     *
     * @param renderable $widget instance with renderable interface
     * @return string
     */
    public function render(renderable $widget) {
        $rendermethod = 'render_'.get_class($widget);
        if (method_exists($this, $rendermethod)) {
            return $this->$rendermethod($widget);
        }
        throw new coding_exception('Can not render widget, renderer method ('.$rendermethod.') not found.');
    }

    /**
     * Returns true is output has already started, and false if not.
     *
     * @return boolean true if the header has been printed.
     */
    public function has_started() {
        return $this->page->state >= moodle_page::STATE_IN_BODY;
    }

}
