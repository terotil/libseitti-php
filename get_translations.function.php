<?php
function &get_translations($lang, $file='trans.txt') {
  $tekstit = array();
  // Käännökset ovat ./trans.txt -tiedostossa tähän tapaan sana/rivi.
  //    hae|Hae|Search|Sök
  $rivit = file($file);
  // Kielet löytyvät ensimmäiseltä riviltä (otsikkorivi).  Shiftataan
  // kerran, koska ensimmäisessä sarakkeessa on kunkin käännöksen avain,
  // eikä käännöstä itseään.
  $kielet = explode('|', array_shift($rivit)); array_shift($kielet);
  // Haetaan monennessako sarakkeessa käytetty kieli taulukossa on
  $lang_col = 0;
  foreach ( $kielet as $nro => $kieli ) {
    if ( $lang == $kieli ) { $lang_col = $nro; break; }
  }
  // Tuodaan tekstit kyseisestä sarakkeesta taulukkoon.  Indeksoidaan
  // ensimmäisessä sarakkeessa olevalla avaimella.
  $tekstit = array();
  foreach ( $rivit as $kaannokset ) {
    $kaannokset = rtrim($kaannokset);
    $kaannokset = explode('|', $kaannokset);
    $key = array_shift($kaannokset);
    $tekstit[$key] = $kaannokset[$lang_col];
  }
  return $tekstit;
}
?>
