<?php

include_once 'bootstrap.php';

class TestProduct extends UnitTestCase {
	protected $ew;
	
	function setUp () {
		$this->ew = new EconomicsWS;
	}
	
	function tearDown () {
		unset($this->ew);
	}
	
	function testGetProducts () {
		// Fetch all products
		$products = $this->ew->get_products();
		
		$this->assertTrue(is_array($products));
	}
}