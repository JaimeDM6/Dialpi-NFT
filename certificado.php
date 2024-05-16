<?php
require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Dialpi NFT');
$pdf->SetTitle('Certificado de Autenticidad NFT');
$pdf->SetSubject('Certificado NFT');
$pdf->SetKeywords('Dialpi, Certificado, NFT, cripto, ETH');

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 052', PDF_HEADER_STRING);

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$info = array(
    'Name' => 'Dialpi NFT',
    'Location' => 'Bilbao',
    'Reason' => 'Certificado NFT',
    'ContactInfo' => 'https://nftdialpi.ddns.net',
    );

$pdf->setSignature('file://certs/tcpdf.crt', 'file://certs/tcpdf.key', 'tcpdf-cert', '', 2, $info);

$pdf->SetFont('helvetica', '', 12);

$pdf->AddPage();

$text = 'Este documento certifica que el NFT con ID ' . $_GET['id'] . ' es auténtico y ha sido adquirido en Dialpi NFT.';
$pdf->writeHTML($text, true, 0, true, 0);

$pdf->Image('img/Dialpi NFT.jpeg');

$pdf->setSignatureAppearance(180, 60, 15, 15);

$pdf->addEmptySignatureAppearance(180, 80, 15, 15);

$pdf->Output('certificado-NFT.pdf', 'D');