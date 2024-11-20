<?php
require '../fpdf/fpdf.php';
require_once 'DB.php';

$filtroFecha = $_GET['fecha'] ?? date('Y-m'); // Mes actual por defecto
$db = getDB();

// Consulta para obtener los datos del reporte
$stmt = $db->prepare("SELECT * FROM transacciones_diarias WHERE DATE_FORMAT(fecha, '%Y-%m') = ?");
$stmt->bind_param("s", $filtroFecha);
$stmt->execute();
$resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Crear PDF con FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Reporte Financiero - ' . $filtroFecha, 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Fecha', 1);
$pdf->Cell(40, 10, 'Ingresos', 1);
$pdf->Cell(40, 10, 'Egresos', 1);
$pdf->Cell(40, 10, 'Utilidad', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($resultados as $row) {
    $pdf->Cell(40, 10, $row['fecha'], 1);
    $pdf->Cell(40, 10, '$' . number_format($row['ingresos'], 0), 1);
    $pdf->Cell(40, 10, '$' . number_format($row['egresos'], 0), 1);
    $pdf->Cell(40, 10, '$' . number_format($row['utilidad'], 0), 1);
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Financiero_' . $filtroFecha . '.pdf');
