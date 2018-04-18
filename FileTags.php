<?php
class FileTags
{
	/**
	 * @var array  
    */
	public $arTags; 
	
	function FileTags($str = "") {
		$this->arTags = array();
		$this->loadFromString($str);
	}
	
	function clear() {
		$this->arTags = array();
	}
	
	function addFromString($str, $delimiter = " ") {
		$str = trim($str);
		if($str == "") return;
		$ar = explode($delimiter, $str);
		for($i = 0; $i < count($ar); $i++) {
			$this->add($ar[$i]);
		}
	}
	
	function loadFromString($str) {
		$this->clear();
		$this->addFromString($str);
	}
	
	function loadFromArray($ar) {
		if(!is_array($ar)) {
			$this->clear();
			return;
		}
		$this->arTags = $ar;
	}
	
	function add($strTag) {
		if(!$this->hasTag($strTag)) {
			array_push($this->arTags, $strTag);
		}
	}
	
	function hasTag($strTag) {
		//if(!is_array($this->arTags)) return false; 
		//if(count($this->arTags) == 0) return false;
		
		if(array_search($strTag, $this->arTags) === false) return false;
		return true; 
	}
	
	function saveToString($delimiter = " ") {
		return implode($delimiter, $this->arTags);
	}
	
	function removeTag($strTag) { 
		$find = array_search($strTag, $this->arTags);
		array_splice($this->arTags, $find, 1);
		
		/* старая неоптимальная, но рабочая версия, через строку
		$st = " ".$this->saveToString()." ";
		$st = str_replace(" ".$strTag." ", " ", $st);
		$this->loadFromString($st);
		*/
	}
	
	function calcDiff($ftOld) {
		/* @var $ftOld FileTags */
		$ftDiff = new FileTags(); /* @var $ftDiff FileTags */
		for($i = 0; $i < count($this->arTags); $i++) {
			if(!$ftOld->hasTag($this->arTags[$i])) {
				$ftDiff->add("add_".$this->arTags[$i]);
			}
		}
		for($i = 0; $i < count($ftOld->arTags); $i++) {
			if(!$this->hasTag($ftOld->arTags[$i])) {
				$ftDiff->add("del_".$ftOld->arTags[$i]);
			}
		}
		return $ftDiff;
		
	}
	
	function findTagStartWith($str) {
		for($i = 0; $i < count($this->arTags); $i++) {
			if(substr($this->arTags[$i],0,strlen($str)) == $str) return $this->arTags[$i];
		}
		return null;
	}
	
	function isIncludeIn($ftOther) {
		/* @var $ftOther FileTags */
		for($i = 0; $i < count($this->arTags); $i++) {
			if(!$ftOther->hasTag($this->arTags[$i])) return false;
		}
		return true;
	}
	
}


?>