<?php
session_start();
require_once('tcpdf/tcpdf.php');
require_once 'conexion.php';

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Dialpi NFT');
$pdf->SetTitle('Certificado de Autenticidad NFT');
$pdf->SetSubject('Certificado NFT');
$pdf->SetKeywords('Dialpi, Certificado, NFT, cripto, ETH');

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(25, 30, 25);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->AddPage();

$colorStart = array(47, 76, 92);
$colorEnd = array(34, 39, 68);
$pdf->LinearGradient(0, 0, $pdf->getPageWidth(), 30, $colorStart, $colorEnd);

$headerHeight = 30;
$logoWidth = 25;
$logoHeight = 25;
$logoX = ($pdf->getPageWidth() - $logoWidth - 60) / 2;
$logoY = ($headerHeight - $logoHeight) / 2;
$desplazamientoY = $logoY + ($logoHeight - 10) / 2;

$pdf->Image('img/Dialpi_NFT.png', $logoX, $logoY, $logoWidth, $logoHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
$pdf->SetFont('grooverheavy', '', 20);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY($logoX + $logoWidth + 10, $desplazamientoY);
$pdf->Cell(0, 10, 'Dialpi NFT', 0, 1, 'L', false, '', 0, false, 'T', 'M');

$pdf->SetY(40);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 10, 'CERTIFICACIÓN DE PROPIEDAD NFT', 0, 1, 'C');

$pdf->SetY(60);

if (isset($_SESSION['invitado'])) {
    $nombre = $_SESSION['invitado']['nombre'];
    $apellidos = $_SESSION['invitado']['apellidos'];
    $dni = $_SESSION['invitado']['dni'];
} else {
    $nombre = $_SESSION['usuario']['nombre'];
    $apellidos = $_SESSION['usuario']['apellidos'];
    $dni = $_SESSION['usuario']['dni'];
}

$pdf->SetFont('helvetica', '', 12);

$día = date('j');
$meses = array(1=>'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
$mes = $meses[date('n')];
$year = date('Y');

$text = "Don JAVIER GÓMEZ MARTÍNEZ, con DNI 465496745-K, en calidad de Gerente de la empresa Dialpi NFT, con NIF 77785369-W y domicilio fiscal en la CALLE DE ALCALÁ, 100 28009 MADRID (ESPAÑA).\n\n\nCERTIFICA:\nQue el NFT cuyo detalle se especifica a continuación, ha sido obtenida por $nombre $apellidos, con DNI $dni y por lo tanto es el/la propietario/a legítimo/a de la misma.\n\n";
$pdf->MultiCell(0, 10, $text, 0, 'J', 0, 1, '', '', true);

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $productId) {
        $query = "SELECT * FROM NFT WHERE id_nft = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $query2 = "SELECT nombre_coleccion FROM Coleccion_NFT WHERE id_coleccion = ?";
            $stmt2 = $conexion->prepare($query2);
            $stmt2->bind_param('i', $row["coleccion"]);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();

            $y = $pdf->GetY();
            
            $pdf->Line(25, $y, $pdf->GetPageWidth() - 25, $y);
            $pdf->Image('img/colecciones/' . $row2["nombre_coleccion"] . '/' . $row["nombre_nft"] . '.png', 25, $y, 20, 20);
            
            $centerY = $y + 10;

            $pdf->SetXY(50, $centerY - 5);
            $pdf->Cell(70, 10, ucfirst($row["nombre_nft"]), 0, 0, 'L', false);
            $pdf->Cell(0, 10, $row["precio"] . ' ETH', 0, 1, 'R', false);
            $pdf->Line(25, $y + 20, $pdf->GetPageWidth() - 25, $y + 20);
            $pdf->SetY($y + 30);
        }
    }
}

$text = "En Madrid, a $día de $mes de $year.\n\n\n";
$pdf->MultiCell(0, 10, $text, 0, 'R', 0, 1, '', '', true);

$text = "Firmado, Don JAVIER GÓMEZ MARTÍNEZ\n\n\n\n\n";
$pdf->MultiCell(0, 10, $text, 0, 'L', 0, 1, '', '', true);

$y = $pdf->GetY();
$sealX = 35;
$firmaX = 25;
$posicionX = $pdf->GetX();

$nuevaPosicionX = $posicionX - 30;

$pdf->Image('img/sello.png', $sealX, $y - 20, 40, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
$pdf->Image('img/firma.png', $firmaX, $y - 20, 70, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);


$pdf->SetY($y + 20);
$text = "Gerente de Dialpi NFT.";
$pdf->MultiCell(0, 10, $text, 0, 'L', 0, 1, '', '', true);

$pdf->Output('certificado-NFT.pdf', 'D');