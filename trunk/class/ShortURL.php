<?
/**
 * Copyright ou © ou Copr. Clément Desmidt - Shikiryu, 14/02/2011
 * 
 * shikiryu+url [ at ] gmail [ dot ] com
 * 
 * Ce logiciel est un programme informatique servant à [rappeler les
 * caractéristiques techniques de votre logiciel]. 
 * 
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
 * sur le site "http://www.cecill.info".
 * 
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,  le
 * titulaire des droits patrimoniaux et les concédants successifs.
 * 
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant 
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  du
 * logiciel à leurs besoins dans des conditions permettant d'assurer la
 * sécurité de leurs systèmes et ou de leurs données et, plus généralement, 
 * à l'utiliser et l'exploiter dans les mêmes conditions de sécurité. 
 * 
 * Le fait que vous puissiez accéder à cet en-tête signifie que vous avez 
 * pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
 * termes.
 * ---------------------------------------
 * Copyright or © or Copr. Clément Desmidt - Shikiryu, 14/02/2011
 * 
 * shikiryu+url [ at ] gmail [ dot ] com
 * 
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use, 
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info". 
 * 
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability. 
 * 
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or 
 * data to be ensured and,  more generally, to use and operate it in the 
 * same conditions as regards security. 
 * 
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
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