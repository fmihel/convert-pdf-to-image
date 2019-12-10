<?php


require_once __DIR__.'/../source/ConvertPdfToImage.php';
use fmihel\ConvertPdfToImage\ConvertPdfToImage;

$pdf = './_data/Primer_pdf_.pdf';
$cpi = new ConvertPdfToImage($pdf); 
$cpi->out(['page'=>0]);


?>