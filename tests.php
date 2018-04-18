<?php

function testAssertEqual($name, $startVal, $endVal) {
		$st = $name."... ";
		if($startVal === $endVal) {
			$st .= '<font color="green">OK</font><br />';
		} else {
			$st .= '<font color="red">FAIL!</font> ('.$startVal.' != '.$endVal.')<br />';
		}
		return $st;
}

require_once('UtilParser.php');
$srvParser = new UtilParser(); /* @var $srvParser UtilParser */

// ------------ tests ----------------

echo "<br /><b>Tests for parsing S-expressions</b><br />";

$sexp = $srvParser->parseSexp("(+ 1 2) ; asdfk asdhfjk hasdfkjhsd");
echo testAssertEqual("Parse1", count($sexp), 3);
echo testAssertEqual("Parse2", $sexp[0], "+");

$sexp = $srvParser->parseSexp("(+ 1 2)", true);
echo testAssertEqual("Parse3", count($sexp), 3);
echo testAssertEqual("Parse4", $sexp[0], "__+");

$sexp = $srvParser->parseSexp("(\"+\" 1 2)", true);
echo testAssertEqual("Parse3a", count($sexp), 3);
echo testAssertEqual("Parse4a", $sexp[0], "+");

$sexp = $srvParser->parseSexp("(+ 1 2)", true);
$res = $srvParser->serializeToSexp($sexp, true);
echo testAssertEqual("Serialize1", $res, "(+ 1 2)");

$sexp = $srvParser->parseSexp("(+ a 2 (c \"da\"))", true);
$res = $srvParser->serializeToSexp($sexp, true);
echo testAssertEqual("Serialize2", $res, "(+ a 2 (c \"da\"))");

// not symbol parsing
$sexp = $srvParser->parseSexp("(+ a 2 (c \"da\"))");
$res = $srvParser->serializeToSexp($sexp);
echo testAssertEqual("Serialize3", $res, "(+ a 2 (c da))");

$sexp = $srvParser->parseSexp("(+ (a) 1 2)", true);
$res = $srvParser->serializeToSexp($sexp);
echo testAssertEqual("Serialize4", $res, "(__+ (__a) 1 2)");

$sexp2 = $srvParser->sexpRemoveAddPrefix($sexp);
$res = $srvParser->serializeToSexp($sexp2);
echo testAssertEqual("RemoveAddPrefix1", $res, "(+ (a) 1 2)");

echo "<br /><b>Tests for processing TextGen Script</b><br />";

$res = $srvParser->sexpToPlainText($srvParser->parseSexp("(a (b c) d)"));
echo testAssertEqual("SExpToPlain1", $res, "abcd");

$res = $srvParser->tgenStrSexp("(a b c)");
echo testAssertEqual("TGen1", $res, "abc");

$res = $srvParser->tgenStrSexp("(a (b c) d)");
echo testAssertEqual("TGen2", $res, "abcd");

$res = $srvParser->sgenStrSexp("(a (b c) d)");
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGen2", $resStr, "(a (b c) d)");



$res = $srvParser->sgenStrSexp("(a (#r b c) d)");
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenR3", $resStr == "(a b d)" || $resStr == "(a c d)", true);

$res = $srvParser->tgenStrSexp("(a (#r b c) d)");
echo testAssertEqual("TGenR3", $res == "abd" || $res == "acd", true);

$res = $srvParser->tgenStrSexp("(a (#r b) d)");
echo testAssertEqual("TGenR4", $res == "abd", true);

$res = $srvParser->tgenStrSexp("(a (p (#r b c)) d)");
echo testAssertEqual("TGenR5", $res == "apbd" || $res == "apcd", true);



$res = $srvParser->sgenStrSexp('(a (#plainText "b" "c") d)');
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGen1Plain", $resStr, '(a "bc" d)');

$res = $srvParser->sgenStrSexp('("a" (#varGet "x"))',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenVarGet1", $resStr, '("a" "b")');

$res = $srvParser->sgenStrSexp('("a" (#varGet "x") (#varSet Y "d") (#varGet Y))',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenVarGetSet1", $resStr, '("a" "b" "d")');

$res = $srvParser->sgenStrSexp('("a" (#varSet Y ("d" "c")) (#rvarGet Y))');
$resStr = $srvParser->serializeToSexp($res, true);
echo $resStr. " - ";
echo testAssertEqual("SGenRVarGetSet1", $resStr == '("a" "d")' || $resStr == '("a" "c")', true );

$res = $srvParser->sgenStrSexp('("a" @x)',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenVarGetSetCompact1", $resStr, '("a" "b")');

$res = $srvParser->sgenStrSexp('("a" @x (#varSet "y" "d") @y)',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenVarGetSetCompact2", $resStr, '("a" "b" "d")');

$res = $srvParser->sgenStrSexp('("a" (#ifVarEq "x" "b" "tr" "fal"))',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenIfVarEq1", $resStr, '("a" "tr")');

$res = $srvParser->sgenStrSexp('("a" (#ifVarEq "x" "c" "tr" "fal"))',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo testAssertEqual("SGenIfVarEq2", $resStr, '("a" "fal")');

$res = $srvParser->sgenStrSexp('("a" (#ifVarEq "x" (#r "b" "c") "tr" "fal"))',array(), array("x" => "b"));
$resStr = $srvParser->serializeToSexp($res, true);
echo $resStr. " - ";
echo testAssertEqual("SGenIfVarEq3", $resStr == '("a" "tr")' || $resStr == '("a" "fal")', true);



?>