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
 * local_usablebackup data generator.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('generator/lib.php');

/**
 * local_usablebackup data generator class.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_file_testcase extends advanced_testcase {

    protected $filegenerator;

    protected function setUp() {
        parent::setUp();
        $this->filegenerator = new local_usablebackup_generator($this->getDataGenerator());
    }

    protected function tearDown() {
        $this->filegenerator = null;
        parent::tearDown();
    }

    public function test_get_db_records() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Software Engineering notes';

        $resources[1] = new stdClass();
        $resources[1]->name = 'How to join Moodle tables without dying in the attempt';

        $course = $this->filegenerator->create_course();

        $generatedresources = array();
        $filesrows = array();
        $files = array();

        foreach ($resources as $resource) {
            $resourceandfile = $this->filegenerator->create_resource($course->id, $resource->name);

            array_push($generatedresources, $resourceandfile['resource']);
            array_push($filesrows, $resourceandfile['filerow']);
            array_push($files, $resourceandfile['file']);
        }

        $expectedresourcecount = count($resources);
        $actualresourcecount = $DB->count_records('resource');

        // If in the database, in the 'resource' table, the number of rows is not the same as the defined resources,
        // something is wrong.
        $this->assertEquals($expectedresourcecount, $actualresourcecount);

        foreach ($generatedresources as $index => $generatedresource) {
            $this->assertEquals($resources[$index]->name, $generatedresource->name);
        }
    }
}
