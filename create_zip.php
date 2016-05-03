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
 * Page that creates the zip before redirecting to the download.
 *
 * The file name to download is passed through the SESSION object, instead of through HTTP parameter, to prevent evil intentions.
 * Another property in SESSION object is set to mark that the checks have been passed (login, enrolled in the course), to have
 * always a value that we know in advance will be assigned (true/false; the file object is more susceptible for possible errors).
 *
 * If the requesting user has no permissions, or he tries to access directly to the download page, he will be redirected to this
 * page, to show the corresponding error message.
 *
 * @package    local_usablebackup
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('classes/downloader/downloader.php');

defined('MOODLE_INTERNAL') || die();

global $CFG, $SESSION;

require_once($CFG->libdir.'/adminlib.php');

use local_usablebackup\downloader;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$nopermission = optional_param('nopermission', 0, PARAM_INT);
$coursecontext = context_course::instance($courseid);

init_page();

if ($nopermission) {
    print_error_page($nopermission);
} else if (is_enrolled($coursecontext) || is_admin($coursecontext)) {
    create_zip_and_redirect_to_download($courseid);
} else {
    print_error_page();
}

/**
 * Initializes the page with: context, url, title.
 * The context is set to system because, in case of having to show the error message for those trying to download resources from
 * a course they are not enrolled in, setting the context of a course where the user is not enrolled makes no sense.
 */
function init_page() {
    global $PAGE;

    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_url('/local/usablebackup/create_zip.php');
    $PAGE->set_title(get_string('createzip_title', 'local_usablebackup'));
}

/**
 * Calls to the zip creation, and then redirects to the download page. Passes the zip file name as SESSION object property,
 * since it's not supposed to be writable by the client. Another property in SESSION object is set to mark that the checks have
 * been passed (login, enrolled in the course), to have always a value that we know in advance will be assigned (true/false;
 * the file object is more susceptible for possible errors).
 *
 * @param int $courseid The id of the current course.
 */
function create_zip_and_redirect_to_download($courseid) {
    global $SESSION;

    $downloader = new downloader($courseid);
    $zipfile = $downloader->create_zip_file();

    $filename = basename($zipfile);
    $downloadurl = new \moodle_url('/local/usablebackup/download.php', array('courseid' => $courseid));

    $SESSION->usablebackup_filename = $filename;
    $SESSION->usablebackup_downloadpermission = true;

    redirect($downloadurl);
}

/**
 * Prints the error page if the user is trying to download the contents from a course he's not enrolled in, or if he tries to
 * access the download page directly.
 *
 * @param boolean $nopermission If the error is because of the user has no permission, and not because it has accessed directly
 * to the download page.
 */
function print_error_page($nopermission = false) {
    global $OUTPUT, $PAGE;

    $home = new \moodle_url('/');
    $errormessage = ($nopermission) ? get_string('nopermission', 'local_usablebackup') : get_string('notenrolled',
        'local_usablebackup');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('error'));
    echo $OUTPUT->navbar();

    echo $OUTPUT->box_start('generalbox', 'notice');
    echo html_writer::tag('p', $errormessage);
    echo $OUTPUT->single_button($home, get_string('sitehome'), 'get');
    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();
}
