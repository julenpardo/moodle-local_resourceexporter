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
 * Download of the resources.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_resourceexporter;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../resources/file.php');
require_once(dirname(__FILE__) . '/../resources/url.php');
require_once(dirname(__FILE__) . '/../resources/folder.php');

use local_resourceexporter\file;
use local_resourceexporter\url;
use local_resourceexporter\folder;

/**
 * Class downloader for the download of the resources.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class downloader {

    /**
     * The course for which the resources will be downloaded.
     * @var int
     */
    protected $courseid;

    /**
     * File handling for the download.
     * @var \local_resourceexporter\file
     */
    protected $file;

    /**
     * Url handling for the download.
     * @var \local_resourceexporter\url
     */
    protected $url;

    /**
     * Folder handling for the download.
     * @var \local_resourceexporter\folder
     */
    protected $folder;

    /**
     * downloader constructor.
     *
     * @param int $courseid The course the download will be created for.
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
        $this->file = new file();
        $this->url = new url();
        $this->folder = new folder();
    }

    /**
     * Creates the zip file. First, creates the parent directory for the contents of the course, and, then, adds the files of each
     * type of resource to that directory, and, finally, adds each added file to the directory to the zip file.
     *
     * First of all, checks if, by any reason, it exists a directory with the name it will have (combination of userid and
     * courseid). And, if exists, deletes it, to avoid possible file overlap of previous generated directories, and the one it
     * will be generated.
     *
     * And, at the end, deletes the generated directory with its contents, not to waste disk space.
     *
     * @throws \Exception If the zip archive cannot be created.
     * @return string The path to the created zip file.
     */
    public function create_zip_file() {
        $pluginrootdir = $this->create_parent_temp_folder_if_not_exists();
        $parentfolder = $this->get_parent_directory_name();

        $fullpathtoparent = $pluginrootdir . '/' . $parentfolder;

        $directoryexists = is_dir($fullpathtoparent);

        if ($directoryexists) {
            $this->rmdir_recursive($fullpathtoparent);
        }

        mkdir($fullpathtoparent);

        $files = $this->file->add_resources_to_directory($this->courseid, $fullpathtoparent);
        $urls = $this->url->add_resources_to_directory($this->courseid, $fullpathtoparent);
        $folders = $this->folder->add_resources_to_directory($this->courseid, $fullpathtoparent);

        $zipfilepath = $fullpathtoparent . '.zip';
        $ziparchive = new \ZipArchive();

        $erroropeningzip = !$ziparchive->open($zipfilepath, \ZipArchive::OVERWRITE);
        if ($erroropeningzip) {
            throw new \Exception('Failed to create zip archive, error object: ' . error_get_last()['message']);
        }

        $allcontentspaths = array_merge($files, $urls, $folders);

        foreach ($allcontentspaths as $contentpath) {
            $filepathincourse = $this->get_file_course_path($contentpath);
            $ziparchive->addFile($contentpath, $filepathincourse);
        }

        $ziparchive->close();

        $this->rmdir_recursive($fullpathtoparent);

        return $zipfilepath;
    }

    /**
     * 'Cuts' file's full path, to get only the path from the course, e.g., section_name/file.txt.
     *
     * @param string $fullpath The full path to the file.
     * @return string The path of the file starting from the course.
     */
    protected function get_file_course_path($fullpath) {
        global $CFG;

        $path = $CFG->tempdir . '/resourceexporter/';
        $path .= $this->get_parent_directory_name() . '/';

        $filecoursepath = str_replace($path, '', $fullpath);

        return $filecoursepath;
    }

    /**
     * Creates the name for the parent directory of the contents; the directory that later will be compressed into a zip file.
     * To create the name, retrieves the course short name, which is probably more suitable for a directory name than the
     * full name.
     *
     * @return string Parent directory name (current course's short name).
     */
    protected function get_parent_directory_name() {
        global $USER;

        $directoryname = $USER->id . '_' . $this->courseid;

        return $directoryname;
    }

    /**
     * Creates, if not exists, a folder for the plugin, in the temp directory in the data root, where all the files generated
     * by the plugin will be located.
     *
     * @return string The full path to the plugin folder in data root.
     */
    protected function create_parent_temp_folder_if_not_exists() {
        global $CFG;

        $parentfolder = $CFG->tempdir . '/resourceexporter';
        $parentfolder = str_replace('//', '/', $parentfolder);

        $directorynotexists = !is_dir($parentfolder);

        if ($directorynotexists) {
            mkdir($parentfolder);
        }

        return $parentfolder;
    }

    /**
     * Removes a directory recursively, i.e., a directory that is not empty. There's no built-in function to remove non-empty
     * directories.
     * Took from: http://stackoverflow.com/questions/7288029/php-delete-directory-that-is-not-empty#7288067
     *
     * @param string $directory The directory to remove.
     */
    protected function rmdir_recursive($directory) {
        foreach (scandir($directory) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            if (is_dir("$directory/$file")) {
                $this->rmdir_recursive("$directory/$file");
            } else {
                unlink("$directory/$file");
            }
        }

        rmdir($directory);
    }

    /**
     * Creates the link that will trigger the download.
     *
     * @return string The link for the download start.
     */
    public function create_download_link() {
        global $CFG;

        $href = $CFG->wwwroot . '/local/resourceexporter/create_zip.php?courseid=' . $this->courseid;
        $link = "<a href='$href'>" . get_string('download', 'local_resourceexporter') . "</a>";

        return $link;
    }

}