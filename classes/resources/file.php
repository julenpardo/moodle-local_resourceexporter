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

namespace local_usablebackup;

defined('MOODLE_INTERNAL') || die();

use local_usablebackup\resource;

class file extends resource {

    public static function add_resources_to_zip($courseid, $zipfile) {

    }

    /**
     * Retrieves the information of all the files of a course, necessary to download them
     * later.
     *
     * @param int $courseid The course to query the contents of.
     * @return array Index-based array ([0,n]) with the information of the files.
     */
    protected static function get_db_records($courseid) {
        global $DB;

        $sql = "SELECT files.id,
                       course.id AS course_id,
                       course.fullname AS course_shortname,
                       course.shortname AS course_shortname,
                       files.contextid,
                       files.filename,
                       files.filearea,
                       files.filepath,
                       files.filesize,
                       files.mimetype,
                       files.itemid,
                       files.component
                FROM {files} files
                INNER JOIN {context} context
                    ON files.contextid = context.id
                INNER JOIN {course} course
                    ON course.id = context.instanceid

                WHERE context.contextlevel = 50
                    AND files.filename <> '.'
                    AND course.id = ?";

        $records = $DB->get_records_sql($sql, array($courseid));
        $records = array_values($records);

        return $records;
    }
}
