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
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('classes/downloader/downloader.php');

defined('MOODLE_INTERNAL') || die();

global $CFG, $SESSION;

require_once($CFG->libdir.'/adminlib.php');

use local_resourceexporter\downloader;

require_login();

$courseid = required_param('courseid', PARAM_INT);

local_resourceexporter_redirect_to_homepage_if_invalid_course($courseid);

$nopermission = optional_param('nopermission', 0, PARAM_INT);
$coursecontext = context_course::instance($courseid);

local_resourceexporter_init_page();

if ($nopermission) {
    local_resourceexporter_print_error_page($nopermission);
} else if (is_enrolled($coursecontext) || is_admin($coursecontext)) {
    local_resourceexporter_create_zip_and_redirect_to_download($courseid);
} else {
    local_resourceexporter_print_error_page();
}

/**
 * Initializes the page with: context, url, title.
 * The context is set to system because, in case of having to show the error message for those trying to download resources from
 * a course they are not enrolled in, setting the context of a course where the user is not enrolled makes no sense.
 */
function local_resourceexporter_init_page() {
    global $PAGE;

    $context = context_system::instance();
    $PAGE->set_context($context);
    $PAGE->set_url('/local/resourceexporter/create_zip.php');
    $PAGE->set_title(get_string('createzip_title', 'local_resourceexporter'));
}

/**
 * Calls to the zip creation, and then redirects to the download page. Passes the zip file name as SESSION object property,
 * since it's not supposed to be writable by the client. Another property in SESSION object is set to mark that the checks have
 * been passed (login, enrolled in the course), to have always a value that we know in advance will be assigned (true/false;
 * the file object is more susceptible for possible errors).
 *
 * If someone is trying to download contents from course=1, that is, the home page, it will be redirected to the home page, since
 * the no contents can be downloaded (and the created zip file will be marked as corrupt).
 *
 * @param int $courseid The id of the current course.
 */
function local_resourceexporter_create_zip_and_redirect_to_download($courseid) {
    global $SESSION;

    $downloader = new downloader($courseid);
    $zipfile = $downloader->create_zip_file();

    $filename = basename($zipfile);
    $downloadurl = new \moodle_url('/local/resourceexporter/download.php', array('courseid' => $courseid));

    $SESSION->resourceexporter_filename = $filename;
    $SESSION->resourceexporter_downloadpermission = true;

    redirect($downloadurl);
}

/**
 * Redirects to homepage if the course specified is invalid, i.e., if it is the homepage itself (it doesn't have resources to
 * download), or if the course doesn't exist.
 *
 * @param int $courseid The course to check if is valid or not.
 */
function local_resourceexporter_redirect_to_homepage_if_invalid_course($courseid) {
    global $DB;

    $invalidcourse = $courseid === 1;

    if (!$invalidcourse) {
        $invalidcourse = !$DB->record_exists('course', array('id' => $courseid));
    }

    if ($invalidcourse) {
        $homepage = new \moodle_url('/');
        redirect($homepage);
    }
}

/**
 * Prints the error page if the user is trying to download the contents from a course he's not enrolled in, or if he tries to
 * access the download page directly.
 *
 * @param boolean $nopermission If the error is because of the user has no permission, and not because it has accessed directly
 * to the download page.
 */
function local_resourceexporter_print_error_page($nopermission = false) {
    global $OUTPUT;

    $home = new \moodle_url('/');
    $errormessage = ($nopermission) ? get_string('nopermission', 'local_resourceexporter') : get_string('notenrolled',
        'local_resourceexporter');

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('error'));
    echo $OUTPUT->navbar();

    echo $OUTPUT->box_start('generalbox', 'notice');
    echo html_writer::tag('p', $errormessage);
    echo $OUTPUT->single_button($home, get_string('sitehome'), 'get');
    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();
}
