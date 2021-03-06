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
 * Resource exporter class tests.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/resourceexporter/classes/downloader/downloader.php');

use local_resourceexporter\downloader;

/**
 * Resource exporter class tests.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourceexporter_downloader_testcase extends advanced_testcase {

    /**
     * URL data generator.
     * @var object
     */
    protected $urlgenerator;

    /**
     * File generator.
     * @var object
     */
    protected $filegenerator;

    /**
     * Set up testcase.
     */
    protected function setUp() {
        parent::setUp();
        $this->urlgenerator = $this->getDataGenerator()->get_plugin_generator('mod_url');
        $this->filegenerator = new local_resourceexporter_generator($this->getDataGenerator());
    }

    /**
     * Tear down testcase.
     */
    protected function tearDown() {
        $this->urlgenerator = null;
        $this->filegenerator = null;
        parent::tearDown();
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param string $name Method name.
     * @return ReflectionMethod
     */
    protected static function get_method($name) {
        $class = new ReflectionClass('local_resourceexporter\downloader');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Tests the creation of zip file.
     */
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

        $pathtofolder = $CFG->dataroot . '/temp/resourceexporter/' . $user->id . '_' . $course->id;
        $pathtofile = $pathtofolder . '.zip';

        // We create the folder to make the function delete it calling to rmdir_recursive, to cover it.
        mkdir($pathtofolder, 0777, true);

        // We get the method by reflection, and we call it.
        $method = self::get_method('create_zip_file');
        $actualzipfile = $method->invokeArgs($downloader, array());

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

            $this->assertEquals('0_General/' . $expected->filename, $actual->filename);
            $this->assertEquals($expected->filecontent, $actual->filecontent);
        }
    }

    /**
     * Tests the parent directory name construction.
     */
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

    /**
     * Tests the created download link.
     */
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

        $href = $CFG->wwwroot . '/local/resourceexporter/create_zip.php?courseid=' . $course->id;
        $expected = "<a href='$href'>" . get_string('download', 'local_resourceexporter') . '</a>';

        $actual = $downloader->create_download_link();

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the construction of path to resource.
     */
    public function test_get_file_course_path() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 5); // 5 is student role id.

        $this->setUser($user);

        $downloader = new downloader($course->id);

        $fullpath = $CFG->tempdir . '/resourceexporter/' . $user->id . '_' . $course->id . '/sectiondir/resource.txt';

        // We get the method by reflection.
        $method = self::get_method('get_file_course_path');

        $expected = 'sectiondir/resource.txt';
        $actual = $method->invokeArgs($downloader, array($fullpath));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the creation of temp folder where the zips will be stored.
     */
    public function test_create_parent_temp_folder_if_not_exists() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $downloader = new downloader($course->id);

        $expected = $CFG->tempdir . '/resourceexporter';

        // We get the method by reflection, and we call it.
        $method = self::get_method('create_parent_temp_folder_if_not_exists');
        $actual = $method->invokeArgs($downloader, array());

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the recursive deletion of directory.
     */
    public function test_rmdir_recursive() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        // We create all the necessary stuff: course, nested directories...
        $course = $this->getDataGenerator()->create_course();
        $downloader = new downloader($course->id);

        $rootdirectory = $CFG->tempdir . '/test_rmdir_recursive';
        $childdirectory = $rootdirectory . '/child1';
        $childchilddirectory = $childdirectory . '/child2'; // Yes, quite silly name.

        mkdir($rootdirectory);
        mkdir($childdirectory);
        mkdir($childchilddirectory);

        // We check that we have created the directories correctly...
        $childchildexists = is_dir($childchilddirectory);
        $childexists = is_dir($childdirectory);
        $rootexists = is_dir($rootdirectory);

        $this->assertTrue($childchildexists);
        $this->assertTrue($childexists);
        $this->assertTrue($rootexists);

        // We get the method by reflection, and we call the testing method.
        $method = self::get_method('rmdir_recursive');
        $method->invokeArgs($downloader, array($rootdirectory));

        // If everything is okay, any created directory must exist.
        $childchildexists = is_dir($childchilddirectory);
        $childexists = is_dir($childdirectory);
        $rootexists = is_dir($rootdirectory);

        $this->assertFalse($childchildexists);
        $this->assertFalse($childexists);
        $this->assertFalse($rootexists);
    }

}
