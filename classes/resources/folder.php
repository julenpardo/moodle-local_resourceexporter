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
 * Management of folder-type resources download.
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

/**
 * Class folder for the management of folder-type resources download.
 *
 * @package    local_usablebackup
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class folder extends resource {

    use file_handler;

    /**
     * Adds the files inside folder resources of the given course to the received parent directory. Every folder-type resource
     * will have a folder with the same name, which will be, in the same way, inside the section directory, if any.
     *
     * @param int $courseid The course id the folders to add to the directory belong to.
     * @param string $parentdirectory The directory to add the resources to.
     * @return array The path of every added folder.
     */
    public function add_resources_to_directory($courseid, $parentdirectory) {
        $resources = $this->get_db_records($courseid);
        $addedfilespaths = array();

        foreach ($resources as $resource) {
            $moduleid = $resource->course_module_id;
            if (!parent::is_module_visible_for_user($courseid, $moduleid)) {
                continue;
            }

            $sectionname = parent::get_section_name($courseid, $moduleid);
            $sectionname = parent::clean_file_and_directory_names($sectionname);

            $file = $this->get_file_from_resource_info($resource); // File_handler trait method.
            $filename = parent::clean_file_and_directory_names($file->get_filename());
            $filecontent = $file->get_content_file_handle();

            $foldername = $resource->folder_name;
            $filepath = parent::create_section_dir_if_not_exists($parentdirectory, $sectionname);
            $filepath = parent::create_section_dir_if_not_exists($filepath, $foldername);
            $filepath .= '/' . $filename;
            $filepath = str_replace('//', '/', $filepath);

            file_put_contents($filepath, $filecontent);

            array_push($addedfilespaths, $filepath);
        }

        return $addedfilespaths;
    }

    /**
     * Retrieves the information of all the folders, and the files within them, of the given course.
     *
     * @param int $courseid The course to query the contents of.
     * @return array Index-based array ([0,n]) with the information of the files in the folders.
     */
    protected function get_db_records($courseid) {
        global $DB;

        $sql = "SELECT files.id AS file_id,
                       folder.id AS folder_id,
                       course_modules.id AS course_module_id,
                       folder.name AS folder_name,
                       files.contextid,
                       files.filename,
                       files.filearea,
                       files.filepath,
                       files.itemid,
                       files.component,
                       course_sections.name AS section_name
                FROM   {files} files
                INNER JOIN {context} context
                    ON files.contextid = context.id
                INNER JOIN {course_modules} course_modules
                    ON course_modules.id = context.instanceid
                INNER JOIN {folder} folder
                    ON folder.id = course_modules.instance
                INNER JOIN {course} course
                    ON course.id = folder.course
                INNER JOIN {course_sections} course_sections
                    ON course_sections.id = course_modules.section

                WHERE files.component = 'mod_folder'
                    AND files.filename <> '.'
                    AND course.id = ?";

        $records = $DB->get_records_sql($sql, array($courseid));
        $records = array_values($records);

        return $records;
    }

}