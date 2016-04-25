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

abstract class resource {

    abstract public function add_resources_to_directory($courseid, $parentdirectory);

    abstract protected function get_db_records($courseid);

    /**
     * Checks if the given module is visible for the current user.
     * See documentation: https://docs.moodle.org/dev/Module_visibility_and_display#get_fast_modinfo_data
     *
     * @param int $courseid The course the backup is being created at.
     * @param int $moduleid The module id of the current resource.
     * @return boolean If the given module is visible for the current user or not.
     */
    protected function is_module_visible_for_user($courseid, $moduleid) {
        $moduleinfo = get_fast_modinfo($courseid);
        $module = $moduleinfo->get_cm($moduleid);

        return $module->uservisible;
    }

    /**
     * Creates the section directory inside the parent directory, if it does not exist already.
     * If the section is an empty string (because the given resource has not been categorized in any section), there's no need
     * to do any action, and the parent directory will be returned, and that's where the resource will be created later.
     *
     * @param string $parentdirectory The parent directory (the full path).
     * @param string $sectionname The name of the section of a resource.
     * @return string The section directory (the full path).
     */
    protected function create_section_dir_if_not_exists($parentdirectory, $sectionname) {
        if ($sectionname !== '') {
            $sectiondirectory = $parentdirectory . '/' . $sectionname;
            $sectiondirectory = str_replace('//', '/', $sectiondirectory);

            $directorynotexists = !is_dir($sectiondirectory);

            if ($directorynotexists) {
                mkdir($sectiondirectory);
            }
        } else {
            $sectiondirectory = $parentdirectory;
        }

        return $sectiondirectory;
    }

    /**
     * Cleans the files or directory names before the creation of them. "Clean" means removing those forbidden characters for the
     * file systems (most of them by Windows, thank you Microsoft). For that, a pattern is used, where each forbidden character is
     * specified.
     * Those characters will be replaced with '-' dash, a suitable for this and non problematic character. :)
     *
     * Do not use this function to clean paths that contains subdirectories! The slashes will be removed, so the path will be lost.
     *
     * @param string $name The file, directory, to be cleaned.
     * @return string The received name, cleaned, if containing any character defined in the pattern; or empty string if null param
     * received.
     */
    protected function clean_file_and_directory_names($name) {
        if ($name === null) {
            $cleanname = '';
        } else {
            $forbiddencharacterspattern = '[\\\\|\/|\:|\*|\?|\"|\<|\>|\|]';
            $replacement = '-';

            $cleanname = preg_replace($forbiddencharacterspattern, $replacement, $name);
        }

        return $cleanname;
    }
}