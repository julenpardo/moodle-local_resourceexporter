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
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('generator/lib.php');
require_once($CFG->dirroot . '/local/resourceexporter/classes/resources/url.php');

use local_resourceexporter\url;

/**
 *
 * @package    local_resourceexporter
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_resourceexporter_url_testcase extends advanced_testcase {

    protected $url;
    protected $urlgenerator;

    protected function setUp() {
        parent::setUp();
        $this->url = new url();
        $this->urlgenerator = $this->getDataGenerator()->get_plugin_generator('mod_url');
    }

    protected function tearDown() {
        $this->url = null;
        $this->urlgenerator = null;
        parent::tearDown();
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param $name
     * @return ReflectionMethod
     */
    protected static function get_method($name) {
        $class = new ReflectionClass('local_resourceexporter\url');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_get_db_records() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $urls = array();
        $urls[0] = new stdClass();
        $urls[0]->name = 'Moodle general development forum';
        $urls[0]->externalurl = 'https://moodle.org/mod/forum/view.php?id=55';
        $urls[0]->hidden = false;

        $urls[1] = new stdClass();
        $urls[1]->name = 'Moodle Testing and QA forum';
        $urls[1]->externalurl = 'https://moodle.org/course/view.php?id=5';
        $urls[1]->hidden = false;

        $resources = array();
        $resources[0] = new stdClass();
        $resources[0]->name = 'Whatever; this is not an url';

        // We generate the urls...
        $generatedurls = array();

        foreach ($urls as $url) {
            $generatedurl = $this->urlgenerator->create_instance(array('course' => $course->id,
                'name' => $url->name,
                'externalurl' => $url->externalurl));

            array_push($generatedurls, $generatedurl);
        }

        // We generate the resources...
        $filegenerator = new local_resourceexporter_generator($this->getDataGenerator());

        foreach ($resources as $resource) {
            $filegenerator->create_resource($course->id, $resource->name);
        }

        // We get the method by reflection, and we call it.
        $method = self::get_method('get_db_records');
        $actualurls = $method->invokeArgs($this->url, array($course->id));
        // The db rows are returned ordered by the insertion time, so we have to revert the array order not to have trouble later.
        $actualurls = array_reverse($actualurls);

        // If the number of defined urls and the number of urls in database is not the same, something is wrong.
        $expectedurlcount = count($urls);
        $actualurlcount = count($actualurls);

        $this->assertEquals($expectedurlcount, $actualurlcount);

        // Finally, we can compare the properties of the defined urls and the values coming from database.
        foreach ($urls as $index => $expectedurl) {
            $actualurl = $actualurls[$index];

            $this->assertEquals($expectedurl->name, $actualurl->name);
            $this->assertEquals($expectedurl->externalurl, $actualurl->externalurl);
        }
    }

    public function test_add_resources_to_directory() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $urls = array();
        $urls[0] = new stdClass();
        $urls[0]->name = 'Moodle - QA Testing';
        $urls[0]->externalurl = 'https://docs.moodle.org/dev/QA_testing';
        $urls[0]->hidden = false;

        $urls[1] = new stdClass();
        $urls[1]->name = 'Moodle - Writing PHPUnit tests';
        $urls[1]->externalurl = 'https://docs.moodle.org/dev/Writing_PHPUnit_tests';
        $urls[1]->hidden = false;

        $urls[2] = new stdClass();
        $urls[2]->name = 'Eh eh, this is a hidden resource!';
        $urls[2]->externalurl = 'I said that this is a hidden resource!';
        $urls[2]->hidden = true;

        // We have to create a student who won't be able to see the hidden resources.
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);

        // We generate the urls...
        $generatedurls = array();

        foreach ($urls as $url) {
            $generatedurl = $this->urlgenerator->create_instance(array('course' => $course->id,
                'name' => $url->name,
                'externalurl' => $url->externalurl));

            if ($url->hidden) {
                $hiddenurl = new stdClass();
                $hiddenurl->id = $generatedurl->cmid;
                $hiddenurl->visible = 0;

                $DB->update_record('course_modules', $hiddenurl);
            } else {
                array_push($generatedurls, $generatedurl);
            }
        }

        // Now, we can call the testing method.
        $parentdirectory = $CFG->tempdir . '/test_add_resources_to_directory';
        mkdir($parentdirectory);

        $this->url->add_resources_to_directory($course->id, $parentdirectory);
        $parentdirectory .= '/0_General';

        // We get the actual files of the directory, omitting '.' and '..'.
        $actualfiles = scandir($parentdirectory);
        unset($actualfiles[0]);
        unset($actualfiles[1]);
        $actualfiles = array_values($actualfiles);

        // If the number of defined url resources and the number of files created in the specified directory is different,
        // something is wrong.
        $expectedfilecount = 0;

        foreach ($urls as $url) {
            if (!$url->hidden) {
                $expectedfilecount++;
            }
        }

        $actualfilecount = count($actualfiles);

        $this->assertEquals($expectedfilecount, $actualfilecount);

        // We save the contents of the generated files...
        $actualfilescontents = array();

        foreach ($actualfiles as $actualfile) {
            $path = $parentdirectory . '/' . $actualfile;
            $content = file_get_contents($path);

            array_push($actualfilescontents, $content);
        }

        // Finally, we can check the created files' names and contents.
        foreach ($urls as $index => $url) {
            if (!$url->hidden) {
                $expectedname = $url->name . '.txt';
                $expectedcontent = $url->externalurl;

                $actualname = $actualfiles[$index];
                $actualcontent = $actualfilescontents[$index];

                $this->assertEquals($expectedname, $actualname);
                $this->assertEquals($expectedcontent, $actualcontent);
            }
        }
    }

}
