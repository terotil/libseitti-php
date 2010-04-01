<?php
/*****************************************************************************
 * Seitti-formatted iso-8859-15 (plain)text to LaTeX -conversion
 * 29.7.2004 Tero Tilus <tero@tilus.net>
 *
 * $Id: stext2latex.function.php,v 1.3 2006/04/11 05:47:49 mediaseitti Exp $
 */
function stext2latex($stext, $hrefcallback=false) {
  $stext = latex_encode($stext);
  
  // Bulleted lists
  $stext = preg_replace('/\n\n+(\s*(-\s+[^\n]+\s*)+)\n\n+/Ue',
			 '"\n\n\\begin{itemize}".latex_itemize(\'\1\').'.
			 '"\\end{itemize}\n\n"', "\n\n".$stext."\n\n");

  // Format links only when callback for hyperlink formatting is given
  if ( $hrefcallback ) {
    // Defined hyperlinks
    $stext = preg_replace('/\[\[([^\[\]|]+)(\|([^\[\]]+))?\]\]/e',
			  $hrefcallback.'("\1", "\3")', $stext);
    // URLs
    $stext = preg_replace('|(\s)([a-z]+://[^\s]+[^\s,\.\:])([\s,\.\:?!)])|e',
			  '"\1".'.$hrefcallback.'("\2")."\3"', $stext);
    // Emails
    $stext = preg_replace('|(\s)([^\s]+@[^\s]+[^\s,\.\:?!])([\s,\.\:?!)])|e',
			  '"\1".'.$hrefcallback.'("mailto:\2", "\2")."\3"', 
			  $stext);
    // URLs
    $stext = preg_replace('|[a-z]+://[^\s]+[^\s,\.\:]|e',
			  $hrefcallback.'("\0")', 
			  $stext);
    // Emails
    $stext = preg_replace('|[^\s]+@[^\s]+[^\s,\.\:]|e',
			  $hrefcallback.'("mailto:\0", "\0")', $stext);
  }

  // Superscript
  $stext = preg_replace('/\\\\\^\{\}([^\s]+)/U', '^{\1}', $stext);

  // Bold
  $stext = preg_replace('/(^|\s)\*((?:\S+\s+){0,4}\S+)\*([\s,\.\:\?\!)]|$)/U',
                         '\1\textbf{\2}\3',
                         $stext);
  // Italic
  $stext = preg_replace('/(^|\s)\/((?:\S+\s+){0,4}\S+)\/([\s,\.\:\?\!)]|$)/U',
                         '\1\textit{\2}\3',
                         $stext);
  // Underline
  $stext = preg_replace('/(^|\s)\\\\_((?:\S+\s+){0,4}\S+)'.
			 '\\\\_([\s,\.\:\?\!)]|$)/U',
                         '\1\ul{\2}\3',
                         $stext);
  // Monospace
  $stext = preg_replace('/\(\((.*)\)\)/Us', '\1\texttt{\2}\3', $stext);

  // Unify linebreaks
  $stext = str_replace("\r\n", "\n", $stext);
  $stext = str_replace("\r", "\n", $stext);

  // Headings
  $stext = preg_replace('|^====+\n([^\n]+)\n====+$|m','\section{\1}', $stext);
  $stext = preg_replace('|^([^\n]+)\n====+ *$|m','\subsection{\1}', $stext);
  $stext = preg_replace('|^([^\n]+)\n----+ *$|m','\subsubsection{\1}', $stext);

  // Forced linebreaks
  $stext = preg_replace('/\n\n+/', '§§§p§§§', $stext);
  $stext = str_replace("\n", "\\\\\n", $stext);
  $stext = str_replace('§§§p§§§', "\n\n", $stext);

  return $stext;
}

// Itemize given Seitti-formatted list
function latex_itemize($stext_bullets) {
  $stext_bullets = preg_replace('/(^|\n)-\s+/',
				' \item ', $stext_bullets);
  $stext_bullets = str_replace("\n", "", $stext_bullets);
  return $stext_bullets;
}
?>
