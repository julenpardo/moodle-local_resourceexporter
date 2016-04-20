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

require_once('../../config.php');
require_once('classes/downloader/downloader.php');

defined('MOODLE_INTERNAL') || die();

global $CFG, $SESSION;

use local_usablebackup\downloader;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$nopermission = optional_param('nopermission', 0, PARAM_INT);
$coursecontext = context_course::instance($courseid);

init_page($coursecontext);

if ($nopermission) {
    print_error_page($nopermission);
} else if (is_enrolled($coursecontext)) {
    create_zip_and_redirect_to_download($courseid);
} else {
    print_error_page();
}

/**
 * Initializes the page with: context, url, title, page layout.
 *
 * @param object $coursecontext The course context.
 */
function init_page($coursecontext) {
    global $PAGE;

    $PAGE->set_context($coursecontext);
    $PAGE->set_url('/local/usablebackup/create_zip.php');
    $PAGE->set_title(get_string('createzip_title', 'local_usablebackup'));
    $PAGE->set_pagelayout('course');
}

/**
 * Calls to the zip creation, and then redirects to the download page.
 *
 * @param int $courseid The id of the current course.
 */
function create_zip_and_redirect_to_download($courseid) {
    global $SESSION;

    $downloader = new downloader($courseid);
    $zipfile = $downloader->create_zip_file();

    $downloadurl = new \moodle_url('/local/usablebackup/download.php', array('file' => $zipfile, 'courseid' => $courseid));

    $SESSION->usablebackup_downloadpermission = true;

    redirect($downloadurl);
}

/**
 * Prints the error page if the user is trying to download the contents from a course he's not enrolled in.
 *
 * @param boolean $nopermission
 */
function print_error_page($nopermission = false) {
    global $OUTPUT;

    echo $OUTPUT->header();
    echo $OUTPUT->navbar();

    echo '<h2>' . get_string('error') . '</h2>';

    $errormessage = ($nopermission) ? get_string('nopermission', 'local_usablebackup') : get_string('notenrolled',
        'local_usablebackup');
    echo $errormessage;

    echo $OUTPUT->footer();
}
