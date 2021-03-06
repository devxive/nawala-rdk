<?php

/**
 * Unit
 *
 * @package Less
 * @subpackage tree
 */
class Less_Tree_Unit extends Less_Tree{

	var $numerator = array();
	var $denominator = array();
	public $backupUnit;
	public $type = 'Unit';

	function __construct($numerator = array(), $denominator = array(), $backupUnit = null ){
		$this->numerator = $numerator;
		$this->denominator = $denominator;
		$this->backupUnit = $backupUnit;
	}

	function __clone(){
	}

    /**
     * @see Less_Tree::genCSS
     */
	function genCSS( $output ){

		if( $this->numerator ){
			$output->add( $this->numerator[0] );
		}elseif( $this->denominator ){
			$output->add( $this->denominator[0] );
		}elseif( !Less_Parser::$options['strictUnits'] && $this->backupUnit ){
			$output->add( $this->backupUnit );
			return ;
		}
	}

	function toString(){
		$returnStr = implode('*',$this->numerator);
		foreach($this->denominator as $d){
			$returnStr .= '/'.$d;
		}
		return $returnStr;
	}

	/**
	 * @param Less_Tree_Unit $other
	 */
	function compare($other) {
		return $this->is( $other->toString() ) ? 0 : -1;
	}

	function is($unitString){
		return $this->toString() === $unitString;
	}

	function isLength(){
		$css = $this->toCSS();
		return !!preg_match('/px|em|%|in|cm|mm|pc|pt|ex/',$css);
	}

	function isAngle() {
		return isset( Less_Tree_UnitConversions::$angle[$this->toCSS()] );
	}

	function isEmpty(){
		return !$this->numerator && !$this->denominator;
	}

	function isSingular() {
		return count($this->numerator) <= 1 && !$this->denominator;
	}


	function usedUnits(){
		$result = array();

		foreach(Less_Tree_UnitConversions::$groups as $groupName){
			$group = Less_Tree_UnitConversions::${$groupName};

			foreach($this->numerator as $atomicUnit){
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}

			foreach($this->denominator as $atomicUnit){
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}
		}

		return $result;
	}

	function cancel(){
		$counter = array();
		$backup = null;

		foreach($this->numerator as $atomicUnit){
			if( !$backup ){
				$backup = $atomicUnit;
			}
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) + 1;
		}

		foreach($this->denominator as $atomicUnit){
			if( !$backup ){
				$backup = $atomicUnit;
			}
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) - 1;
		}

		$this->numerator = array();
		$this->denominator = array();

		foreach($counter as $atomicUnit => $count){
			if( $count > 0 ){
				for( $i = 0; $i < $count; $i++ ){
					$this->numerator[] = $atomicUnit;
				}
			}elseif( $count < 0 ){
				for( $i = 0; $i < -$count; $i++ ){
					$this->denominator[] = $atomicUnit;
				}
			}
		}

		if( !$this->numerator && !$this->denominator && $backup ){
			$this->backupUnit = $backup;
		}

		sort($this->numerator);
		sort($this->denominator);
	}


}

