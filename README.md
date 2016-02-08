# CakePHP Application Skeleton

[![Build Status](https://img.shields.io/travis/cakephp/app/master.svg?style=flat-square)](https://travis-ci.org/cakephp/app)
[![License](https://img.shields.io/packagist/l/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)

A skeleton for creating applications with [CakePHP](http://cakephp.org) 3.x.

The framework source code can be found here: [cakephp/cakephp](https://github.com/cakephp/cakephp).

## Installation

Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.

## Configuration

Create `config/app.php` and setup the 'Datasources' and any other
configuration relevant for your application according to app.default.php

## Creating Database Schema

You can use the script in `config/schema/users.sql` to create the `Users` table which will be used for authentication.


## Acceptto

Integration is done using Acceptto MFA Rest API Guide [Acceptto REST](https://www.acceptto.com/docs/general_api)

## Running Application

Just go to the command line and run `bin/cake server` from directory of project. It will run a development web server for test at `http://localhost:8765/`