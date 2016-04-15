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

require_once('resource.php');

use local_usablebackup\resource;

class file extends resource {

    public static function add_resources_to_zip($courseid, $zipfile) {
        $resources = self::get_db_records($courseid);

        foreach ($resources as $resource) {
            $file = self::get_file_from_resource_info($resource);
        }
    }

    /**
     * Retrieves the information of all the files of a course, necessary to download them
     * later. And, also, the section of the course where it is.
     *
     * @param int $courseid The course to query the contents of.
     * @return array Index-based array ([0,n]) with the information of the files.
     */
    protected static function get_db_records($courseid) {
        global $DB;

        $sql = "SELECT files.id,
                       course.id AS course_id,
                       course.shortname AS course_shortname,
                       files.contextid,
                       files.filename,
                       files.filearea,
                       files.filepath,
                       files.itemid,
                       files.component,
                       resource.name AS resource_name,
                       course_sections.name AS section_name
                FROM {files} files
                INNER JOIN {context} context
                    ON files.contextid = context.id
                    AND context.contextlevel = 70
                INNER JOIN {course_modules} course_modules
                    ON context.instanceid = course_modules.id
                INNER JOIN {course course}
                    ON course_modules.course = course.id
                INNER JOIN {resource} resource
                    ON resource.course = course.id
                    AND resource.id = course_modules.instance
                INNER JOIN {course_sections} course_sections
                    ON course_sections.id = course_modules.section

                WHERE filename <> '.'
                    AND course.id = ?";

        $records = $DB->get_records_sql($sql, array($courseid));
        $records = array_values($records);

        return $records;
    }

    protected static function get_file_from_resource_info($resource) {
        $filestorage = get_file_storage();

        $file = $filestorage->get_file($resource->contextid,
            $resource->component,
            $resource->filearea,
            $resource->itemid,
            $resource->filepath,
            $resource->filename);

        return $file;
    }

}
