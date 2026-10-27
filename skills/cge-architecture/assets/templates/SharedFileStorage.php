<?php

namespace App\Infrastructure\Storage;

/**
 * Gestor de almacenamiento y ensamblado de documentos (Document Assembly).
 * Combina imágenes y PDFs en un único documento optimizado vía Ghostscript (gs).
 * Compatible con PHP 7.3.
 */
class SharedFileStorage
{
    /** @var string */
    private $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/');
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Ensambla múltiples archivos (PDF o imágenes) en un único PDF final optimizado.
     *
     * @param array $filePaths Rutas temporales de los archivos subidos.
     * @param string $outputFilename Nombre deseado para el archivo final.
     * @return string Ruta completa del PDF generado.
     */
    public function mergeAndOptimize(array $filePaths, string $outputFilename): string
    {
        $tempPdfs = [];

        foreach ($filePaths as $path) {
            $mime = mime_content_type($path);
            if ($mime === 'application/pdf') {
                $tempPdfs[] = $path;
            } elseif (in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
                // Conversión de imagen a PDF temporal usando ImageMagick o TCPDF
                $pdfFromImg = $this->convertImageToPdf($path);
                $tempPdfs[] = $pdfFromImg;
            }
        }

        $destPath = $this->uploadDir . '/' . $outputFilename;

        // Si es solo 1 PDF ya listo, se copia directamente
        if (count($tempPdfs) === 1 && end($tempPdfs) === $filePaths[0]) {
            copy($tempPdfs[0], $destPath);
            return $destPath;
        }

        // Fusión con Ghostscript aplicando optimización web (-dPDFSETTINGS=/ebook)
        $escapedInputs = implode(' ', array_map('escapeshellarg', $tempPdfs));
        $escapedOutput = escapeshellarg($destPath);

        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dCompatibilityLevel=1.4 -sOutputFile={$escapedOutput} {$escapedInputs} 2>&1";
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException('Error al ensamblar el PDF con Ghostscript: ' . implode("\n", $output));
        }

        return $destPath;
    }

    /**
     * Convierte una imagen a un archivo PDF temporal.
     */
    private function convertImageToPdf(string $imagePath): string
    {
        $tempPdf = tempnam(sys_get_temp_dir(), 'img_') . '.pdf';
        $cmd = "convert " . escapeshellarg($imagePath) . " " . escapeshellarg($tempPdf);
        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($tempPdf)) {
            throw new \RuntimeException('Error al convertir imagen a PDF: ' . implode("\n", $output));
        }

        return $tempPdf;
    }
}
