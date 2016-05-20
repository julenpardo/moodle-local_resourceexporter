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
 * Behat steps definitions for resource exporter local plugin.
 *
 * @package   local_resourceexporter
 * @copyright 2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Steps definitions for resource exporter local plugin.
 *
 * @package   local_resourceexporter
 * @copyright 2016 onwards Julen Pardo & Mondragon Unibertsitatea
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_resourceexporter extends behat_base {

    /**
     * Sets the page location to the given url.
     *
     * @Given /^I go to "([^"]*)" "([^"]*)"$/
     * @param string $url The url to go.
     * @param string $courseshortname The short name of the ?courseid param.
     */
    public function i_go_to($url, $courseshortname) {
        global $DB;

        $courseid = $DB->get_record('course', array('shortname' => $courseshortname), 'id', MUST_EXIST);
        $completeurl = $url . "?courseid=$courseid->id";

        $this->getSession()->visit($this->locate_path($completeurl));
    }
}

