<?php
/**
 * @author: Kristian Nissen
 * @version: 0.4
 */
class EconomicsWS {
  // Soap client
  private $client;
  // Debug access
  private $access_granted;
  
  function __construct() {    
    try {
      $settings = parse_ini_file(realpath(dirname(__FILE__) .'/economics.ini'));

      if (is_array($settings) && count($settings) > 0) {
        $this->client = new SoapClient($settings['wsdl_endpoint'], array("trace" => 1, "exceptions" => 1));
        
        $this->client->Connect(array(
		      'agreementNumber' => $settings['agreement_number'],
		      'userName' => $settings['user_name'],
		      'password' => $settings['password']
		    ));
		    
		    $this->access_granted = true;
      } else {
        die('INI file could not be found');
      }      
		  
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());

		  $this->access_granted = false;      
    }
  }
  function disconnect() {
    $this->client->Disconnect();
  }
  /**
   * @return boolean
   */
  function test_access_credentials () {
    return $this->access_granted;
  }
  /**
   * @param mixed $params
   * @param string [$vat_zone]
   * @return int
   */
  function create_debitor($params, $vat_zone = 'HomeCountry') {
    try {
	    $debtor = $this->client->Debtor_FindByName(array(
	      'name' => $params['debitor_name']
	    ))->Debtor_FindByNameResult->DebtorHandle;
	    
	    if (!$debtor) {
		    $number = $this->client->Debtor_GetNextAvailableNumber()->Debtor_GetNextAvailableNumberResult;
		    
		    $debtor_group_handles = $this->client->debtorGroup_GetAll()->DebtorGroup_GetAllResult->DebtorGroupHandle;		    
		    
		    $currency_handle = $this->client->Currency_FindByCode(array(
          'code' => $params['currency']
        ))->Currency_FindByCodeResult;
        
        $term_handle = $this->client->TermOfPayment_FindByName(array(
          'name' => $params['term_of_payment']
        ))->TermOfPayment_FindByNameResult->TermOfPaymentHandle;
        
        $templatecollection_handle = $this->client->TemplateCollection_FindByName(array(
          'name' => $params['template_name']
        ))->TemplateCollection_FindByNameResult->TemplateCollectionHandle;
        
        $employee_handle = $this->client->Employee_FindByNumber(array(
          'number' => employee_number
        ))->Employee_FindByNumberResult->EmployeeHandle;
        
        $debtor_data = (object)array(          
          'IsAccessible' => true,
          'Number' => $number,
          'DebtorGroupHandle' => $debtor_group_handles,
          'Name' => $params['debitor_name'],
          'VatZone' => $vat_zone,
          'CurrencyHandle' => $currency_handle,
          'TermOfPaymentHandle' => $term_handle,
          'LayoutHandle' => $templatecollection_handle,
          'Name' => $params['debitor_name'],
          'Email' => $params['debitor_email'],
          'TelephoneAndFaxNumber' => $params['debitor_phone_and_fax'],
          'Website' => $params['debitor_website'],
          'Address' => $params['debitor_address'],
          'PostalCode' => $params['debitor_postalcode'],
          'City' => $params['debitor_city'],
          'Country' => $params['debitor_country'],
          'OurReferenceHandle' => $employee_handle,
          'CINumber' => $params['debitor_cvr']
        );        
		    $this->client->Debtor_CreateFromData(array(
		      'data' => $debtor_data
		    ));
		    $debtor = $this->client->Debtor_FindByNumber(array(
		      'number' => $number
		    ))->Debtor_FindByNumberResult;
		    
		    $debtor_contact_handle = $this->client->DebtorContact_Create(array(
		      'debtorHandle' => $debtor,
		      'name' => $params['debitor_attention']
		    ))->DebtorContact_CreateResult;
		    
		    $this->client->Debtor_SetAttention(array(
		      'debtorHandle' => $debtor,
		      'valueHandle' => $debtor_contact_handle
		    ));
		    
		    if(!empty($params['debitor_ean'])){
		      $this->client->Debtor_SetEan(array(
		        'debtorHandle' => $debtor,
		        'valueHandle' => $params['debitor_ean']
		      ));
		    }
	    } else {
	      $number = 0;
	    }
	  } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      return 0;
	  }

	  return intval($number);
  }
  /**
   * @param mixed $params
   * @return boolean
   */
  function update_debitor($params, $vat_zone = 'HomeCountry') {
    try {
      $debtor = $this->client->Debtor_FindByNumber(array(
        'number' => $params['debitor_number']
      ))->Debtor_FindByNumberResult;
	    
	    $debtor_group_handles = $this->client->debtorGroup_GetAll()->DebtorGroup_GetAllResult->DebtorGroupHandle;
	    
	    $currency_handle = $this->client->Currency_FindByCode(array(
        'code' => $params['currency']
      ))->Currency_FindByCodeResult;
      
      $term_handle = $this->client->TermOfPayment_FindByName(array(
        'name' => $params['term_of_payment']
      ))->TermOfPayment_FindByNameResult->TermOfPaymentHandle;
      
      $templatecollection_handle = $this->client->TemplateCollection_FindByName(array(
        'name' => $params['template_name']
      ))->TemplateCollection_FindByNameResult->TemplateCollectionHandle;
	    
	    $debtor_handle = $this->client->Debtor_UpdateFromData(array(
	      'data' => (object) array(
	        'Handle' => $debtor,
	        'Number' => $debtor->Number,
	        'VatZone' => $vat_zone,
	        'IsAccessible' => true,
	        'Name' => $params['debitor_name'],
          'Email' => $params['debitor_email'],
          'CINumber' => $params['debitor_vatnumber'],
          'TelephoneAndFaxNumber' => $params['debitor_phone_and_fax'],
          'Website' => $params['debitor_website'],
          'Address' => $params['debitor_address'],
          'PostalCode' => $params['debitor_postalcode'],
          'City' => $params['debitor_city'],
          'Country' => $params['debitor_country'],
          'DebtorGroupHandle' => $debtor_group_handles,
          'CurrencyHandle' => $currency_handle,
          'TermOfPaymentHandle' => $term_handle,
          'LayoutHandle' => $templatecollection_handle
	      )
	    ))->Debtor_UpdateFromDataResult;
	    
	    if (!empty($params['debitor_ean'])) {
        $this->client->Debtor_SetEan(array(
          'debtorHandle' => $debtor,
          'valueHandle' => $params['debitor_ean']
        ));      
      }
	    
    } catch (Exception $e) {
      syslog(LOG_DEBUG, ' SOAP response '. $this->client->__getLastResponse());
      return false;
    }
    
    return true;
  }
  /**
   * @param mixed $params
   * @return boolean
   */
  function add_order_to_invoice($params) {
    try {
      $debtor_current_invoices = $this->client->Debtor_GetCurrentInvoices(array(
        'debtorHandle' => (object)array(
          'Number' => $params['debitor_number']
        )
      ))->Debtor_GetCurrentInvoicesResult->CurrentInvoiceHandle;

      if(gettype($debtor_current_invoices) == 'array'){
        $current_invoice = array_shift($debtor_current_invoices);
      } else if(gettype($debtor_current_invoices) == 'object'){
        $current_invoice = $debtor_current_invoices;
      } else {
        $current_invoice = $this->client->CurrentInvoice_Create(array(
          'debtorHandle' => (object)array(
            'Number' => $params['debitor_number']
          ) 
        ))->CurrentInvoice_CreateResult;
      }

      $invoiceline_handle = $this->client->CurrentInvoiceLine_Create(array(
        'invoiceHandle' => $current_invoice
      ))->CurrentInvoiceLine_CreateResult;

      $product_handle = $this->client->Product_FindByNumber(array(
        'number' => $params['product_number']
      ))->Product_FindByNumberResult;
      
      $product_data_handle = $this->client->Product_GetData(array(
        'entityHandle' => $product_handle
      ))->Product_GetDataResult;
      
      $current_invoiceline_handle = $this->client->CurrentInvoiceLine_CreateFromData(array(
        'data' => array(
          'Handle' => $invoiceline_handle,
          'Id' => $current_invoice->Id,
          'Number' => $invoiceline_handle->Number,
          'InvoiceHandle' => $current_invoice,
          'Description' => empty($params['product_description']) ? $product_data_handle->Name : $params['product_description'],
          'DeliveryDate' => NULL,
          'ProductHandle' => $product_handle,
          'Quantity' => floatval($params['product_quantity']),
          'UnitNetPrice' => $product_data_handle->SalesPrice,
          'DiscountAsPercent' => floatval($params['product_discount']),          
          'UnitCostPrice' => 0.0,
          'TotalNetAmount' => (floatval($product_data_handle->SalesPrice) * floatval($params['product_quantity'])),
          'TotalMargin' => 0.0,
          'MarginAsPercent' => 0.0
        )
      ))->CurrentInvoiceLine_CreateFromDataResult;
      
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      return false;
    }
    return true;
  }
  /**
   * @return mixed
  */
  function get_product_groups() {
    return $this->client->ProductGroup_GetAll()->ProductGroup_GetAllResult;
  }
  /**
   * @return mixed
   */
  function get_products() {
    return $this->client->Product_GetAll()->Product_GetAllResult->ProductHandle;
  }
  /**
   * @params int debitor id
   * @return boolean
   */
  function add_subscriber($params) {
    try {
      $subscriber_handle = $this->client->Subscriber_Create(array(
        'debtorHandle' => (object)array(
          'Number' => $params['debitor_number']
        ),
        'startDate' => $params['start_date'],
        'endDate' => $params['end_date'],
        'registeredDate' => date('Y-m-d'). 'T00:00:00',
        'subscriptionHandle' => (object)array(
          'Id' => intval($params['subscription_id'])
        )
      ))->Subscriber_CreateResult;
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      return false;
    }
    
    return true;
  }
  /**
   * @params mixed
   * @return boolean
   */
  function expire_subscriber($params) {
    try {                  
      $this->client->Subscriber_SetExpiryDate(array(
        'subscriberHandle' => (object) array(
          'SubscriberId' => $params['subscriber_id']
        ),
        'value' => $params['expire_date']
      ));
      
    } catch (Exception $e) {
      //syslog(LOG_DEBUG, ' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      return false;
    }
    
    return true;
  }
  /**
   * @return mixed
   */
  function get_subscribers() {
    return $this->client->Subscriber_GetAll()->Subscriber_GetAllResult->SubscriberHandle;
  }
  /**
   * @return mixed
   */
  function get_subscriptions() {
    return $this->client->Subscription_GetAll()->Subscription_GetAllResult->SubscriptionHandle;
  }
  /**
   * @param string debitor number
   * @return boolean
   */
  function download_current_invoice($params, $format = 'pdf') {
    try {      
      $invoice_handle = $this->client->CurrentInvoice_Book(array(
        'currentInvoiceHandle' => (object)array(
          'Id' => intval($params['current_invoice_id'])
        )
      ))->CurrentInvoice_BookResult;
      
      $invoice = "";
      
      switch ($format) {
        default:
        case 'pdf':
          $invoice = $this->client->Invoice_GetPdf(array(
            'invoiceHandle' => $invoice_handle
          ))->Invoice_GetPdfResult;
            break;
        case 'oioxml':
          $invoice = $this->client->Invoice_GetOioXml(array(
            'invoiceHandle' => $invoice_handle
          ))->Invoice_GetOioXmlResult;
            break;
      }
      
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      return false;
    }
    
    return $invoice;
  }
  /**
   * @return mixed
   */
  function get_currencies() {
    return $this->client->Currency_GetAll()->Currency_GetAllResult->CurrencyHandle;
  }
  /**
   * @return mixed
   */
  function get_term_of_payment() {
    $terms = $this->client->TermOfPayment_GetAll()->TermOfPayment_GetAllResult->TermOfPaymentHandle;
    return $this->client->TermOfPayment_GetDataArray(array(
      'entityHandles' => $terms
    ))->TermOfPayment_GetDataArrayResult->TermOfPaymentData;
  }
  /**
   * @return mixed
   */
  function get_templates() {
    $templates = $this->client->TemplateCollection_GetAll()->TemplateCollection_GetAllResult->TemplateCollectionHandle;
    return $this->client->TemplateCollection_GetDataArray(array(
      'entityHandles' => $templates
    ))->TemplateCollection_GetDataArrayResult->TemplateCollectionData;
  }
  /**
   * @return mixed
   */
  function get_debitors() {
    return $this->client->Debtor_GetAll()->Debtor_GetAllResult->DebtorHandle;
  }
  /**
   * @return mixed
   */
  function get_employees () {
    $employees = array();
    
    try {
      $employees = $this->client->Employee_GetAll()->Employee_GetAllResult->EmployeeHandle;
      
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'get_employees(): exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
    }
    
    return $employees;
  }
  /**
   * @return mixed
   */
  function get_employee_by_number ($number) {
     
  }  
  /**
   * @return mixed
   */
  function get_debitor_current_invoices($params) {
    try {
      $debtor = $this->client->Debtor_FindByNumber(array(
	      'number' => $params['debitor_number']
	    ))->Debtor_FindByNumberResult;
	    
      $current_invoices = $this->client->Debtor_GetCurrentInvoices(array(
        'debtorHandle' => $debtor
      ))->Debtor_GetCurrentInvoicesResult->CurrentInvoiceHandle;
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      $current_invoices = array();
    }
    
    return $current_invoices;
  }
  /**
   * @return object
   */
  function get_debitor($params) {
    try {
      $debtor = $this->client->Debtor_FindByNumber(array(
        'number' => $params['debitor_number']
      ))->Debtor_FindByNumberResult;
      
      $debtor_data = $this->client->Debtor_GetData(array(
        'entityHandle' => $debtor
      ))->Debtor_GetDataResult->Handle;
    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      $debtor_data = (object)array();
    }
    
    return $debtor_data;
  }
  /**
   * @return mixed
   */
  function get_debitor_invoices($params) {
    try {
      $debtor = $this->client->Debtor_FindByNumber(array(
        'number' => $params['debitor_number']
      ))->Debtor_FindByNumberResult;
      
      $invoices = $this->client->Debtor_GetInvoices(array(
        'debtorHandle' => $debtor
      ))->Debtor_GetInvoicesResult->InvoiceHandle;

    } catch (Exception $e) {
      syslog(LOG_DEBUG, 'exception '. $e->getMessage() .' at line '. $e->getLine() .' SOAP request '. $this->client->__getLastRequest() .' SOAP response '. $this->client->__getLastResponse());
      $invoices = array();
    }
    
    return $invoices;
  }
}
