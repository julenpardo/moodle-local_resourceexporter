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
require_once($CFG->dirroot . '/local/usablebackup/classes/resources/file.php');

use local_usablebackup\file;

/**
 * local_usablebackup data generator class.
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_file_testcase extends advanced_testcase {

    protected $file;
    protected $filegenerator;

    protected function setUp() {
        parent::setUp();
        $this->file = new file();
        $this->filegenerator = new local_usablebackup_generator($this->getDataGenerator());
    }

    protected function tearDown() {
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
        $class = new ReflectionClass('local_usablebackup\file');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_get_db_records() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $method = self::get_method('get_db_records');

        $urlgenerator = $this->getDataGenerator()->get_plugin_generator('mod_url');

        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Software Engineering notes';

        $resources[1] = new stdClass();
        $resources[1]->name = 'How to join Moodle tables without dying in the attempt';

        $url = new stdClass();
        $url->name = 'Whatever; this is not a file';

        $course = $this->filegenerator->create_course();

        $urlgenerator->create_instance(array('course' => $course->id));

        $generatedresources = array();
        $filesrows = array();
        $files = array();

        foreach ($resources as $resource) {
            $resourceandfile = $this->filegenerator->create_resource($course->id, $resource->name);

            array_push($generatedresources, $resourceandfile['resource']);
            array_push($filesrows, $resourceandfile['filerow']);
            array_push($files, $resourceandfile['file']);
        }

        // If in the database, in the 'resource' table, the number of rows is not the same as the defined resources,
        // something is wrong.
        $expectedresourcecount = count($resources);
        $actualresourcecount = $DB->count_records('resource');

        $this->assertEquals($expectedresourcecount, $actualresourcecount);

        // If the number of files for the course is not the same as the defined resources, something is wrong.
        $expectedfilecount = count($resources);
        $actualfilecount = $DB->count_records_sql("SELECT count(files.*)
                                                    FROM {files} files
                                                    INNER JOIN {context} context
                                                        ON files.contextid = context.id
                                                        AND context.contextlevel = 70
                                                    INNER JOIN {course_modules} course_modules
                                                        ON context.instanceid = course_modules.id
                                                    INNER JOIN {course} course
                                                        ON course_modules.course = course.id
                                                    INNER JOIN {resource} resource
                                                        ON resource.course = course.id
                                                        AND resource.id = course_modules.instance
                                                    INNER JOIN {course_sections} course_sections
                                                        ON course_sections.id = course_modules.section

                                                    WHERE filename <> '.'
                                                        AND course.id = ?", array($course->id));

        $this->assertEquals($expectedfilecount, $actualfilecount);

        // Finally, we can start testing the method.
        $actualresources = $method->invokeArgs($this->file, array($course->id));

        // If the returned number of resources is not the same as the defined resource number, something is wrong.
        $expectedresources = count($resources);
        $actualresourcescount = count($actualresources);

        $this->assertEquals($expectedresources, $actualresourcescount);

        // Finally, the names of the resources.
        foreach ($actualresources as $index => $actualresource) {
            $expectedname = $resources[$index]->name;
            $actualname = $actualresource->name;

            $this->assertEquals($expectedname, $actualname);
        }
    }

}
