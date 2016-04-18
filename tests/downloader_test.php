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

        // First step is to create the course, and instantiate the testing class.
        $courseshortname = 'testingcourse';

        $course = $this->getDataGenerator()->create_course(array('shortname' => $courseshortname));

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
        $method->invokeArgs($downloader, array());

        // We set the expected SHA-1 hash value of the zip file...
        $expectedfilehash = '5bd159e24749c1218cc927ce224b3bb773eb9b32';

        // We calculate the SHA-1 hash of the actual file created by the testing method...
        $pathtofile = $CFG->dataroot . '/' . $courseshortname . '.zip';
        $actualfilehash = sha1_file($pathtofile);

        // Finally, we can make the assertion.
        $this->assertEquals($expectedfilehash, $actualfilehash);
    }

    public function test_get_parent_directory_name() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $courseshortname = 'Testing Moodle';

        $course = $this->getDataGenerator()->create_course(array('shortname' => $courseshortname));

        // We instantiate the testing class...
        $downloader = new downloader($course->id);

        // We get the protected method by reflection, and we call it.
        $method = self::get_method('get_parent_directory_name');

        $actual = $method->invokeArgs($downloader, array());
        $expected = strtolower($courseshortname);

        $this->assertEquals($expected, $actual);
    }

    public function test_create_download_link() {

    }

}
