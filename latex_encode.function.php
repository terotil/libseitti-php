<?php
/*****************************************************************************
 * LaTeX-encode iso-8859-15 plain text
 * 29.7.2004 Tero Tilus
 *
 * $Id: latex_encode.function.php,v 1.3 2006/04/11 05:24:23 mediaseitti Exp $
 *
 * TODO: use gnu recode instead!
 */
function latex_encode($plaintext) {
  // Unify different linebreaks
  $plaintext = str_replace("\r\n", "\n", $plaintext);
  $plaintext = str_replace("\r", "\n", $plaintext);

  // Temporarily translate to sequences the characters which need more
  // attention than plain backslash quoting
  $repl1 = array("\\" => '§§§§b§§§§',
		 '~' => '§§§§t§§§§',
		 '^' => '§§§§c§§§§');
  foreach ( $repl1 as $s => $r ) {
    $plaintext = str_replace($s, $r, $plaintext);
  }

  // These are done with ordinary backslash quoting
  $plaintext = addcslashes($plaintext, '$&%#_{}');

  // Handle previously translated characters
  $repl2 = array('§§§§b§§§§' => '$\backslash$',
		 '§§§§t§§§§' => '\~{}',
		 '§§§§c§§§§' => '\^{}');
  foreach ( $repl2 as $s => $r ) {
    $plaintext = str_replace($s, $r, $plaintext);
  }

  // The previous steps still leave us quite a few corrections
  $repl3 = array('ß' => '\ss ',
		 'µ' => '$\mu$',
		 '©' => '\copyright',
		 '®' => '(R)',
		 '±' => '$\pm$',
		 "\244" => 'e',  // \usepackage{eurosans} -> \euro
		 '¹' => '$^1$',
		 '²' => '$^2$',
		 '³' => '$^3$',
		 '"' => "''",
		 '<' => '$<$',
		 '>' => '$>$',
		 "\\'" => "'",
		 '´' => "'",
		 // '`' => "'",
		 // Fix windows charset    char                     text repr.
		 "\200" => 'e',         // euro sign                e
		 // \usepackage{textcomp}
		 //"\202" => '\textquotestraightbase',
		                        // baseline single quote    '
		 "\203" => '$f$',       // florin                   f
		 // \usepackage{textcomp}
		 //"\204" => '\textquotestraightdblbase', 
                                        // baseline double quote    "
		 "\204" => "''",
		 "\205" => '\ldots',    // ellipsis                 ...
		 "\206" => '\dag',      // dagger                   k. or ¹
		 "\207" => '\ddag',     // double dagger            ²
		 "\210" => '\^{}',      // circumflex accent        ^
		 // \usepackage{textcomp}
		 //"\211" => '\textperthousand', // permile           o/oo
		 "\211" => 'o/oo',
		 "\212" => '\check{S}', // S Hacek                  Sh or SH
		 "\213" => '``',        // left single guillemet    " FIXME
		 "\214" => '\OE',       // OE ligature              Oe or OE
		 "\216" => '\check{Z}', // Z Hacek                  Zh or ZH
		 "\221" => "`",         // left single quote        '
		 "\222" => "'",         // right single quote       '
		 "\223" => "``",        // left double quote        "
		 "\224" => "''",        // right double quote       "
		 // \usepackage{textcomp}
		 //"\225" => '\textopenbullet', // bullet             o or -
		 "\225" => '-',
		 "\226" => '--',        // endash                   -
		 "\227" => '---',       // emdash                   -
		 "\230" => '\~{}',      // tilde accent             ~
		 // \usepackage{textcomp}
		 //"\231" => '\texttrademark', // trademark ligature  TM
		 "\231" => 'TM',
		 "\232" => '\check{s}', // s Hacek                  sh
		 "\233" => "''",        // right single guillemet   " FIXME
		 "\234" => '\oe',       // oe ligature              oe
		 "\236" => '\check{z}', // z Hacek                  zh
		 "\237" => '\"Y'        // Y Dieresis               Y
		 );
  foreach ( $repl3 as $s => $r ) {
    $plaintext = str_replace($s, $r, $plaintext);
  }

  return $plaintext;
}
?>
