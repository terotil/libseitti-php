<?php
/*****************************************************************************
 * Tag-skipping substr()
 * 29.7.2004 Tero Tilus <tero@tilus.net>
 *
 * $Id: substr_skiptags.function.php,v 1.3 2006/10/05 13:43:36 mediaseitti Exp $
 *
 * Changes:
 * Count entities as single character, 9.2.2005 Tero
 *
 * $dots is inserted at the end of the result text and inside
 * $dots_depth of tags which have been left open
 *
 * substr_skiptags('<p>as<i>df</i></p>', 1, 2, '...', 1) == 's<i>d...</i>'
 * substr_skiptags('<p>as<i>df</i></p>', 0, 3, '.', 1) == '<p>as<i>d.</i></p>'
 */
define('OTAG',        1); // input cursor at tag opening '<'
define('TAG_OTHER',   2); // input cursor inside tag and outside tagname
define('STAG',        3); // input cursor at start of starttag
define('ETAG',        4); // input cursor at start of endtag
define('TEXT',        5); // input cursor at 'plain text'
define('ENT',         6); // input cursor at entity (among plain text)
define('IN_SUBSTR',  11); // input cursor inside substring
define('OUT_SUBSTR', 12); // input cursor outside substring

// return true if tag is expected to have a closing tag
function needtoclose($tag) { 
  static $dontclosetags = array('br', 'hr');
  return ! in_array($tag, $dontclosetags);
}

function substr_skiptags(&$string, $start, $length=false, $dots=array(''), 
			 $dots_depth=0) {

  $in = 0;
  $out = 0;
  $strlen = strlen($string);
  $in_state = TEXT;
  $out_state = OUT_SUBSTR;
  $tags_open = array();
  while ( true ) {
    switch ( $in_state ) {
    case OTAG:
      $tag = '';
      switch ( $string[$in] ) {
      case '/': $in_state = ETAG;  break;
      default: $tag .= $string[$in]; $in_state = STAG; 
      }
      break;
    case TAG_OTHER:
    case STAG:
    case ETAG:
      switch ( ereg('[a-zA-Z]', $string[$in]) ) {
      case false:
	if ( $in_state == STAG && $out_state == IN_SUBSTR ) {
	  if ( $string[$in] == '>' && $string[$in-1] == '/' ) {
	    // Starttag was a endtag too, like <startend/>, ending is
	    // not to be expected
	  } else {
	    // push to stack of open tags
	    array_push($tags_open, $tag); 
	  }
	  $in_state = TAG_OTHER;
	}
	if ( $in_state == ETAG ) {
	  while ( $st = array_pop($tags_open) ) {
	    if ( $st == $tag ) break;
	  }
	  $in_state = TAG_OTHER;
	}
	if ( $string[$in] == '>' ) {
	  $in_state = TEXT;
	}
	break;
      case true:
	$tag .= $string[$in]; 
      }
      break;
    case TEXT:
      if ( $out == $start && $out_state == OUT_SUBSTR ) {
	$substr_start = $in;
	$out_state = IN_SUBSTR;
      }
      switch ( $string[$in] ) {
      case '<':	$in_state = OTAG; break;
      case '&': $in_state = ENT; break;
      default: $out++; break;
      }
      break;
    case ENT:
      if ( $string[$in] == ';' ) {
	// end of entity
	$in_state = TEXT;
	$out++;
      }
      break;
    }
    $in++;
    // End of the input string was reached
    if ( $in >= $strlen ) {
      $wastruncated = false;
      break;
    }
    // End of the substring was reached
    if ( $length !== false && ($out - $start >= $length) ) { 
      $wastruncated = true;
      break;
    }
  }
  $closing = array_map(create_function('$s','return "</$s>";'),
		       array_reverse(array_filter($tags_open, 'needtoclose')));
  $depth = count($closing)-$dots_depth;
  if ( $depth < 0 ) $depth = 0;
  if ( $wastruncated ) array_splice($closing, $depth, 0, $dots);
  return substr($string, $substr_start, $in-$substr_start) . 
    join('', $closing);
}
?>