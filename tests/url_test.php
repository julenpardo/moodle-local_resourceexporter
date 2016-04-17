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
require_once($CFG->dirroot . '/local/usablebackup/classes/resources/url.php');

use local_usablebackup\url;

/**
 *
 * @package    local_usablebackup
 * @category   test
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_usablebackup_url_testcase extends advanced_testcase {

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
        $class = new ReflectionClass('local_usablebackup\url');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_get_db_records() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $urls = array();
        $urls[0] = new stdClass();
        $urls[0]->name = 'Moodle general development forum';
        $urls[0]->externalurl = 'https://moodle.org/mod/forum/view.php?id=55';
        $urls[1] = new stdClass();
        $urls[1]->name = 'Moodle Testing and QA forum';
        $urls[1]->externalurl = 'https://moodle.org/course/view.php?id=5';

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
        $filegenerator = new local_usablebackup_generator($this->getDataGenerator());

        foreach ($resources as $resource) {
            $filegenerator->create_resource($course->id, $resource->name);
        }

        $actualurls = $DB->get_records('url');
        $actualurls = array_values($actualurls);

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

    }

}
