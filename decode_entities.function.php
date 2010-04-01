<?php
/*****************************************************************************
 * Decode literal and numeric html entities to latin1 or latin9
 * Undecodable entities are left in place
 *
 * $Id: decode_entities.function.php,v 1.3 2006/04/11 05:17:51 mediaseitti Exp $
 *
 * Differences between latin1 and latin9
 * http://www.cs.tut.fi/~jkorpela/latin9.html
 *
 *             latin1           latin9
 * dec oct hex unicode and name unicode and name
 * 164 244 A4  U+00A4 currency  U+20AC euro sign
 * 166 246 A6  U+00A6 bar       U+0160 latin capital letter s with caron
 * 168 250 A8  U+00A8 umlaut    U+0161 latin small letter s with caron
 * 180 264 B4  U+00B4 acute     U+017D latin capital letter z with caron
 * 184 270 B8  U+00B8 cedilla   U+017E latin small letter z with caron
 * 188 274 BC  U+00BC 1/4       U+0152 latin capital ligature oe
 * 189 275 BD  U+00BD 1/2       U+0153 latin small ligature oe
 * 190 276 BE  U+00BE 3/4       U+0178 latin capital letter y with diaeresis
 */
function html_chr($code, $latin9=false) {
  if ( $code < 256 ) {
    return chr($code);
  }
  if ( $latin9 ) {
    // http://www.cs.tut.fi/~jkorpela/latin9.html
    $utf82latin9 = array(0x20AC => "\244",
			 0x0160 => "\246",
			 0x0161 => "\250",
			 0x017D => "\264",
			 0x017E => "\270",
			 0x0152 => "\274",
			 0x0153 => "\275",
			 0x0178 => "\276");
    if ( array_key_exists($code, $utf82latin9) ) {
      return $utf82latin9[$code];
    }
  }
  return "&#$code;";
}

function decode_literal_entities($string, $latin9=false) {
  // original from http://fi.php.net/manual/en/function.html-entity-decode.php
  $trans_tbl = get_html_translation_table(HTML_ENTITIES);
  if ( $latin9 ) {
    $trans_tbl["\244"] = '&euro;';
    $trans_tbl["\246"] = '&Scaron;';
    $trans_tbl["\250"] = '&scaron;';
  // no literal entity defined for Z caron and z caron, see
  // http://www.utf8-chartable.de/unicode-utf8-table.pl?start=256&utf8=string-literal&htmlent=1
    $trans_tbl["\274"] = '&OElig;';
    $trans_tbl["\275"] = '&oelig;';
    $trans_tbl["\276"] = '&Yuml;';
  }
  $trans_tbl = array_flip($trans_tbl);
  return strtr($string, $trans_tbl);
}

function decode_numeric_entities($string, $latin9=false) {
  // Original work from daniel at brightbyte dot de
  // decimal notation
  $string = preg_replace('/&#(\d+);/me',"html_chr(\\1, $latin9)",$string); 
  // hex notation
  $string= preg_replace('/&#x([a-f0-9]+);/mei',"html_chr(0x\\1, $latin9)",
			$string); 
  return $string;
}

function decode_entities($string, $latin9=false) {
  return decode_numeric_entities(decode_literal_entities($string, $latin9), $latin9);
}

?>