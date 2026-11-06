# Guía de Generación de Archivos PDF (SAGE / CGE)

Este documento establece el estándar para la generación de archivos PDF en sistemas SAGE / CGE, garantizando coherencia visual y técnica en PHP 8.5.

## 1. Regla Arquitectónica: Wrapper `TCPDFGenerator`

> [!WARNING]
> **PROHIBIDO** instanciar librerías externas (`Dompdf`, `mPDF`, `TCPDF` directo) en la capa de Aplicación (UseCases). Siempre usar la abstracción `App\Infrastructure\Reports\TCPDFGenerator`.

## 2. Flujo en el UseCase

```php
// 1. Limpiar buffer de salida
if (ob_get_length()) {
    ob_clean();
}

// 2. Instanciar wrapper
use App\Infrastructure\Reports\TCPDFGenerator;

$generator = new TCPDFGenerator();
$generator->init('P', 'mm', 'A4', [
    'titulo' => 'Reporte Oficial SAGE',
    'fuente' => 'helvetica',
    'tamanio_fuente' => 9
]);

$generator->addPage();
$generator->writeHTML($html);

$filename = 'reporte_' . date('YmdHis') . '.pdf';
$pdfData = $generator->output($filename);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdfData));
echo $pdfData;
exit;
```
