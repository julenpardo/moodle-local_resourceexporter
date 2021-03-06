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
 * File class test.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/resourceexporter/classes/resources/file.php');

use local_resourceexporter\file;

/**
 * File class test.
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourceexporter_file_testcase extends advanced_testcase {

    /**
     * Testing class.
     * @var object
     */
    protected $file;

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
        $this->file = new file();
        $this->filegenerator = new local_resourceexporter_generator($this->getDataGenerator());
    }

    /**
     * Tear down testcase.
     */
    protected function tearDown() {
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
        $class = new ReflectionClass('local_resourceexporter\file');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Test getting db records info of files.
     */
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
            $actualname = $actualresource->resource_name;

            $this->assertEquals($expectedname, $actualname);
        }
    }

    /**
     * Test add file resources to download directory.
     */
    public function test_add_resources_to_directory() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->filegenerator->create_course();

        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Unit testing rules';
        $resources[0]->hidden = false;

        $resources[1] = new stdClass();
        $resources[1]->name = 'Software Engineering is fun';
        $resources[1]->hidden = false;

        $resources[2] = new stdClass();
        $resources[2]->name = 'Eh eh, this is a hidden resource!';
        $resources[2]->hidden = true;

        // We have to create a student who won't be able to see the hidden resources.
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);

        $generatedresources = array();
        $filesrows = array();
        $files = array();

        foreach ($resources as $resource) {
            $resourceandfile = $this->filegenerator->create_resource($course->id, $resource->name, $resource->hidden);

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

        // Now, finally, we can start testing the method.
        $parentdirectory = $CFG->dataroot . '/test_add_resources_to_directory';
        mkdir($parentdirectory);
        $actualpaths = $this->file->add_resources_to_directory($course->id, $parentdirectory);
        $parentdirectory .= '/0_General';

        // We get the actual files of the directory, omitting '.' and '..'.
        $actualfiles = scandir($parentdirectory);
        unset($actualfiles[0]);
        unset($actualfiles[1]);
        $actualfiles = array_values($actualfiles);

        // We get the information of the created visible files, necessary later to get their name and content.
        $sql = "SELECT files.id,
                       course.id AS course_id,
                       course.shortname AS course_shortname,
                       course_modules.id AS course_module_id,
                       files.contextid,
                       files.filename,
                       files.filearea,
                       files.filepath,
                       files.itemid,
                       files.component,
                       resource.name AS resource_name,
                       course_sections.name AS section_name
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
                    AND course.id = ?
                    AND course_modules.visible = 1";
        $expectedresources = $DB->get_records_sql($sql, array($course->id));
        $expectedfiles = array();

        $filestorage = get_file_storage();
        foreach ($expectedresources as $expectedresource) {
            $file = $filestorage->get_file($expectedresource->contextid,
                $expectedresource->component,
                $expectedresource->filearea,
                $expectedresource->itemid,
                $expectedresource->filepath,
                $expectedresource->filename);
            array_push($expectedfiles, $file);
        }

        // If the number of files defined in database and the number of physical files is not the same, or there is not
        // expected file, something is wrong.
        $this->assertFalse(empty($expectedfiles));
        $this->assertEquals(count($expectedfiles), count($actualfiles));

        // We compare the names of the files retrieved from database, and the names of the scanned physical files.
        foreach ($expectedfiles as $index => $expectedfile) {
            $expectedfilename = $expectedfile->get_filename();
            $actualfilename = $actualfiles[$index];

            $this->assertEquals($expectedfilename, $actualfilename);
        }

        // Finally, we compare the files' contents.
        $actualfilecontents = array();

        foreach ($actualfiles as $actualfile) {
            $pathtoactualfile = $parentdirectory . '/' . $actualfile;
            $content = file_get_contents($pathtoactualfile);

            array_push($actualfilecontents, $content);
        }

        // File resource generator creates file contents in the following way:
        // "Test resource x file", starting 'x' from 1.
        $expectedfilecontents = array();

        for ($index = 0; $index < count($expectedfiles); $index++) {
            $content = 'Test resource ' . ($index + 1) . ' file';

            array_push($expectedfilecontents, $content);
        }

        // If the number of files read and the number of defined resources is not the same, something is wrong.
        $expectedfilecount = count($expectedfilecontents);
        $actualfilecount = count($actualfilecontents);

        $this->assertEquals($expectedfilecount, $actualfilecount);

        // If the number of different returned paths is different to the generate files, something is wrong.
        $expectedpaths = array();
        foreach ($resources as $index => $resource) {
            if (!$resource->hidden) {
                $path = $parentdirectory . '/resource' . ($index + 1) . '.txt';
                array_push($expectedpaths, $path);
            }
        }
        $expectedpathscount = count($expectedpaths);
        $actualpathscount = count($actualpaths);

        $this->assertEquals($expectedpathscount, $actualpathscount);

        // And, of course, the paths their self must coincide.
        foreach ($expectedpaths as $index => $expectedpath) {
            $actualpath = $actualpaths[$index];

            $this->assertEquals($expectedpath, $actualpath);
        }

        // Finally, we can compare files' contents.
        foreach ($expectedfilecontents as $index => $expectedfilecontent) {
            $actualfilecontent = $actualfilecontents[$index];

            $this->assertEquals($expectedfilecontent, $actualfilecontent);
        }
    }

}
