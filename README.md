Moodle Resource Exporter
========================

![Release](https://img.shields.io/badge/release-v1.0-brightgreen.svg) ![Coverage](https://img.shields.io/badge/coverage-89.77%25-brightgreen.svg) ![Supported](https://img.shields.io/badge/supported-Moodle%202.9%2C%20Moodle%203.0-green.svg)

## What is this thing?
This is a plugin for those who like to have resources of Moodle courses in their local disk, and are tired of downloading each resource one by one.

With a single click, it will create a zip that will contain the following resources of the course from where the exportation is made:

 - Files.
 - Folders, and files within.
 - URLs, in txt format.

## Current version
The current release, the first stable, is the v1.0(build 2016050500), tested for Moodle 2.9 and 3.0:
 - For [Moodle 2.9 (v2.9.1.0)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v2.9.1.0)
 - For [Moodle 3.0 (v3.0.1.0)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v3.0.1.0)

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
 - Clone this repository to a directory named `usablebackup`:
 `git clone https://github.com/julenpardo/moodle-local_resourceexporter resourceexporter`
 (Or, download directly the [latest master release, v1.0](https://github.com/julenpardo/moodle-local_resourceexporter/archive/v1.0.zip)).
 - Finally, install it from Moodle.

## Usage
Just go to the course you want download the resources from, and follow the link in the administration block:

![Resource exporter](img/local_resourceexporter.png)
