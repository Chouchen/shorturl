<?
include 'XMLSQL.php';
class ShortURL extends XMLSQL{

	const DATABASE         		= "db/database.xml";
	const STATE_ALREADY_EXIST 	= "Ce nom de raccourci existe déjà";
	const STATE_FIELD_MISSING	= "Merci de remplir les 2 champs. ";
	const STATE_ERROR			= "Erreur. ";
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