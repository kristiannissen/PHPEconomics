<?
class EconomicsException extends Exception {
	public function __toString () {
		$_msg = "Exception ". __CLASS__ ." threw an exception: ". $this->getMessage() ." at line ". $this->getLine();
		
		syslog(LOG_DEBUG, $_msg);
		
		return $_msg;
	}
}