# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http:#www.gnu.org/licenses/>.

@local @local_resourceexporter
  Feature: Resource export
    To download resources at once, I need to add the link to export resources.

    Scenario: Show link to the admin user, who should be able to export the resources even if he's not enrolled
      Given the following "courses" exist:
        | fullname | shortname |
        | Course 1 | c1        |
      And I log in as "admin"
      And I follow "Courses"
      And I follow "Course 1"
      And I click on "Export resources" "link" in the "Administration" "block"
      Then I should not see "You can't download resources from a course you are not enrolled in."
      And I should not see "You don't have permission to download the resources."

    Scenario: Show link to a user enrolled in a course
      Given the following "courses" exist:
        | fullname | shortname |
        | Course 1 | c1        |
      And the following "users" exist:
        | username | firstname | lastname | email                |
        | student1 | Student   | 1        | student1@example.com |
      And the following "course enrolments" exist:
        | user     | course | role    |
        | student1 | c1     | student |
      When I log in as "student1"
      And I follow "Course 1"
      And I click on "Export resources" "link" in the "Administration" "block"
      Then I should not see "You can't download resources from a course you are not enrolled in."
      And I should not see "You don't have permission to download the resources."

    Scenario: Evil student trying to access directly the download link for a course he's not enrolled in, receives an error message
      Given the following "courses" exist:
        | fullname | shortname |
        | Course 1 | c1        |
      And the following "users" exist:
        | username | firstname | lastname | email                |
        | student1 | Student   | 1        | student1@example.com |
      When I log in as "student1"
      And I go to "/local/resourceexporter/create_zip.php" "c1"
      Then I should see "You can't download resources from a course you are not enrolled in."