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
 * File handler trait test.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/resourceexporter/classes/resources/file_handler.php');

use local_resourceexporter\file_handler;

/**
 * File handler trait test.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourceexporter_file_handler_testcase extends advanced_testcase {

    use file_handler;

    /**
     * Data genearator.
     * @var object
     */
    protected $generator;

    /**
     * Set up testcase.
     */
    protected function setUp() {
        parent::setUp();
        $this->generator = new local_resourceexporter_generator($this->getDataGenerator());
    }

    /**
     * Tear down testcase.
     */
    protected function tearDown() {
        $this->generator = null;
        parent::tearDown();
    }

    /**
     * Test the method that returns the file, passing the resource information.
     */
    public function test_get_file_from_resource_info() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $resource = new stdClass();
        $resource->name = 'Apparently simple functions must also be tested';

        $resourceandfile = $this->generator->create_resource($course->id, $resource->name);
        $filerow = $resourceandfile['filerow'];

        // We call the testing trait method.
        $actualfile = $this->get_file_from_resource_info($filerow);

        $reflectionfile = new ReflectionClass('stored_file');
        $reflectionfilerecord = $reflectionfile->getProperty('file_record');
        $reflectionfilerecord->setAccessible(true);

        $actualfile = $reflectionfilerecord->getValue($actualfile);

        // We construct the expected object. With the file content hash and the file name, should be enough to assert that the
        // method works correctly.
        $expectedfile = new stdClass();
        $expectedfile->contenthash = sha1('Test resource 1 file');
        $expectedfile->filename = 'resource1.txt';

        $this->assertEquals($expectedfile->contenthash, $actualfile->contenthash);
        $this->assertEquals($expectedfile->filename, $actualfile->filename);
    }
}