# sexp-textgenscript-php

Small PHP library for working with S-expression and small S-based DSL for text generation

Can be used for:
* parsing S-expressions
* serializing S-expressions
* text generation using TextGen Script (DSL for text generation)

# Use

```
require_once('UtilParser.php');
$srvParser = new UtilParser(); /* @var $srvParser UtilParser */

$sexp = $srvParser->parseSexp("(+ b 1 2 \"a\")", false); // parse to structure [+, b, 1, 2, a]
$sexp = $srvParser->parseSexp("(+ b 1 2 \"a\")", true); // parse to structure [__+, __b, 1, 2, a], adding __ to non-quoted atoms

$sexp = $srvParser->parseSexp("(+ 1 2)", true); // parse to structure [__+, 1, 2]
$res = $srvParser->serializeToSexp($sexp, true); // serializing back - $res must be "(+ 1 2)"

// TextGen Script
$res = $srvParser->tgenStrSexp("(a (#r b c) d)"); // $res == "abd" or $res == "acd"
```

More examples can be found in tests.php file

# Rules for TextGen Script

Elements are
* Text elements - renders "as is"
* Control atom - renders, and render subelements
* List - render and adds subelements

Examples:
* ("a" "b" "c") will be rendered to "abc"
* ("a" (("b") "c")) will be rendered to "abc", too

Control elements:
* #r - render random element of list. Example: ("a" (#r "b" "c")) will be rendered to "ab" or "ac" (randomly)
* #varSet - set var. Example: (#varSet "hero" "elf"). Or: (#varSet "hero" (#r "elf" "dwarf"))
* @\<var\> - render var. Example: ("The greatest hero, the " @hero " will come!")
* #ifVarEq - check var for equality. Example: (#ifVarEq "hero" "elf" ("Elf from forest moves silently..") ("Somebody UNKNOWN comes..."))

Live example (JS) here: http://d.janvarev.ru/sexp/textgenscript-html/

Russian version with a NUMBER of generators: http://janvarev.ru/TGen

TypeScript/JavaScript version: https://github.com/janvarev/sexp-textgenscript-tsjs