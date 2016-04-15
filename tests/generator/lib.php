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
 * local_usablebackup data generator.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * local_usablebackup data generator.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_generator extends testing_module_generator {

    protected $resourcegenerator;

    public function __construct($datagenerator) {
        parent::__construct($datagenerator);
        $this->resourcegenerator = $datagenerator->get_plugin_generator('mod_resource');
    }

    /**
     * Creates the course, just calls to the original method.
     *
     * @param string $name The name of the course.
     * @return mixed The created course object.
     */
    public function create_course($name = 'Some course') {
        return $this->datagenerator->create_course(array($name));
    }

    /**
     * Queries the last created file for a course, used the data generator for resources.
     *
     * @param int $courseid The course for which the resource was created.
     * @return object The last file object created for the course.
     */
    protected function get_last_created_file($courseid) {
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
                INNER JOIN {course} course
                    ON course_modules.course = course.id
                INNER JOIN {resource} resource
                    ON resource.course = course.id
                    AND resource.id = course_modules.instance
                INNER JOIN {course_sections} course_sections
                    ON course_sections.id = course_modules.section

                WHERE filename <> '.'
                    AND course.id = ?

                ORDER BY files.id DESC";

        $file = $DB->get_record_sql($sql, array($courseid));

        return $file;
    }

    /**
     * Creates a resource with the given data using the data generator. Then, updates the file that was automatically generated,
     * using the data provided.
     *
     * @param int $course The course for which the resource will be created.
     * @param string $name The name of the resource.
     * @param string $filename The name of the file.
     * @param string $filearea The area of the file.
     * @param string $filepath The path to the file.
     * @return array The resource and the file objects, the only way to return two values...
     */
    public function create_resource($course, $name, $filename, $filearea = "content", $filepath = "/") {
        global $DB;

        $resourceattributes = array('course' => $course, 'name' => $name);

        $resource = $this->resourcegenerator->create_instance($resourceattributes);
        $file = $this->get_last_created_file($course);

        $file->filename = $filename;
        $file->filearea = $filearea;
        $file->filepath = $filepath;

        $DB->update_record('files', $file);

        $resourceandfile = array('resource' => $resource, 'file' => $file);

        return $resourceandfile;
    }
}
