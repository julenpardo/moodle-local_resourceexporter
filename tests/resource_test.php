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
 * Resource class tests.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/resourceexporter/classes/resources/resource.php');

use local_resourceexporter\resource;

/**
 * This dirty workaround is required to test the implemented methods of the abstract class.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class concrete_resource extends resource {
    public function add_resources_to_directory($courseid, $parentdirectory) {
        null;
    }

    protected function get_db_records($courseid) {
        null;
    }

    protected function create_section_if_dir_not_exists($parentdirectory, $sectionname) {
        return $this->create_section_dir_if_not_exists($parentdirectory, $sectionname);
    }
}

/**
 * Resource class tests.
 *
 * @package    local_resourceexporter
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_resourceexporter_resource_testcase extends advanced_testcase {

    /**
     * Testing class.
     * @var object
     */
    protected $resource;

    /**
     * Set up testcase.
     */
    protected function setUp() {
        parent::setUp();
        $this->resource = new concrete_resource();
    }

    /**
     * Tear down testcase.
     */
    protected function tearDown() {
        parent::tearDown();
        $this->resource = null;
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param string $name Method name.
     * @return ReflectionMethod
     */
    protected static function get_methods($name) {
        $class = new ReflectionClass('local_resourceexporter\resource');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Test the creation of section directory, having an empty section.
     */
    public function test_create_section_dir_if_not_exists_emtpy_section() {
        global $CFG;

        $method = self::get_methods('create_section_dir_if_not_exists');

        $parentdirectory = $CFG->dataroot;
        $sectionname = '';

        $expected = $parentdirectory;

        $actual = $method->invokeArgs($this->resource, array($parentdirectory, $sectionname));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the creation of section directory, when the directory does not exist.
     */
    public function test_create_section_dir_if_not_exists_dir_not_exists() {
        global $CFG;

        $method = self::get_methods('create_section_dir_if_not_exists');

        $parentdirectory = $CFG->dataroot;
        $sectionname = 'section';

        $expected = $parentdirectory . '/' . $sectionname;

        $actual = $method->invokeArgs($this->resource, array($parentdirectory, $sectionname));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the creation of section directory, when the directory already exists.
     */
    public function test_create_section_dir_if_not_exists_dir_exists() {
        global $CFG;

        $method = self::get_methods('create_section_dir_if_not_exists');

        $parentdirectory = $CFG->dataroot;
        $sectionname = 'section';

        $expected = $parentdirectory . '/' . $sectionname;

        mkdir($expected);

        $actual = $method->invokeArgs($this->resource, array($parentdirectory, $sectionname));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the file and directory name clean, when this is null.
     */
    public function test_clean_file_and_directory_names_null() {
        $name = null;

        // We get the protected method by reflection.
        $method = self::get_methods('clean_file_and_directory_names');

        $expected = '';
        $actual = $method->invokeArgs($this->resource, array($name));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the file and directory name clean, when only passing allowed chars.
     */
    public function test_clean_file_and_directory_names_allowed_chars() {
        $name = 'String with non problematic characters.';

        // We get the protected method by reflection.
        $method = self::get_methods('clean_file_and_directory_names');

        $expected = 'String with non problematic characters.';
        $actual = $method->invokeArgs($this->resource, array($name));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the file and directory name clean, when passing string with forbidden chars.
     */
    public function test_clean_file_and_directory_names_forbidden_chars() {
        $name = 'String:with/some/problematic?characters';

        // We get the protected method by reflection.
        $method = self::get_methods('clean_file_and_directory_names');

        $expected = 'String-with-some-problematic-characters';
        $actual = $method->invokeArgs($this->resource, array($name));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the file and directory name clean, when passing non-ascii chars.
     */
    public function test_clean_file_and_directory_names_non_ascii_chars() {
        $name = 'String con caracteres problemáticos';

        // We get the protected method by reflection.
        $method = self::get_methods('clean_file_and_directory_names');

        $expected = 'String con caracteres problematicos';
        $actual = $method->invokeArgs($this->resource, array($name));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the visibility of the module for the user, when this is visible.
     */
    public function test_is_module_visible_for_user_visible() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // We generate all the required stuff: course, a resource (url, e.g.), a user enrolled in the course.
        $course = $this->getDataGenerator()->create_course();
        $url = $this->getDataGenerator()->get_plugin_generator('mod_url')->create_instance(array('course' => $course->id));
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);

        $visibleurlmodule = new stdClass();
        $visibleurlmodule->id = $url->cmid;
        $visibleurlmodule->visible = 1;

        $DB->update_record('course_modules', $visibleurlmodule);

        // We get the testing method by reflection.
        $method = self::get_methods('is_module_visible_for_user');

        // And, finally, we test the method.
        $actual = $method->invokeArgs($this->resource, array($course->id, $url->cmid));

        $this->assertTrue($actual);
    }

    /**
     * Test the visibility of the module for the user, when this is hidden.
     */
    public function test_is_module_visible_for_user_hidden() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // We generate all the required stuff: course, a resource (url, e.g.), a user enrolled in the course.
        $course = $this->getDataGenerator()->create_course();
        $url = $this->getDataGenerator()->get_plugin_generator('mod_url')->create_instance(array('course' => $course->id));
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);

        $visibleurlmodule = new stdClass();
        $visibleurlmodule->id = $url->cmid;
        $visibleurlmodule->visible = 0;

        $DB->update_record('course_modules', $visibleurlmodule);

        // We get the testing method by reflection.
        $method = self::get_methods('is_module_visible_for_user');

        // And, finally, we test the method.
        $actual = $method->invokeArgs($this->resource, array($course->id, $url->cmid));

        $this->assertFalse($actual);
    }

    /**
     * Test the visibility of the module for the user, when this is restricted for groups.
     */
    public function test_is_module_visible_for_user_group_restriction() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->markTestSkipped("Don't know why, but setting the module available only for the created group, doesn't affect to the
            availability of the resource for the user.");

        // We generate all the required stuff: course, a resource (url, e.g.), a user enrolled in the course, a group.
        $course = $this->getDataGenerator()->create_course();
        $url = $this->getDataGenerator()->get_plugin_generator('mod_url')->create_instance(array('course' => $course->id));
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);
        $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id));

        $visibleurlmodule = new stdClass();
        $visibleurlmodule->id = $url->cmid;
        $visibleurlmodule->visible = 1;
        // The following attribute value defines that the resource will be visible only for those students that are enrolled in
        // generated group before. Based on an example looking at the database.
        $visibleurlmodule->availability = '{"op":"&","c":[{"type":"group","id":' . $group->id . '}],"showc":[true]}';

        $DB->update_record('course_modules', $visibleurlmodule);

        // We get the testing method by reflection.
        $method = self::get_methods('is_module_visible_for_user');

        // And, finally, we test the method.
        $actual = $method->invokeArgs($this->resource, array($course->id, $url->cmid));

        $this->assertFalse($actual);
    }

    /**
     * Test the visibility of the module for the user, when he has no visibility capability for the module.
     */
    public function test_is_module_visible_for_user_no_view_capability() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // We generate all the required stuff: course, a resource (url, e.g.), a user enrolled in the course, a group.
        $course = $this->getDataGenerator()->create_course();
        $url = $this->getDataGenerator()->get_plugin_generator('mod_url')->create_instance(array('course' => $course->id));
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 5); // 5 is student role id.
        $this->setUser($student);

        $nocap = new stdClass();
        $nocap->contextid = 1;
        $nocap->roleid = 5;
        $nocap->capability = 'mod/url:view';
        $nocap->permission = CAP_PROHIBIT;
        $DB->insert_record('role_capabilities', $nocap);

        // We get the testing method by reflection.
        $method = self::get_methods('is_module_visible_for_user');

        // And, finally, we test the method.
        $actual = $method->invokeArgs($this->resource, array($course->id, $url->cmid));

        $this->assertFalse($actual);
    }

    /**
     * Test getting the section name of a resource.
     */
    public function test_get_section_name() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // We generate all the required stuff: course, a resource (url, e.g.).
        $course = $this->getDataGenerator()->create_course();
        $url = $this->getDataGenerator()->get_plugin_generator('mod_url')->create_instance(array('course' => $course->id));

        // We get the testing method by reflection.
        $method = self::get_methods('get_section_name');

        // And, finally, we test the method.
        $expected = '0_General';
        $actual = $method->invokeArgs($this->resource, array($course->id, $url->cmid));

        $this->assertEquals($expected, $actual);
    }
}