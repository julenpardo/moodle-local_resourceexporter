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
require_once('file_handler.php');

use local_usablebackup\file_handler;
use local_usablebackup\resource;

class file extends resource {

    use file_handler;

    /**
     * Adds the file resources of the given course to the received parent directory. If the file is not categorized in a section
     * in the course, it will be added to the $parentdirectory root.
     *
     * @param int $courseid The course id the files to add to the directory belong to.
     * @param string $parentdirectory The directory to add the resources to.
     * @return array The path of every added file.
     */
    public function add_resources_to_directory($courseid, $parentdirectory) {
        $resources = $this->get_db_records($courseid);
        $addedfilespaths = array();

        foreach ($resources as $resource) {
            $moduleid = $resource->course_module_id;
            if (!parent::is_module_visible_for_user($courseid, $moduleid)) {
                continue;
            }

            $sectionname = parent::clean_file_and_directory_names($resource->section_name);

            $file = $this->get_file_from_resource_info($resource); // file_handler trait method.
            $filename = parent::clean_file_and_directory_names($file->get_filename());

            $filecontent = $file->get_content_file_handle();

            $filepath = parent::create_section_dir_if_not_exists($parentdirectory, $sectionname);
            $filepath .= '/' . $filename;
            $filepath = str_replace('//', '/', $filepath);

            file_put_contents($filepath, $filecontent);

            array_push($addedfilespaths, $filepath);
        }

        return $addedfilespaths;
    }

    /**
     * Retrieves the information of all the files of a course, necessary to download them later. And, also, the section of
     * the course where it is.
     *
     * @param int $courseid The course to query the contents of.
     * @return array Index-based array ([0,n]) with the information of the files.
     */
    protected function get_db_records($courseid) {
        global $DB;

        $sql = "SELECT files.id,
                       course.id AS course_id,
                       course.shortname AS course_shortname,
                       course_modules.id AS course_module_id,
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
                INNER JOIN {course} course
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

}
