<?php
/**
 * @author: Kristian Nissen
 * 
 */
include realpath(dirname(__FILE__) .'/../economicsws.php');

class DescribeDebtor extends PHPSpec_Context {
  protected $econ;  
  
  function beforeAll() { 
    $this->econ = new EconomicsWS();
  }
  function afterAll() {
    $this->econ->disconnect();
    
    unset($this->econ);
  }
  function itShouldReturnIdOnCreate() {
    $params = array(		
		  'debitor_name' => 'CompuGlobalHyperMegaNet',
		  'debitor_ean' => '',
		  'debitor_email' => 'chunkylover53@aol.com',
		  'debitor_phone_and_fax' => '80808080',
		  'debitor_website' => 'http://www.example.com',
		  'debitor_address' => 'et eller andet sted',
		  'debitor_postalcode' => '1100',
		  'debitor_city' => 'en eller anden gade',
		  'debitor_country' => 'Danmark',
		  'debitor_cvr' => '123456789',
		  'currency' => 'DKK',
		  'term_of_payment' => 'Netto 8 dage',
		  'template_name' => '22.05.07',
		  'debitor_attention' => 'Homer Simpson',
		  'employee_number' => '007'
	  );	
	  $this->spec($this->econ->create_debitor($params) > 0)->should->beTrue();	
  } 
  function itShouldReturnTrueWhenNewOrderIsAddedToInvoice() {
	  $params = array(
	    'debitor_number' => '78263',
	    'product_number' => '200',
	    'product_quantity' => rand(1,3),
	    'product_description' => ''
	  );	  
	  $this->spec($this->econ->add_order_to_invoice($params))->should->beTrue();
  }
  function itShouldReturnStringWhenCurrentInvoiceIsDownloaded() {
    $params = array(
      'current_invoice_id' => '917'
    );    
    $this->spec(gettype($this->econ->download_current_invoice($params)) == 'string')->should->beTrue();
  }
  function itShouldReturnTrueWhenDebtorIsAddedToRecurringPayments() {
    $params = array(
      'debitor_number' => '78184',
      'start_date' => '2008-07-24T00:00:00',
      'end_date' => '2008-08-24T00:00:00',
      'subscription_id' => '1005'
    );    
    $this->spec($this->econ->add_subscriber($params))->should->beTrue();
  }
  function itShouldReturnTrueWhenExpireSubscriberIsCalled() {
    $params = array(
      'subscriber_id' => '191',
      'expire_date' => '2009-08-18T00:00:00'
    );
    $this->spec($this->econ->expire_subscriber($params))->should->beTrue();
  }
  /**
   * Helpers
   */
  function itShouldReturnAnArrayWhenGetTemplatesIsCalled() {
    $this->spec(gettype($this->econ->get_templates()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenTermOfPaymentIsCalled() {
    $this->spec(gettype($this->econ->get_term_of_payment()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetCurrenciesIsCalled() {
    $this->spec(gettype($this->econ->get_currencies()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetSubscriptionsIsCalled() {
    $this->spec(gettype($this->econ->get_subscriptions()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetSubscribersIsCalled() {
    $this->spec(gettype($this->econ->get_subscribers()) == 'array')->should->beTrue();    
  }
  function itShouldReturnAnArrayWhenGetProductsIsCalled() {
    $this->spec(gettype($this->econ->get_products()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetDebtorsIsCalled() {
    $this->spec(gettype($this->econ->get_debitors()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetEmployessIsCalled() {
    $this->spec(gettype($this->econ->get_employees()) == 'array')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetDebitorCurrentInvoicesIsCalled() {
    $params = array(
      'debitor_number' => '78263'
    );
    $this->spec(gettype($this->econ->get_debitor_current_invoices($params)) == 'array')->should->beTrue();
  }
  function itShouldReturnAnObjectWhenGetDebitorIsCalled() {
    $params = array(
      'debitor_number' => '78263'
    );
    $this->spec(gettype($this->econ->get_debitor($params)) == 'object')->should->beTrue();
  }
  function itShouldReturnAnArrayWhenGetDebitorInvoicesIsCalled() {
    $params = array(
      'debitor_number' => '78263'
    );
    $this->spec(count($this->econ->get_debitor_invoices($params)) > 0)->should->beTrue();
  }
  function itShouldReturnTrueWhenDebitorIsUpdated() {
    $params = array(
      'debitor_number' => '78263',
      'debitor_name' => 'CompuGlobalHyperMegaNet',
		  'debitor_ean' => '',
		  'debitor_email' => 'chunkylover53@aol.com',
		  'debitor_phone_and_fax' => '80808080',
		  'debitor_website' => 'http://www.example.co.uk',
		  'debitor_address' => '742 Evergreen Terrace',
		  'debitor_postalcode' => '1100',
		  'debitor_city' => 'Springfield',
		  'debitor_country' => 'Danmark',
		  'debitor_vatnumber' => '26901626',
		  'currency' => 'DKK',
		  'term_of_payment' => 'Netto 8 dage',
		  'template_name' => '22.05.07'
    );
    $this->spec($this->econ->update_debitor($params))->should->beTrue();
  }
}
