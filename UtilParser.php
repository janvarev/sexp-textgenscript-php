<?php
class UtilParser {

    
    /**
     * Sexp
     * @var Sexp
     */
    var $sexp_parser;
    
    function UtilParser() {
           
           require_once 'Sexp.php';
		   require_once 'FileTags.php';
           $this->sexp_parser = new Sexp();
           $this->sexp_parser->setPrettyPrint(false);
           
    }
    
	function parseSexp($str, $isAddPrefixToSymbols = false) {
		$this->sexp_parser->isAddPrefixToSymbols = $isAddPrefixToSymbols;
		return $this->sexp_parser->parse($str);
	}
	
	function serializeToSexp($sexp, $isAddPrefixToSymbols = false) {
		$this->sexp_parser->isAddPrefixToSymbols = $isAddPrefixToSymbols;
		return $this->sexp_parser->serialize($sexp);
	}
	
	function lscriptIsAtom($sexp) {
		return !is_array($sexp);
	}
	
	function lscriptIsSymbol($sexp) {
		if(is_string($sexp)) {
			
			if(substr($sexp,0,2) == "__") return true;
		}
		return false;
	}
	
	function lscriptExtractSymbol($sexp) {
		return substr($sexp, 2);
	}
	
	function sexpFirst($sexp) {
		return $sexp[0];
	}
	
	function sexpRest($sexp) {
		return array_slice($sexp, 1);
	}
	
	function sexpCons($front, $sexp) {
		//return array_slice($sexp, 1);
		array_splice($sexp, 0, 0, array($front));
		return $sexp;
	}
	
	function arrayCopy( array $array ) {
	        $result = array();
	        foreach( $array as $key => $val ) {
	            if( is_array( $val ) ) {
	                $result[$key] = $this->arrayCopy( $val );
	            } elseif ( is_object( $val ) ) {
	                $result[$key] = clone $val;
	            } else {
	                $result[$key] = $val;
	            }
	        }
	        return $result;
	}
	
	function sexpRemoveAddPrefix($structure) {
		if(!is_array($structure)) {
			if($this->lscriptIsSymbol($structure)) {
				return $this->lscriptExtractSymbol($structure);
			} else {
				return $structure;
			}
			
		} else {
			$ar = array();
			for($i = 0; $i < count($structure); $i++) {
				$ar[$i] = $this->sexpRemoveAddPrefix($structure[$i], $vars);
			}
			return $ar;
		}
	}
	

	// ----------- text generation ---------------
	function tgenStrSexp($strSexp, $arTags = array(), $vars = array()) {
		$sexp = $this->parseSexp($strSexp,true);
		return $this->tgenSexp($sexp,$arTags,$vars);
	}
	
	function tgenSexp($sexp, $arTags = array(), $vars = array()) {
		$ft = new FileTags();
		$ft->loadFromArray($arTags);
		$ft->objVars = $vars;
		if(count($arTags) == 0) { 
			$ft->add("notags");
		}
		return $this->tgenSexpFTInternal($sexp, $ft);
	}
	
	function tgenSexpFTInternal($sexp, $ft) {
		$sexp = $this->sgenSexpFTInternal($sexp, $ft);
		$sexp2 = $this->sexpRemoveAddPrefix($sexp);
		return $this->sexpToPlainText($sexp2);
	}
	
	function sgenStrSexp($strSexp, $arTags = array(), $vars = array()) {
		$sexp = $this->parseSexp($strSexp, true);
		return $this->sgenSexp($sexp,$arTags,$vars);
	}
	
	function sgenSexp($sexp, $arTags = array(), $vars = array()) {
		$res = $this->sgenSexpFull($sexp,$arTags,$vars);
		return $res[0];
	}
	
	function sgenSexpFull($sexp, $arTags = array(), $vars = array()) {
		$ft = new FileTags();
		$ft->loadFromArray($arTags);
		$ft->objVars = $vars;
		if(count($arTags) == 0) { 
			$ft->add("notags");
		}
		$res = $this->sgenSexpFTInternal($sexp, $ft);
		return array($res, $ft->arTags, $ft->objVars);
	}
	
	function sexpToPlainText($sexp) {
		if(is_array($sexp)) {
			if(count($sexp) == 0) { // end of recursion
				return "";
			} else {
				return $this->sexpToPlainText($sexp[0]).$this->sexpToPlainText($this->sexpRest($sexp));
			}
		} else {
			return $sexp;
		}
	}
	
	function sgenSexpFTInternal($sexp, $ft) {
		/* @var $ft FileTags */
		if(is_array($sexp)) {
			if(count($sexp) == 0) { // end of recursion
				return $sexp;
			} else {
				$first = $sexp[0];
				if(is_array($first)) {
					return $this->sexpConsFirstNotNull($this->sgenSexpFTInternal($sexp[0], $ft),$this->sgenSexpFTInternal($this->sexpRest($sexp), $ft));
				} else {
					// atom!
					
					if(substr($first, 0, 3) == "__#") { // special cases
						//$firstR = (substr($first, 0, 2) == "__"?substr($first, 2):$first);
						if($first == "__#r") { // random
							$rand = $this->getRandArrayElement($this->sexpRest($sexp));
							return $this->sgenSexpFTInternal($rand, $ft);
						}
						if($first == "__#plainText") { // to plain text
							$txt = $this->sexpToPlainText($this->sgenSexpFTInternal($this->sexpRest($sexp), $ft));
							return $txt;
						}
						if($first == "__#varGet") { // var get
							return $ft->objVars[$sexp[1]];
						}
						if($first == "__#varSet") { // var set
							$ft->objVars[$sexp[1]] = $this->sgenSexpFTInternal($sexp[2], $ft);
							return null;
						}
						if($first == "__#rvarGet") { // var get and random
							$rand = $this->getRandArrayElement($ft->objVars[$sexp[1]]);
							return $rand;
						}
						if($first == "__#null") { // nullify result
							$this->sgenSexpFTInternal($sexp[1], $ft);
							return null;
						}
						if($first == "__#ifVarEq") { // if var equal
							$varVal = $ft->objVars[$sexp[1]];
							$secVal = $this->sgenSexpFTInternal($sexp[2], $ft);
							if($varVal == $secVal) {
								return $this->sgenSexpFTInternal($sexp[3], $ft);
							} else {
								return $this->sgenSexpFTInternal($sexp[4], $ft);
							}
							
							
						}
					}
					if(substr($first, 0, 3) == "__@") { // get var construction
						$res = $ft->objVars[substr($first, 3)];
						return $this->sexpConsFirstNotNull($res, $this->sgenSexpFTInternal($this->sexpRest($sexp), $ft));
					}
					return $this->sexpConsFirstNotNull($first, $this->sgenSexpFTInternal($this->sexpRest($sexp), $ft));
					
				}
			}
		} else { // just an atom string
			return $sexp;
		}
	}
	
	function sexpConsFirstNotNull($first, $rest) {
		if($first === null) {
			return $rest;
		}
		return $this->sexpCons($first, $rest);
	}
	
	function getRandArrayElement($ar) {
		if(count($ar) > 0) {
			return $ar[rand(0, count($ar)-1)];
		} else {
			return null;
		}
	}
	
	function utilSexpToUTF8($array)
	{
	    foreach($array as $key => $value)
	    {
	        if(is_array($value))
	        {
	            $array[$key] = $this->utilSexpToUTF8($value);
	        }
	        else
	        {
	            $array[$key] = iconv("windows-1251", "utf-8", $value);
	        }
	    }
	
	    return $array;
	}
	

}
?>