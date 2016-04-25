Human Usable Backup
===================

![Release](https://img.shields.io/badge/release-v1.0--beta1-brightgreen.svg) ![Coverage](https://img.shields.io/badge/coverage-86.54%25-brightgreen.svg) ![Supported](https://img.shields.io/badge/supported-Moodle%202.9%2C%20Moodle%203.0-green.svg)

## What is this thing?
This is a plugin for those who like to have resources of Moodle courses in their disk, and are tired of downloading each resource one by one.

With a single click, it will create a zip that will contain the following resources of the course from where the download is made:

 - Files.
 - Folders, and files within.
 - URLs, in txt format.


For the moment, this plugin is local, because it has not been decided yet which form will it have (or it may remain as local).
 
In the same way, the name is not definitive.

## Current version
The current release is the v1.0-beta (build 2016042500), tested for Moodle 2.9 and 3.0.

This beta version, actually, does the work, but it's not displayed in the course; the download link has to be accessed directly.

## Installation
 - Go to the local directory of your Moodle installation:
 `cd /wwwroot/local`
 - Clone this repository to a directory named `usablebackup`:
 `git clone https://github.com/julenpardo/moodle-local_usablebackup usablebackup`
 (Or, download directly the [latest release, v1.0-beta1](https://github.com/julenpardo/moodle-local_usablebackup/archive/v1.0-beta1.zip)).
 - Finally, install it from Moodle.
 
## Usage
If you want to download the contents of course with id *x*, you just have to follow the following URL:

`http://my_moodle_wwwroot/local/usablebackup/create_zip.php?courseid=x`

So, e.g., if your `wwwroot` is `http://localhost/my_moodle`, and the course for which you want to perform the download is `id=2`, then, you have to follow the following URL:

`http://localhost/my_moodle/local/usablebackup/create_zip.php?courseid=2`
