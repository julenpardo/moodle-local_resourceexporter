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
 * Management of url-type resources download.
 *
 * @package    local_usablebackup
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_usablebackup;

defined('MOODLE_INTERNAL') || die();

use local_usablebackup\resource;

/**
 * Class url for the management of url-type resources download.
 *
 * @package    local_usablebackup
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class url extends resource {

    /**
     * Adds the urls of the given course to the received parent directory. If the url is not categorized in a section in the
     * course, it will be added to the $parentdirectory root.
     * The "transformation" of resource url to file, is creating a .txt file with the name of the url resource, and putting the
     * external url as the content.
     *
     * @param int $courseid The course id the urls to add to the directory belong to.
     * @param string $parentdirectory The directory to add the urls to.
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

            $sectionname = parent::get_section_name($courseid, $moduleid);
            $sectionname = parent::clean_file_and_directory_names($sectionname);

            $filename = $resource->name;
            $filename = parent::clean_file_and_directory_names($filename);

            $url = $resource->externalurl;

            $filedirectory = parent::create_section_dir_if_not_exists($parentdirectory, $sectionname);

            $filepath = $filedirectory . '/' . $filename . '.txt';
            $filepath = str_replace('//', '/', $filepath);

            file_put_contents($filepath, $url);

            array_push($addedfilespaths, $filepath);
        }

        return $addedfilespaths;
    }

    /**
     * Retrieves the information of all the urls of a course, and, also, the section of the course where it is.
     *
     * @param int $courseid The course to query the contents of.
     * @return array Index-based array ([0,n]) with the information of the urls.
     */
    protected function get_db_records($courseid) {
        global $DB;

        $sql = "SELECT course_modules.id AS course_module_id,
                       url.name,
                       url.externalurl,
                       course_sections.name AS section_name
                FROM   {url} url
                INNER JOIN {course_modules} course_modules
                    ON url.id = course_modules.instance
                    AND url.course = course_modules.course
                INNER JOIN {course_sections} course_sections
                    ON course_modules.section = course_sections.id

                WHERE url.course = ?";

        $records = $DB->get_records_sql($sql, array($courseid));
        $records = array_values($records);

        return $records;
    }
}
