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
 * File handling with Moodle File API.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourceexporter;

defined('MOODLE_INTERNAL') || die();

/**
 * Trait file_handler for file handling with Moodle File API.
 * A trait is used instead of parent methods in local_resourceexporter\resource class, because not all the resources deal with files,
 * but at least two of them they do.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

trait file_handler {

    /**
     * Creates the file object from the database info.
     *
     * @param object $resource The resource database object.
     * @return object The file object.
     */
    public function get_file_from_resource_info($resource) {
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
