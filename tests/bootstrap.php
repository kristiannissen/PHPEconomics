<?php
/*
 * Establish reference to simpletest
 * we will include this in all our tests
 * it bootstraps the test suite
 */
ini_set('error_reporting', E_ALL);

$path_to_simpletest = realpath(dirname(dirname(dirname(__FILE__))));

set_include_path(join(PATH_SEPARATOR, array($path_to_simpletest, get_include_path())));

require_once 'simpletest/autorun.php';

require_once 'PHPEconomics/economicsws.php';