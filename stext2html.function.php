<?php
/*****************************************************************************
 * Seitti-formatted iso-8859-15 & windows-1252 (plain)text to HTML -conversion
 * 2004-07-29 Tero Tilus <tero@tilus.net>
 *
 * $Id: stext2html.function.php,v 1.3 2006/04/11 05:34:18 mediaseitti Exp $
 *
 * 2004-07-30 Fixes windows-1252 peculiarities on codes 128-159
 * 2004-08-14 Ultra lightweight spamharvester protection by entitification
 */
function stext2html($stext) {

  $repl = array('&' => '&amp;',
		'"' => '&quot;',     
		'<' => '&lt;',
		'>' => '&gt;',
		'©' => '&copy;',
		'®' => '&reg;',
		'±' => '&plusmn;',
		"\244" => '&euro;',
		'¹' => '<sup>1</sup>',
		'²' => '<sup>2</sup>',
		'³' => '<sup>3</sup>',
		// Fix windows charset   char                     text repr.
		"\200" => '&euro;',   // euro sign                e
		"\202" => '&sbquo;',  // baseline single quote    '
		"\203" => '&fnof;',   // florin                   f
		"\204" => '&bdquo;',  // baseline double quote    "
		"\205" => '&hellip;', // ellipsis                 ...
		"\206" => '&dagger;', // dagger                   k. or ¹
		"\207" => '&Dagger;', // double dagger            ²
		"\210" => '&circ;',   // circumflex accent        ^
		"\211" => '&permil;', // permile                  o/oo
		"\212" => '&Scaron;', // S Hacek                  Sh or SH
		"\213" => '&laquo;',  // left single guillemet    "
		"\214" => '&OElig;',  // OE ligature              Oe or OE
		"\216" => '&#381;',   // Z Hacek                  Zh or ZH
		"\221" => '&lsquo;',  // left single quote        '
		"\222" => '&rsquo;',  // right single quote       '
		"\223" => '&ldquo;',  // left double quote        "
		"\224" => '&rdquo;',  // right double quote       "
		"\225" => '&bull;',   // bullet                   o or -
		"\226" => '&ndash;',  // endash                   -
		"\227" => '&mdash;',  // emdash                   -
		"\230" => '&tilde;',  // tilde accent             ~
		"\231" => '&trade;',  // trademark ligature       TM
		"\232" => '&scaron;', // s Hacek                  sh
		"\233" => '&rsaquo;', // right single guillemet   "
		"\234" => '&oelig;',  // oe ligature              oe
		"\236" => '&#382;',   // z Hacek                  zh
		"\237" => '&Yuml;'    // Y Dieresis               Y
		);
  foreach ( $repl as $s => $r ) {
    $stext = str_replace($s, $r, $stext);
  }

  // Defined hyperlinks
  $stext = preg_replace('/\[\[([^\[\]|]+)(\|([^\[\]]+))?\]\]/e',
			'html_href("\1", "\3")', $stext);
  // URLs
  $stext = preg_replace('|(\s)([a-z]+://[^\s]+[^\s,\.\:])([\s,\.\:\?\!])|e',
			'"\1".html_href("\2")."\3"', $stext);
  // Emails
  // 'mailto:' == html_decode('&#x6d;&#x61;&#x69;&#x6c;&#x74;&#x6f;&#x3a;')
  // ...to provide a little protection against mail harvesters
  $stext = preg_replace('|(\s)([^\s]+@[^\s]+[^\s,\.\:\?\!])([\s,\.\:\?\!])|e',
			'"\1".html_href("&#x6d;&#x61;&#x69;&#x6c;&#x74;&#x6f;&#x3a;\2", "\2")."\3"', $stext);

  // Superscript
  $stext = preg_replace('/\^([^\s]+)/U', '<sup>\1</sup>', $stext);

  // Bold
  $stext = preg_replace('/(^|\s)\*((?:\S+\s+){0,4}\S+)\*([\s,\.\:\?\!)]|$)/U',
			'\1<b>\2</b>\3',
			$stext);
  // Italic
  $stext = preg_replace('/(^|\s)\/((?:\S+\s+){0,4}\S+)\/([\s,\.\:\?\!)]|$)/U',
			'\1<i>\2</i>\3',
			$stext);
  // Underline
  $stext = preg_replace('/(^|\s)_((?:\S+\s+){0,4}\S+)_([\s,\.\:\?\!)]|$)/U',
			'\1<u>\2</u>\3',
			$stext);
  // Monospace
  $stext = preg_replace('/\(\((.*)\)\)/Us',
			'<tt>\1</tt>',
			$stext);
  // Block-context assumed
  //  FIXME: would stext2html_inline() be needed?
  // Paragraphs (only for multi-line texts)
  //  if ( ereg("[\r\n]", $stext) ) {
    // Unify linebreaks
    $stext = str_replace("\r\n", "\n", $stext);
    $stext = str_replace("\r", "\n", $stext);
    // Find paragraphs
    $stext = '<p>'.preg_replace('/\n\n+/', "</p>§§§p§§§<p>", $stext).'</p>';
    //  }

  // Headings
  $stext = preg_replace('|<p>====+ *\n([^\n]+)\n====+ *</p>|', 
			'<h1>\1</h1>', $stext);
  $stext = preg_replace('|<p>([^\n§]+)\n====+ *</p>|', '<h2>\1</h2>', $stext);
  $stext = preg_replace('|<p>([^\n§]+)\n----+ *</p>|', '<h3>\1</h3>', $stext);

  // Bulleted lists
  $stext = preg_replace('/<p>(\s*(-\s+[^\n]+\s*)+)<\/p>/Ue',
			 '"<ul>".html_itemize("\1")."</ul>"', $stext);

  $repl = array("\n" => "<br />\n",  // Forced linebreaks
		'§§§p§§§' => "\n\n", // Encoded empty lines
		"\\'" => "'",
		'@' => '&#64;', /*  A modest harvester protection */
		'mailto:' => '&#x6d;&#x61;&#x69;&#x6c;&#x74;&#x6f;&#x3a;');
  
  foreach ( $repl as $s => $r ) {
    $stext = str_replace($s, $r, $stext);
  }

  return $stext;
}

// Create href
function html_href($url, $txt=false) {
  if ( ! $txt ) $txt = $url;
  return "<a href=\"{$url}\">{$txt}</a>";
}

// Itemize given Seitti-formatted list
function html_itemize($stext) {
  return preg_replace('/(^|\n)-\s+/',
		      " <li>", $stext);
}

?>
