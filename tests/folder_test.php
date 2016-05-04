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
 * local_resourceexporter folder test.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/resourceexporter/classes/resources/folder.php');

use local_resourceexporter\folder;

/**
 * Class local_resourceexporter_folder_testcase
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_resourceexporter_folder_testcase extends advanced_testcase {

    protected $folder;
    protected $filegenerator;

    protected function setUp() {
        parent::setUp();
        $this->folder = new folder();
        $this->filegenerator = new local_resourceexporter_generator($this->getDataGenerator());
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
        $class = new ReflectionClass('local_resourceexporter\folder');
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
        $resources[0]->filename = 'resource1.txt';

        $resources[1] = new stdClass();
        $resources[1]->name = 'How to join Moodle tables without dying in the attempt';
        $resources[1]->filename = 'resource2.txt';

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

        // We sort the actual values by the filename, to allow later make the assertion in the loop.
        usort($actualfilesinfolder, function($a, $b) {
            return strcmp($a->filename, $b->filename);
        });

        // Finally, we can test the resources their self.
        foreach ($actualfilesinfolder as $index => $actualfile) {
            $expectedfoldername = $folder->name;
            $actualfoldername = $actualfile->folder_name;

            $this->assertEquals($expectedfoldername, $actualfoldername);

            $expectedfilename = $resources[$index]->filename;
            $actualfilename = $actualfile->filename;

            $this->assertEquals($expectedfilename, $actualfilename);
        }
    }

    public function test_add_resources_to_directory() {
        global $DB, $CFG;

        $this->markTestSkipped("This is not testeable since a way to generate files in folders natively, with the data generator,
            is found. Because, the 'create_resource_in_folder' updates manually the files' table, in order to make the query of
            'get_db_records' return the files in folders, but then the file API is not able to retrieve the files with those
            modified values.");

        $this->resetAfterTest();
        $this->setAdminUser();

        $folder = 'my_folder';
        $course = $this->getDataGenerator()->create_course();
        $foldergenerator = $this->getDataGenerator()->get_plugin_generator('mod_folder');
        $folder = $foldergenerator->create_instance(array('course' => $course->id, 'name' => $folder));

        // We made all the necessary things to create the resources within the folder...
        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Unit testing rules';
        $resources[0]->filename = 'resource1.txt';

        $resources[1] = new stdClass();
        $resources[1]->name = 'Software Engineering is fun';
        $resources[1]->filename = 'resource2.txt';

        $generatedresources = array();
        $filesrows = array();
        $files = array();

        foreach ($resources as $resource) {
            $resourceandfile = $this->filegenerator->create_resource_in_folder($course->id, $resource->name, $folder->id);

            array_push($generatedresources, $resourceandfile['resource']);
            array_push($filesrows, $resourceandfile['filerow']);
            array_push($files, $resourceandfile['file']);
        }

        $parentdirectory = $CFG->tempdir . '/test_add_resources_to_directory';

        // Finally, we can call the testing methods.
        $actualvalues = $this->folder->add_resources_to_directory($course->id, $parentdirectory);
    }

}
