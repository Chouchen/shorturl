<?
include 'log.php';
class XMLSQL{

    const SELECT = 'select';
    const INSERT = 'insert';
    const DELETE = 'delete';
    const UPDATE = 'update';

    /**
     * Request
     */
    protected $current_request;
    protected $operation;
    protected $from;
    protected $setID;
    protected $setChild;
    protected $whereID;
    protected $whereChildren;
    protected $position;
    protected $limit;

    // Path and name to the file
    protected $_file;

    // Primary key
    protected $_primaryKey;

    // Selected table
    protected $_table;

    // XPATH of the doc
    protected $_xpath;

    // Content of the XML DB File
    protected $_doc;

    // Name of the main root
    protected $_databaseName;

    // Name of each table root
    protected $_tableName;

    // Name of each item inside tables
    protected $_itemName;

    // Encoding used for the XML
    protected $_encoding;

    // Node buffered
    protected $_buffer;

    public $_debug = true;
    public $_log;


    /**
     * Constructor
     * @param $file string path to the file to read/create
     * @param $pk string name of the primary key
     * @param $createIfNotExist bool create the file if it doesn't exist
     */
    public function __construct($file, $pk = "id", $createIfNotExist = false, $databaseName = "Database", $tableName = "table", $itemName = "item", $encoding = "utf-8"){
        if($this->_debug) $this->_log = new Log('log.dat');
        $this->_buffer            = null;
        $this->_databaseName     = $databaseName;
        $this->_itemName         = $itemName;
        $this->_tableName         = $tableName;
        $this->_encoding         = $encoding;
        $this->_primaryKey         = $pk;
        $this->_file             = $file;
        $this->_doc             = new DOMDocument;
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->formatOutput = true;
        if($this->_doc->load($this->_file)){
            if($this->_debug)
                $this->_log->message('DB lue.');
            $this->_xpath = new DOMXpath($this->_doc);
        }
        else{
            if($createIfNotExist){
                if($this->_debug)
                    $this->_log->message('création de la DB.');
                $this->createDatabase($file);
            }else{
                if($this->_debug)
                    $this->_log->error('fichier non trouvé lors de la création de l\'instance.');
                $this->_file     = null;
                $this->_doc     = null;
                $this->xpath     = null;
            }
        }
    }

    public function __destruct(){
        if($this->_debug)
            $this->_log->message('fin d\'execution du script.');
        $this->commit();
    }

    public function createDatabase($file){
        $this->_file     = $file;
        $this->_doc     = DOMDocument::loadXML('<?xml version="1.0" encoding="' . $this->_encoding . '"?>
            <' . $this->_databaseName . '>
            </' . $this->_databaseName . '>');
        $this->_xpath     = new DOMXpath($this->_doc);
        if($this->_debug)
            $this->_log->message('DB créée en cache.');
        return $this->commit();
    }

    public function dropDatabase($definitely = false){
        if($definitely){
            if($this->_debug)
                $this->_log->message('fichier supprimé.');
            unlink($this->_file);
        }else{
            $this->createDatabase($this->_file);
            if($this->_debug)
                $this->_log->message('DB effacée.');
        }
    }

    /**
     * @return bool
     */
    public function tableAlreadyExists($tableName){
        $request = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$tableName.'"]');
        if($this->_debug) $this->_log->message('Requête "tableAlreadyExist" : //' .  $this->_tableName . '[@name = "'.$tableName.'"]');
        if($this->getResult($request, 'count') >= 1)
            return true;
        return false;
    }

