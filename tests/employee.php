<?php

include realpath(dirname(__FILE__) .'/../economicsws.php');
/**
 * http://www.phpunit.de/manual/3.4/en/index.html
 */
class AccessTest extends PHPUnit_Framework_TestCase {
  protected $econ;
  
  public function setUp () {
    $this->econ = new EconomicsWS();
  }
  public function tearDown () {
    $this->econ->disconnect();
    
    unset($this->econ);
  }
  
  public function testGetEmployees () {
    $this->assertTrue($this->econ->get_employees() > 0);
  }
  
  public function testCreateEmployee () {
    $this->assertTrue(is_object($this->econ->create_employee(array())));
  }
}