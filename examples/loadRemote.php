<?php

require_once __DIR__.'/../source/ConvertPdfToImage.php';
use fmihel\ConvertPdfToImage\ConvertPdfToImage;

$time_start = microtime(true);
//echo '<html><head><style>body{color:gray}</style></head>';


$pdf = 'https://windeco.su/betta/source/reports/report.php?token=LD078492M46B8ON7&ID_ORDER=262&NOM_ORDER=1455&viewAs=order&vnznxmf=uzmTBzC';
$out = './_result/remote.jpg';


$cpi = new ConvertPdfToImage($pdf); 
$cpi->out(['page'=>0,'resolution'=>144,'compression'=>100,'needHeader'=>true,'format'=>'png']);


$time_end = microtime(true);
$time = $time_end - $time_start;


//echo 'dT: '.$time.' [s]<br>';

?>