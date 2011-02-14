<?
/**
 * Copyright ou © ou Copr. Clément Desmidt - Shikiryu, 14/02/2011
 * 
 * shikiryu+url [ at ] gmail [ dot ] com
 * 
 * Ce logiciel est un programme informatique servant à faire un raccourci d'url et stats. 
 * 
 * Il est sous license : http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
 * ---------------------------------------
 * Copyright or © or Copr. Clément Desmidt - Shikiryu, 14/02/2011
 * 
 * shikiryu+url [ at ] gmail [ dot ] com
 * 
 * This software is a computer program whose purpose is to url shortening and stats.
 * 
 * It's under this license : http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
 */ 
include 'XMLSQL.php';
class ShortURL extends XMLSQL{

	const DATABASE         		= "db/database.xml";
	const STATE_ALREADY_EXIST 	= "This shortcut already exists. ";
	const STATE_FIELD_MISSING	= "Don't leave any field blank ! ";
	const STATE_ERROR			= "Error. ";
	const STATE_CREATED			= "Shortcut created ";
	public $_debug = false;
       
    public function __construct($path = ''){
		parent::__construct($path.self::DATABASE);
    }

    public function shortThisUrl($longUrl, $shortName){
		if($this->pkAlreadyExists($shortName, 'url')){
			return self::STATE_ALREADY_EXIST;
		}else{
			return $this->insert(array('url'=>$longUrl,'hit'=>'0'), rawurlencode($shortName))->into('url')->query();
		}
	}
	
	public function findThisUrl($shortName){
		if($this->pkAlreadyExists(rawurlencode($shortName), 'url')){
			$this->_incrementStatFor($shortName);
			return $this->select(array('url'))->from('url')->where(rawurlencode($shortName))->query();
		}else{
			return;
		}
	}
	
	public function extractEverything(){
		return $this->select()->from('url')->query();
	}
	
	
	/**
	 * Considering the table with $shortname already exist
	 */
	private function _incrementStatFor($shortName){
		$currentHit = $this->select(array('hit'))->from('url')->where(rawurlencode($shortName))->query();
		$currentHit = $currentHit[0];
		return $this->update('url')->set(array('hit'=>$currentHit+1))->where(rawurlencode($shortName))->query();
	}
	
}