    public function isTableAI($tableName){
        if($this->tableAlreadyExists($tableName)){
            $table = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$tableName.'"]');
            $ai = $this->_getAttribute('autoincrement', $table);
            if($ai == 'true')
                return true;
        }
        return false;
    }

    public function isLoaded(){
        if($this->_doc != null)
            return true;
        else
            return false;
    }

       public function setPrimaryKey($pk){
        $this->_primaryKey = $pk;
    }

    public function getPrimaryKey(){
        return $this->_primaryKey;
    }

    public function getXPath(){
        return $this->_xpath;
    }

    public function setBuffer($node){
        $this->_buffer = $node;
    }

    public function getBuffer($buffer){
        return $this->_buffer;
    }

    /**
     * Saving the DB file
     */
     public function commit(){
        if($this->_doc != null && $this->_file != null){
            $this->_doc->preserveWhiteSpace = false;
            $this->_doc->formatOutput = true;
            $this->_doc->save($this->_file);
            if($this->_debug)
            $this->_log->message('DB sauvegardée.');
            return true;
        }else{
            if($this->_debug)
            $this->_log->error('Erreur lors de l\'enregistrement.');
            return false;
        }
     }
     
    public function createTable($name, $autoincrement = false, $aiDefaultValue = 0){
        if($name == '*' || $this->tableAlreadyExists($name))
            return false;
        else{
            if($autoincrement)
                return $this->_insert(array('name'=>$this->_tableName, 'attributes'=>array('name'=>$name, 'autoincrement'=>'true', 'aivalue'=>$aiDefaultValue)));
            else
                return $this->_insert(array('name'=>$this->_tableName, 'attributes'=>array('name'=>$name)));
        }
    }

    public function dropTable($table){
        return $this->_delete($table);
    }

    private function updateTableAIValue($tableName){
        if($this->tableAlreadyExists($tableName)){
            //$table = $this->selectTable($table);
            $table = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$tableName.'"]');
            $newValue = (int)$table->item(0)->getAttribute('aivalue') + 1;
            $table->item(0)->setAttribute('aivalue', $newValue);
            if($this->_debug)
            $this->_log->message('Nouvelle increment pour la table '.$tableName.' : '.$newValue);
            return $newValue;
        }
        if($this->_debug)
            $this->_log->error('Erreur lors de l\'attribution du nouvel increment de la table '.$table);
        return false;
    }

    private function _getNewIncrement($table){
        return $this->updateTableAIValue($table);
    }

    // TODO sur toutes les tables
    public function pkAlreadyExists($pk, $table = '*'){
        // if($this->selectFromPK($table, $pk , 'count') > 0){
        $tableTemp = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$table.'"]/item[@'.$this->_primaryKey.' = "'.$pk.'"]');
        if($tableTemp->length > 0){
			if($this->_debug) $this->_log->message('table '.$table.' already have '.$pk.' as a key');
            return true;
        }
		if($this->_debug) $this->_log->message('table '.$table.' doesn\'t have '.$pk.' as a key');
        return false;
    }




    private function getResult($request, $format){
        switch($format){
            case "node":
                return $request;
                break;
            case "count":
                return $request->length;
                break;
            case "array":
            default:
                return $this->requestToArray($request);
        }
    }

    public function select($what = array('*')){
        if($this->_debug)
            $this->_log->message('Construction de la requête "SELECT" :'.implode(', ',$what));
        $this->current_request = self::SELECT;
        $this->operation = $what;
        return $this;
    }

    public function insert(array $what = null, $id = null){
        if($this->_debug)
            $this->_log->message('Construction de la requête "INSERT"');
        $this->current_request = self::INSERT;
        if($what != null){
            $this->operation = $what;
			if($this->_debug) $this->_log->message('inserting : '.implode(', ', $what));
        }
        else{
            throw new Exception('You must indicate something to insert');
            if($this->_debug)
            $this->_log->error('Parameter for "INSERT" was wrong or empty.');
        }
		$this->setID = $id;
		if($this->_debug && $id!=null) $this->_log->message('ID = '.$id);
        return $this;
    }

    public function delete(){
        if($this->_debug)
            $this->_log->message('Construction de la requête "DELETE"');
        $this->current_request = self::DELETE;
        return $this;
    }

    // TODO 
    public function update($from){
        $this->current_request = self::UPDATE;
        $this->from = $from;
        if($this->_debug)
            $this->_log->message('Construction de la requête "UPDATE" : FROM = '.$this->from);
        return $this;
    }

    public function from($from){
        if($this->tableAlreadyExists($from))
            $this->from = $from;
        else
            $this->from = '*';
        if($this->_debug)
            $this->_log->message('Construction de la requête : FROM = '.$this->from);
        return $this;
    }
    
    public function into($to){
        if($this->tableAlreadyExists($to))
            $this->from = $to;
        else
            throw new Exception('This table doesn\'t exist');
        if($this->_debug)
            $this->_log->message('Construction de la requête : INTO = '.$this->from);
        return $this;
    }
    
    public function limit($numStart=0, $numFinish=null){
       if(is_int($numStart)){
			if(isset($numFinish) && is_int($numFinish))
            $this->limit = array($numStart, $numFinish);
			else
			$this->limit = $numStart;
            if($this->_debug) $this->_log->message('Construction de la requête : LIMIT = '.$this->limit);
        }else{
             if($this->_debug) $this->_log->error('Construction de la requête : les limites ne sont pas des entiers : '.$numStart.' et '.$numFinish);
        }
        return $this;
    }

    public function set(array $setChild = null, array $setID = null){
        $this->setID    = $setID;
        $this->setChild = $setChild;
        if($this->_debug)
            $this->_log->message('Construction de la requête : SET ID = '.$this->setID.' OR CHILD = '.$this->setChild);
        return $this;
    }
    
    public function where($whereID = null, array $whereChildren = null){
        $this->whereID             = $whereID;
        $this->whereChildren     = $whereChildren;
        if($this->_debug)
            $this->_log->message('Construction de la requête : WHERE ID = "'.$this->whereID.'" OR child = "'.$this->whereChildren.'"');
        return $this;
    }

    public function toPosition($to){
        if(is_numeric($to)){
            $this->position = $to;
            if($this->_debug)
            $this->_log->message('Construction de la requête : TO = '.$this->position);
        }else if($this->_debug)
            $this->_log->error('The position enter wasn\'t a number.');
        return $this;
        
    }

    public function query(){
        $stmt = $this->_prepareStmt();
        /*if(is_bool($stmt) || is_array($stmt) || is_string($stmt)){
            if($this->_debug && $stmt)
            $this->_log->message('Requête réussie.');
            else
            $this->_log->error('Requête erronée.');
            return $stmt;
        }*/
		if(is_bool($stmt)){
			if($this->_debug)
				$this->_log->message('Resultat de la requete : '.$stmt);
			return $stmt;
		}
        return $this->_requestToArray($stmt);
    }

    private function _prepareStmt(){
        switch($this->current_request){
            case self::SELECT:
                return $this->_select($this->operation, $this->from, $this->whereID, $this->whereChildren, '/'.$this->_itemName);
                break;
            case self::INSERT:
                return $this->_insertItem($this->setID, null, $this->operation, $this->from, $this->position);
                break;
            case self::DELETE:
                return $this->_delete($this->from, $this->whereID);
                break;
            case self::UPDATE:
                return $this->_update($this->from, $this->setID, $this->setChild, $this->whereID, $this->whereChildren);
                break;
            default:
                throw new Exception('no request detected.');
                break;
        }
    }

    private function _getAttribute($attribute, $node){
        if($node->length == 1){
            return $node->item(0)->getAttribute($attribute);
        }else{
            return false;
        }
    }

    private function _getChildValue($child, $node){
        $nodeArray = array();
        if($node->length == 1){
            $nodeArray = $this->_requestToArray($node);
            if(isset($nodeArray[0]['childs'][$child]))
                return $nodeArray[0]['childs'][$child];
        }
        return false;
    }

	private function _cleanRequest(){
		$this->operation 		= null;
		$this->from 			= null;
		$this->whereID 			= null;
		$this->whereChildren 	= null;
		$this->position 		= null;
		$this->limit 			= null;
		$this->setChild 		= null;
		$this->setID 			= null;
	}
	
    private function _requestToArray($request){
        $return = array();
        $number = 0;
		$what = $this->operation;
		if(count($what) == 1 && $what[0] != '*'){		
			$return = array();
			foreach($request as $element){
				$nodes = $element->childNodes;
				$length = $nodes->length;
				for ($i = 0; $i <= $length -1; $i++) {
                if($nodes->item($i)->nodeName == $what[0])
					$return[] = $nodes->item($i)->nodeValue;
				}
			}
			$this->_cleanRequest();
			if($this->_debug) $this->_log->message('Resultat de la requete : '.implode(', ', $return));
			return $return;
		}
        foreach($request as $element){
            /*if($childName != null && $childValue != null)
            $element = $element->parentNode;*/
            $elementValue = $element->attributes->item(0)->value;
            $return[$number]['name'] = $this->_itemName;
            $return[$number]['attributes'] = array($this->_primaryKey => $elementValue);
            $return[$number]['childs'] = array();

            //Retrieving Attributes
            $attributes = $element->attributes;
            $length = $attributes->length;
            for ($i = 0; $i <= $length -1 ; $i++) {
                if($attributes->item($i)->name != '')
                $return[$number]['attributes'][$attributes->item($i)->name] = $attributes->item($i)->value;
            }

            // Retrivieving childs
            $nodes = $element->childNodes;
            $length = $nodes->length;
            for ($i = 0; $i <= $length -1; $i++) {
                if($nodes->item($i)->nodeName != '')
                $return[$number]['childs'][$nodes->item($i)->nodeName] = $nodes->item($i)->nodeValue;
            }
			
            $number++;
        }
		if(isset($this->limit) && is_array($this->limit)){
			krsort($return);
			$limit = $this->limit;
			$return = array_slice($return, $limit[0], $limit[1]); 
		}else if(is_int($this->limit) && $this->limit != 0){
			krsort($return);
			$return = array_slice($return, 0, $this->limit); 
		}
        if($this->_debug) {
            $debug_text = '';
            foreach($return as $indice=>$value)
                $debug_text .= $indice.'=>'.$value;
            $this->_log->message('Request to array => result : '.$debug_text);
        }
		$this->_cleanRequest();
		if($this->_debug) $this->_log->message('Resultat de la requete : '.implode(', ', $return));
        return $return;
    }

    //TODO forceinsert?
    private function _update($from, $setID = null, $setChild = null, $whereID = null, $whereChildren = null, $forceInsert = false){
        $node = $this->_select(array('*'), $from, $whereID, $whereChildren, '/'.$this->_itemName);
        $nodeArray = $this->_requestToArray($node);
        if($node != null && $nodeArray != null){
            if($setChild != null){
                if($this->_debug) $this->_log->message('Updating '.count($setChild).' children');
                foreach($setChild as $indice=>$value)
                    $this->_updateChildValue($from, $node, $indice, $value);
                }    
            // $this->updateChildValue($from, $node, $setChild, $forceInsert);
            if($setID != null){
                if($this->_debug) $this->_log->message('Updating ID '.$nodeArray[0]["attributes"][$this->_primaryKey].' into '.$setID);
                $this->_updateItemID($from, $nodeArray, $setID, $forceInsert);
            }
            return true;
        }else{
            return false;
        }
    }
    
    //TODO requete pour recevoir plusieurs valeurs what
    private function _select(array $what, $from, $id = null, $childs = null, $item = ''){
        $attribute  = '';
        $child      = '';
        if($id != null && !is_array($id)){
            $attribute = '[@' . $this->_primaryKey . ' = "' . $id . '"]';
        }
        if($childs != null && is_array($childs)){
            foreach($childs as $childName=>$childValue)
                $child .= '[' . $childName . '="' . $childValue . '"]';
        }
        if($from == '*')
            $request = $this->_xpath->query('//item'.$attribute.$child);
        else
            $request = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$from.'"]'.$item.$attribute.$child);
       /* if($what == array('*'))
            return $request;
        else{
            return $this->_getChildValue($what[0], $request);
        }*/
		return $request;
    }

    private function _arrayToNode($node){
        if(!is_array($node) || !in_array($node['name'], array($this->_tableName, $this->_itemName)))
            return;
        $element = $this->_doc->createElement($node['name']);
        if(isset($node['attributes'])){
            foreach($node['attributes'] as $attributeName=>$attributeValue){
                if($attributeName != '')
                $element->setAttribute($attributeName, htmlspecialchars(stripslashes($attributeValue)));
            }
        }
        if(isset($node['childs'])){
            foreach($node['childs'] as $childName=>$childValue){
                if($childName != ''){
                    $newElement = $this->_doc->createElement($childName, $childValue);
                    $element->appendChild($newElement);
                }
            }
        }
        return $element;
    }



    /**
     * Allows you to insert a node into your DB thanks to an array
     * @param $node array with 'name' 'attributes' and 'childs'
     * @param $table string in which node you want to put it. By default, the root of the xml file
     * @param $position string 'before' or 'after'
     * @return bool
     */
    private function _insertItem($id = null, $attributes = null, $childs = null, $table, $position = null){
        if($id == null && $this->isTableAI($table)){
            $id = $this->_getNewIncrement($table);
        }
        else if(($id == null && !$this->isTableAI($table)) || ($id != null && $this->isTableAI($table)))
            return false;

        if($attributes == null)
            $attributes = array($this->_primaryKey=>$id);
        else
            $attributes += array($this->_primaryKey=>$id);
        if($this->tableAlreadyExists($table) && !$this->pkAlreadyExists($id, $table))
            return $this->_insert(array('name'=>$this->_itemName, 'attributes'=>$attributes, 'childs'=>$childs), $table, $position);
        return false;
    }

    // TODO $position
    private function _insert(array $node, $table = null, $position = null){
        if(isset($node[0]))
            $node = $node[0];
        if(!is_array($node) || !isset($node['name']) || !isset($node['attributes'])){
            throw new Exception('The node is not well formated.');
        }
        // Creating the node from an array
        $element = $this->_arrayToNode($node);

        // Inserting the node into the DB
        // case : creation of a new table
        if($table == null &&  !$this->tableAlreadyExists($node['name'])){
            $this->_doc->firstChild->appendChild($element);
        }else if($table != null){
        // case : insertion into the end of table
            if(!$this->tableAlreadyExists($table) || $this->pkAlreadyExists($node['attributes'][$this->_primaryKey], $table)){
                return false;
            }
            $tempTable = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$table.'"]/item');
            // $totalItemInTable = $this->selectAllFromTable($table, 'count');
            $totalItemInTable = $tempTable->length;
            if($position == null || $position < $totalItemInTable){
                $request = $this->_xpath->query('//' .  $this->_tableName . '[@name = "'.$table.'"]');
                $request->item(0)->appendChild($element);
            }else{
                $itemsAfter = $this->selectAllFromTable($table, 'node');
                $itemAfter = $itemsAfter->item($position-1);
                $itemAfter->parentNode->insertBefore($element, $itemAfter);
            }
        }else{
            return false;
        }
        return $this->commit();
    }

    /**
     *
     * @param $table string
     * @param $oldAttribute string name of the attribute you want to change
     * @param $newAttribute array name/value of the attribute you want to add
     * @param $forceInsert bool
     * @return bool
     */
    public function updateItemAttribute($table, $oldAttribute, $newAttribute, $forceInsert = false){
        $request = $this->select($table, null, array($oldAttribute[0]=>$oldAttribute[1]), null, 'node', '/'.$this->_itemName);
        if($request->length == 1){
            if(!$forceInsert){
                $request->item(0)->setAttribute($oldAttribute[0],$newAttribute[1]);
            }else{
                $request->item(0)->setAttribute($newAttribute[0],$newAttribute[1]);
            }
            return $this->commit();
        }
        else
            return false;
    }
    
    private function _updateItemID($table, $node, $newAttribute, $forceInsert = false){
        if($this->pkAlreadyExists($node[0]["attributes"][$this->_primaryKey], $table) && $newAttribute != $node[0]["attributes"][$this->_primaryKey]){
            $request = $this->_select(array('*'), $table, $node[0]["attributes"][$this->_primaryKey], null, $item = '/'.$this->_itemName);
            $request->item(0)->setAttribute($this->_primaryKey,$newAttribute);
            return true;
        }
        else return false;
    }

    /**
     *
     * @param $table string
     * @param $value string new value of the node
     * @return bool
     */
    public function updateItemValue($table, $attribute = null, $child = null, $value){
        $request = $this->select($table, null, array($attribute[0]=>$attribute[1]), $child, 'node', '/'.$this->_itemName);
        //$request = $this->_xpath->query('//'.$node.'[@' . $attribute[0] . ' = "' . $attribute[1] . '"]');
        if($request->length == 1){
            $request = $request->item(0);
            $newText = new DOMText($value);
            $request->removeChild($request->firstChild);
            $request->appendChild($newText);
            return $this->commit();
        }
        else
            return false;
    }

    public function updateChildValue($table, $node, $child, $value){
        if($node->length == 1){
            $node = $node->item(0);
            $newChild = $this->_doc->createElement($child, $value);
            $old_element_childNodes = $node->childNodes;
            $length = $old_element_childNodes->length;
            $index = 0;
            for($i = 0; $i < $length; $i++)
            {
                if($old_element_childNodes->item($i)->nodeName == $child){
                    $index = $i;
                    break;
                }
            }
            //$request = $node->getElementsByTagName($child)->item(0);
            if($node->replaceChild($newChild, $old_element_childNodes->item($index)))
            return $this->commit();
        }
        return false;
    }
    
    private function _updateChildValue($table, $node, $child, $value){
        if($node->length == 1){
            $node = $node->item(0);
            $value = htmlspecialchars(stripslashes($value));
            $newChild = $this->_doc->createElement($child, $value);
            $old_element_childNodes = $node->childNodes;
            $length = $old_element_childNodes->length;
            $index = 0;
            for($i = 0; $i < $length; $i++)
            {
                if($old_element_childNodes->item($i)->nodeName == $child){
                    $index = $i;
                    break;
                }
            }
            //$request = $node->getElementsByTagName($child)->item(0);
            if($node->replaceChild($newChild, $old_element_childNodes->item($index)))
            return $this->commit();
        }
        return false;
    }
    
    /**
     * Delete an entry
     * @param $table name of the table in which the entry is
     * @param $id $attributes array where condition(s)
     * @return bool
     */
    private function _delete($table, $id = null){
        if($id != null)
            $request = $this->_select (array('*'), $table, $id, null, '/'.$this->_itemName);
            //$request = $this->selectFromPK($table, $id, 'node')->item(0);
        else
            $request = $this->_select (array('*'), $table);
        if($request == null)
            return false;
        else
            $request = $request->item(0);
        try{
            $request->parentNode->removeChild($request);
        }catch(Exception $e){
            echo $e->getMessage();
            return false;
        }
        return $this->commit();
    }

    public function move($node, $to, $position = null){
        $this->_buffer = $node;
        if($this->deleteNode($node)){
            $nodeArray = $this->requestToArray($this->_buffer);
            return $this->_insert($nodeArray, $to, $position);
        }else
            return false;
    }
}