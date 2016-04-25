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
 * local_usablebackup folder test.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/usablebackup/classes/resources/folder.php');

use local_usablebackup\folder;

/**
 * Class local_usablebackup_folder_testcase
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_folder_testcase extends advanced_testcase {

    protected $folder;
    protected $filegenerator;

    protected function setUp() {
        parent::setUp();
        $this->folder = new folder();
        $this->filegenerator = new local_usablebackup_generator($this->getDataGenerator());
    }

    protected function tearDown() {
        $this->folder = null;
        $this->filegenerator = null;
        parent::tearDown();
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param string $name Method name.
     * @return ReflectionMethod The method object.
     */
    protected static function get_method($name) {
        $class = new ReflectionClass('local_usablebackup\folder');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_get_db_records() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $method = self::get_method('get_db_records');

        $foldergenerator = $this->getDataGenerator()->get_plugin_generator('mod_folder');

        $folder = 'my folder';

        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Software Engineering notes';

        $resources[1] = new stdClass();
        $resources[1]->name = 'How to join Moodle tables without dying in the attempt';

        $file = new stdClass();
        $file->name = "This is not in the folder so it shouldn't be taken into account";

        $course = $this->getDataGenerator()->create_course();

        $folder = $foldergenerator->create_instance(array('course' => $course->id, 'name' => $folder));

        $generatedresources = array();
        $filesrows = array();
        $files = array();

        foreach ($resources as $resource) {
            $resourceandfile = $this->filegenerator->create_resource_in_folder($course->id, $resource->name, $folder->id);

            array_push($generatedresources, $resourceandfile['resource']);
            array_push($filesrows, $resourceandfile['filerow']);
            array_push($files, $resourceandfile['file']);
        }

        // Finally, we can call the testing method.
        $actualfilesinfolder = $method->invokeArgs($this->folder, array($course->id));

        // If the number of elements received and the number of resources for folder defined is different, something is wrong.
        $expectedfilecount = count($resources);
        $actualfilecount = count($actualfilesinfolder);

        $this->assertEquals($expectedfilecount, $actualfilecount);
    }

}
