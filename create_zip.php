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
require_once('classes/downloader/downloader.php');

defined('MOODLE_INTERNAL') || die();

global $CFG;

use local_usablebackup\downloader;

require_login();
$context = context_system::instance();

$courseid = required_param('courseid', PARAM_INT);

$downloader = new downloader($courseid);
$zipfile = $downloader->create_zip_file();

$downloadurl = new \moodle_url('/local/usablebackup/download.php', array('file' => $zipfile));

redirect($downloadurl);
