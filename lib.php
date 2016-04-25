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
 *
 * @package    local_usablebackup
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds the created url to the navigation block (that usually under the admin block).
 * See documentation: https://docs.moodle.org/dev/Navigation_API#Navbar
 *
 * @param object $navigation The navigation object.
 */
function local_usablebackup_extend_navigation($navigation) {
    global $COURSE;

    $pluginname = get_string('pluginname', 'local_usablebackup') . ' in navigation!';
    $url = new moodle_url('/local/usablebackup/create_zip.php', array('courseid' => $COURSE->id));

    $navigation->add($pluginname, $url, navigation_node::TYPE_SETTING);
}
