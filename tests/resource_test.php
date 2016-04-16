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
 * @copyright  2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @category   phpunit
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/usablebackup/classes/resources/resource.php');

use local_usablebackup\resource;

/**
 * This dirty workaround is required to test the implemented methods of the abstract class.
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

class local_usablebackup_resource_testcase extends advanced_testcase {

    protected $resource;

    protected function setUp() {
        parent::setUp();
        $this->resource = new concrete_resource();
    }

    protected function tearDown() {
        parent::tearDown();
        $this->resource = null;
    }

    /**
     * Reflection method, to access non-public methods.
     *
     * @param $name
     * @return ReflectionMethod
     */
    protected static function get_methods($name) {
        $class = new ReflectionClass('local_usablebackup\resource');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public function test_create_section_dir_if_not_exists_emtpy_section() {
        global $CFG;

        $method = self::get_methods('create_section_dir_if_not_exists');

        $parentdirectory = $CFG->dataroot;
        $sectionname = '';

        $expected = $parentdirectory;

        $actual = $method->invokeArgs($this->resource, array($parentdirectory, $sectionname));

        $this->assertEquals($expected, $actual);
    }

    public function test_create_section_dir_if_not_exists_dir_not_exists() {
        global $CFG;

        $method = self::get_methods('create_section_dir_if_not_exists');

        $parentdirectory = $CFG->dataroot;
        $sectionname = 'section';

        $expected = $parentdirectory . '/' . $sectionname;

        $actual = $method->invokeArgs($this->resource, array($parentdirectory, $sectionname));

        $this->assertEquals($expected, $actual);
    }

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

}