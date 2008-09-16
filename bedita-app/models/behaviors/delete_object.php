<?php
/**
 * 
 * Serve per cancellare un oggetto rispettando e usando le dipendenze
 * imposte dalle foreign key. Viene cancellato solo il record della
 * tabella base, il resto lo fase MySQL.
 * Con Configure viene passato il nome delal tabella radice.
 * 
 * 
 * giangi@qwerg.com
 *
 */

class DeleteObjectBehavior extends ModelBehavior {
	var $config = array();
	
	function setup(&$model, $config = array()) {
		$this->config[$model->name] = $config ;
	}

	/**
	 * Elimina tutte le associazioni, saranno reiserite dopo la cancellazione.
	 * Dati i vincoli (foreignKey)  tra le tabelle in DB, viene forzata la 
	 * cancellazione del record della tabella iniziale, objects
	 *
	 * if specified delete related object too
	 * @return unknown
	 */
	
	function beforeDelete(&$model) {
		$model->tmpAssociations = array();
		$model->tmpTable 		= $model->table ;
		
		$associations = array('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany');
		foreach ($associations as $association) {
			$model->tmpAssociations[$association] = $model->$association ;
			$model->$association = array() ;
		}
		$configure = $this->config[$model->name] ;
		
		if (!empty($configure)) {
			if (is_string($configure)) {
				$model->table = $configure;
			} elseif (is_array($configure) && count($configure) == 1) {
				
				if (is_string(key($configure))) {
					
					$model->table = key($configure);
					if (!empty($configure[$model->table]["relatedObjects"])) {
						$this->delRelatedObjects($configure[$model->table]["relatedObjects"], $model->id);
					}
					
				} else {
					$model->table = array_shift($configure);
				}
			}
		}
		
		$model->table =  (isset($configure) && is_string($configure)) ? $configure : $model->table ;

		// Cancella i riferimenti del'oggetto nell'albero
		if(!class_exists('Tree')){
			App::import('Model','Tree');
		}		
		$tree = new Tree ;
		$tree->del($model->id) ;

		if(!class_exists('SearchText')){
			App::import('Model','SearchText');
		}		
		$st = new SearchText ;
		$st->deleteAll("object_id=".$model->id) ;
		
		return true ;
	}

	/**
	 * Reinserice le associazioni
	 *
	 */
	function afterDelete(&$model) {
		
		// Ripristina le associazioni
		foreach ($model->tmpAssociations as $association => $v) {
			$model->$association = $v ;
		}
		unset($model->tmpAssociations) ;
		
		$model->table = $model->tmpTable ;
		unset($model->tmpTable) ;
	}

	/**
	 * Delete related objects
	 *
	 * @param array $relations: array of relations type. 
	 * 							The object related to main object by a relation in $relations will be deleted 
	 * @param int $object_id: main object that has to be deleted
	 */
	private function delRelatedObjects($relations, $object_id) {

		$o = ClassRegistry::init('BEObject') ;
		
		$res = $o->find("first", array(
									"contain" => array("RelatedObject"),
									"conditions" => array("`BEObject`.id" => $object_id)
									)
							);
		if (!empty($res["RelatedObject"])) {
			
			$conf = Configure::getInstance();
			foreach ($res["RelatedObject"] as $obj) {
				
				if (in_array($obj["switch"],$relations)) {
					
					$modelClass = $o->getType($obj['object_id']);

					$model = ClassRegistry::init($modelClass);
				
					if (!$model->del($obj["object_id"])) 
						throw new BeditaException(__("Error deleting related object ", true), "id: ". $obj["object_id"] . ", switch: " . $obj["switch"]);
				}
				
			}
			
		}
		
	}
}
?>
