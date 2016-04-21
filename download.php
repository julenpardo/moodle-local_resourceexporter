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

require_once(dirname(__FILE__) . '/../../config.php');

defined('MOODLE_INTERNAL') || die();

require_login();

global $SESSION, $CFG;

$courseid = required_param('courseid', PARAM_INT);

$coursecontext = context_course::instance($courseid);

$validsubmission = isset($SESSION->usablebackup_downloadpermission);
$validsubmission &= isset($SESSION->usablebackup_filename);
$validsubmission &= is_enrolled($coursecontext);

if ($validsubmission) {
    $filename = $SESSION->usablebackup_filename;

    unset($SESSION->usablebackup_downloadpermission);
    unset($SESSION->usablebackup_filename);

    $zipfile = $CFG->tempdir . '/usablebackup/' . $filename;

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zipfile));

    readfile($zipfile);
} else {
    $previousurl = new \moodle_url('/local/usablebackup/create_zip.php', array('courseid' => $courseid, 'nopermission' => 1));

    redirect($previousurl);
}
