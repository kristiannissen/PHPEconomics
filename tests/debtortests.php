<?php

include realpath(dirname(__FILE__) .'/../economicsws.php');

class DebtorTest extends PHPUnit_Framework_TestCase {
  protected $ew;
  
  function setUp () {
    $this->ew = new EconomicsWS();
  }
  
  function tearDown () {
    $this->ew->disconnect();
    
    unset($this->ew);
  }
  
  function testGetAll () {
    $this->assertTrue(is_array($this->ew->get_debtors()));
  }
  
  function testGetDebtor () {
    $this->assertTrue(is_object($this->ew->get_debtor(array(
      'number' => 9
    ))));
  }
  
  function testCreateDebtor () {
    $this->markTestIncomplete('Not yet tested');
  }
  
  function testUpdateDebtor () {
    $this->markTestIncomplete('Not yet tested');
  }
}
