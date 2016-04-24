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

class folder extends resource {

    public function add_resources_to_directory($courseid, $parentdirectory) {

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
                       folder.name AS folder_name,
                       files.contextid,
                       files.filename,
                       files.filearea,
                       files.filepath,
                       files.itemid,
                       files.component,
                       course_sections.name AS section_name
                FROM   m_files files
                INNER JOIN m_context context
                    ON files.contextid = context.id
                INNER JOIN m_course_modules course_modules
                    ON course_modules.id = context.instanceid
                INNER JOIN m_folder folder
                    ON folder.id = course_modules.instance
                INNER JOIN m_course course
                    ON course.id = folder.course
                INNER JOIN m_course_sections course_sections
                    ON course_sections.id = course_modules.section

                WHERE files.component = 'mod_folder'
                    AND files.filename <> '.'
                    AND course.id = ?";

        $records = $DB->get_records_sql($sql, array($courseid));
        $records = array_values($records);

        return $records;
    }

}