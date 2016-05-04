Moodle Resource Exporter
========================

![Release](https://img.shields.io/badge/release-v1.0--rc1-brightgreen.svg) ![Coverage](https://img.shields.io/badge/coverage-89.77%25-brightgreen.svg) ![Supported](https://img.shields.io/badge/supported-Moodle%202.9%2C%20Moodle%203.0-green.svg)

## What is this thing?
This is a plugin for those who like to have resources of Moodle courses in their disk, and are tired of downloading each resource one by one.

With a single click, it will create a zip that will contain the following resources of the course from where the exportation is made:

 - Files.
 - Folders, and files within.
 - URLs, in txt format.

## Current version
The current release is the v1.0-rc1 (build 2016050400), tested for Moodle 2.9 and 3.0:
 - For [Moodle 2.9 (v2.9.1.0-rc1)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v2.9.1.0-rc1)
 - For [Moodle 3.0 (v3.0.1.0-rc1)](https://github.com/julenpardo/moodle-local_resourceexporter/releases/tag/v3.0.1.0-rc1)

## Changes from v1.0-beta2
 - Fix [issue #14: Files with tildes and those special characters are not named correctly](https://github.com/julenpardo/moodle-local_resourceexporter/issues/14).
 - Fix [issue #15: When section has no name, use default one for creating directories](https://github.com/julenpardo/moodle-local_resourceexporter/issues/15).
 - Fix [issue #16: Admin user can't download the contents if they are not enrolled](https://github.com/julenpardo/moodle-local_resourceexporter/issues/16).

## Installation
 - Go to the local directory of your Moodle installation:
 `cd /wwwroot/local`
 - Clone this repository to a directory named `usablebackup`:
 `git clone https://github.com/julenpardo/moodle-local_resourceexporter resourceexporter`
 (Or, download directly the [latest master release, v1.0-rc1](https://github.com/julenpardo/moodle-local_resourceexporter/archive/v1.0-rc1.zip)).
 - Finally, install it from Moodle.

## Usage
Just go to the course you want download the resources from, and follow the link in the administration block:

![Resource exporter](img/local_resourceexporter.png)
