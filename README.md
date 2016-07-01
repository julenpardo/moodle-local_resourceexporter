Moodle Resource Exporter
========================

![Release](https://img.shields.io/badge/release-v1.2-blue.svg) ![Supported](https://img.shields.io/badge/supported-2.9%2C%203.0%2C%203.1--rc2-green.svg) ![Coverage](https://img.shields.io/badge/coverage-89.77%25-brightgreen.svg) 

## What is this thing?
This is a plugin for those who like to have resources of Moodle courses in their local disk, and are tired of downloading each resource one by one.

With a single click, it will create a zip that will contain the following resources of the course from where the exportation is made:

 - Files.
 - Folders, and files within.
 - URLs, in txt format.

## Current version
The current release, the first stable, is the v1.2 (build 2016070100), tested for Moodle 2.9, 3.0 and 3.1:
 - For [Moodle 2.9 (v2.9.1.2)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v2.9.1.2)
 - For [Moodle 3.0 (v3.0.1.2)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v3.0.1.2)
 - For [Moodle 3.1-rc2 (v3.1.1.2)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v3.1.1.2)

## Changes from v1.1
 - Fix issue "Misleading installation instructions" (see [issue 21](https://github.com/julenpardo/moodle-local_resourceexporter/issues/21)).
 - Fix issue "Errors while trying to test the plugin" (see [issue 22](https://github.com/julenpardo/moodle-local_resourceexporter/issues/22).
 - Fix issue "Naming issues" (see [issue 23](https://github.com/julenpardo/moodle-local_resourceexporter/issues/23)).
 - Fix issue "Where does the tool check for access rights?" (see [issue 24](https://github.com/julenpardo/moodle-local_resourceexporter/issues/24)). 

## Upcoming features
 - Allow to export the remaining resources:
  - Books
  - Pages
  - Labels

## Where are the created files stored in the server?
 They are stored in the temporary directory ($CFG->tempdir). So, they will be removed by the "Delete stale temp files" scheduled task (\core\task\file_temp_cleanup_task).

## Installation
 - Go to the local directory of your Moodle installation:
 `cd /wwwroot/local`
 - Clone this repository to a directory named `resourceexporter`:
 `git clone https://github.com/julenpardo/moodle-local_resourceexporter resourceexporter`
 (Or, download directly the [latest master release, v1.0](https://github.com/julenpardo/moodle-local_resourceexporter/archive/v1.0.zip)).
 - Finally, install it from Moodle.

## Usage
Just go to the course you want download the resources from, and follow the link in the administration block:

![Resource exporter](img/local_resourceexporter.png)
