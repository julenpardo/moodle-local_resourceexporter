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
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/usablebackup/classes/downloader/downloader.php');

use local_usablebackup\downloader;

/**
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_downloader_testcase extends advanced_testcase {

    protected $urlgenerator;
    protected $filegenerator;

    protected function setUp() {
        parent::setUp();
        $this->urlgenerator = $this->getDataGenerator()->get_plugin_generator('mod_url');
        $this->filegenerator = new local_usablebackup_generator($this->getDataGenerator());
    }

    protected function tearDown() {
        $this->urlgenerator = null;
        $this->filegenerator = null;
        parent::tearDown();
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param $name
     * @return ReflectionMethod
     */
    protected static function get_method($name) {
        $class = new ReflectionClass('local_usablebackup\downloader');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_create_zip_file() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        // First step is to create the course and user, and instantiate the testing class.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 5); // 5 is student role id.

        $this->setUser($user);

        $downloader = new downloader($course->id);

        // Now, we generate the resources that will be downloaded.
        $urls = array();
        $urls[0] = new stdClass();
        $urls[0]->name = 'PHPUnit - The PHP testing framework';
        $urls[0]->externalurl = 'https://phpunit.de/';

        $files = array();
        $files[0] = new stdClass();
        $files[0]->name = 'Unit testing rules';

        foreach ($urls as $url) {
            $this->urlgenerator->create_instance(array('course' => $course->id,
                'name' => $url->name,
                'externalurl' => $url->externalurl));
        }

        foreach ($files as $file) {
            $this->filegenerator->create_resource($course->id, $file->name);
        }

        // We get the method by reflection, and we call it.
        $method = self::get_method('create_zip_file');
        $actualzipfile = $method->invokeArgs($downloader, array());

        $zipfilename = basename($actualzipfile);
        $pathtofile = $CFG->dataroot . '/temp/usablebackup/' . $user->id . '_' . $course->id . '.zip';

        // We set the expected values.
        $expecteds = array();
        $expecteds[0] = new stdClass();
        $expecteds[0]->filename = 'resource1.txt';
        $expecteds[0]->filecontent = 'Test resource 1 file';

        $expecteds[1] = new stdClass();
        $expecteds[1]->filename = 'PHPUnit - The PHP testing framework.txt';
        $expecteds[1]->filecontent = 'https://phpunit.de/';

        // We get the actual values, from the generated zip file, and extracting the data of every files in it.
        $ziparchive = new ZipArchive();
        $ziparchive->open($pathtofile);

        $actuals = array();

        for ($index = 0; $index < $ziparchive->numFiles; $index++) {
            $actuals[$index] = new stdClass();

            $filename = $ziparchive->getNameIndex($index);
            $filecontent = $ziparchive->getFromName($filename);

            $actuals[$index]->filename = $filename;
            $actuals[$index]->filecontent = $filecontent;
        }

        // If the number of files in the zip and the number of defined resources is different, something is wrong.
        $expectedfilecount = count($expecteds);
        $actualfilecount = count($actuals);

        $this->assertEquals($expectedfilecount, $actualfilecount);

        // Finally, we can compare files names and contents.
        foreach ($expecteds as $index => $expected) {
            $actual = $actuals[$index];

            $this->assertEquals($expected->filename, $actual->filename);
            $this->assertEquals($expected->filecontent, $actual->filecontent);
        }
    }

    public function test_get_parent_directory_name() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 5); // 5 is student role id.

        $this->setUser($user);

        // We instantiate the testing class...
        $downloader = new downloader($course->id);

        // We get the protected method by reflection, and we call it.
        $method = self::get_method('get_parent_directory_name');

        $actual = $method->invokeArgs($downloader, array());
        $expected = $user->id . '_' . $course->id;

        $this->assertEquals($expected, $actual);
    }

    public function test_create_download_link() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 5); // 5 is student role id.

        $this->setUser($user);

        // We instantiate the testing class...
        $downloader = new downloader($course->id);

        $href = $CFG->wwwroot . '/local/usablebackup/create_zip.php?courseid=' . $course->id;
        $expected = "<a href='$href'>" . get_string('download', 'local_usablebackup') . '</a>';

        $actual = $downloader->create_download_link();

        $this->assertEquals($expected, $actual);
    }

    public function test_get_file_course_path() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 5); // 5 is student role id.

        $this->setUser($user);

        $downloader = new downloader($course->id);

        $fullpath = $CFG->tempdir . '/usablebackup/' . $user->id . '_' . $course->id . '/sectiondir/resource.txt';

        // We get the method by reflection.
        $method = self::get_method('get_file_course_path');

        $expected = 'sectiondir/resource.txt';
        $actual = $method->invokeArgs($downloader, array($fullpath));

        $this->assertEquals($expected, $actual);
    }

}
