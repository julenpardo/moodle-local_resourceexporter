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
 * Download of the file, or redirection if something went bad.
 * This page cannot be accessed directly: the arrival to this page has to be made by a redirection from create_zip.php.
 * If a user tries to access this page directly, it will be redirected to the create_zip, where an error message will be shown.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

defined('MOODLE_INTERNAL') || die();

require_login();

global $SESSION, $CFG;

$courseid = required_param('courseid', PARAM_INT);

$coursecontext = context_course::instance($courseid);

$validsubmission = isset($SESSION->resourceexporter_downloadpermission);
$validsubmission &= isset($SESSION->resourceexporter_filename);
$validsubmission &= is_enrolled($coursecontext) || local_resourceexporter_is_admin($coursecontext);

if ($validsubmission) {
    $filename = $SESSION->resourceexporter_filename;

    unset($SESSION->resourceexporter_downloadpermission);
    unset($SESSION->resourceexporter_filename);

    $zipfile = $CFG->tempdir . '/resourceexporter/' . $filename;

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zipfile));

    readfile($zipfile);
} else {
    $previousurl = new \moodle_url('/local/resourceexporter/create_zip.php', array('courseid' => $courseid, 'nopermission' => 1));

    redirect($previousurl);
}
