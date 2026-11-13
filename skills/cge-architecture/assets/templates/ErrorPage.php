<?php

declare(strict_types=1);

namespace App\Infrastructure\Ui\Http;

/**
 * Clase unificada para el renderizado de pantallas de error (401, 403, 404, 500).
 * Estándar PHP 8.5 y tokens Blue & Slate (custom-ui).
 */
final class ErrorPage
{
    /**
     * Renderiza una pantalla de error autónoma y detiene la ejecución.
     */
    public static function render(int $code, string $message = '', string $details = ''): never
    {
        if (!headers_sent()) {
            http_response_code($code);
        }

        $config = match ($code) {
            401 => [
                'title'   => '401 - No Autorizado',
                'message' => 'Sesión Expirada o Inválida',
                'details' => 'No pudimos validar tu sesión activa. Por favor, reingresá al sistema SAGE.',
            ],
            403 => [
                'title'   => '403 - Acceso Denegado',
                'message' => 'Permisos Insuficientes',
                'details' => 'Tu rol actual no posee permisos de consulta para el recurso seleccionado.',
            ],
            404 => [
                'title'   => '404 - No Encontrado',
                'message' => 'Recurso no Encontrado',
                'details' => 'El recurso o sección solicitado no existe en esta versión del módulo.',
            ],
            default => [
                'title'   => '500 - Error Interno',
                'message' => 'Error del Servidor',
                'details' => 'Ocurrió un problema inesperado al procesar tu solicitud. El equipo técnico ha sido notificado.',
            ],
        };

        $displayTitle   = htmlspecialchars($config['title'], ENT_QUOTES, 'UTF-8');
        $displayMessage = htmlspecialchars($message !== '' ? $message : $config['message'], ENT_QUOTES, 'UTF-8');
        $displayDetails = htmlspecialchars($details !== '' ? $details : $config['details'], ENT_QUOTES, 'UTF-8');

        $svgFilename = in_array($code, [401, 403, 404, 500], true) ? "{$code}.svg" : '500.svg';
        $svgContent = '';

        $possiblePaths = [
            ($_SERVER['DOCUMENT_ROOT'] ?? '') . "/error/img/{$svgFilename}",
            dirname(__DIR__, 4) . "/error/img/{$svgFilename}",
            dirname(__DIR__, 4) . "/img/{$svgFilename}",
            ($_SERVER['DOCUMENT_ROOT'] ?? '') . "/img/{$svgFilename}",
        ];

        foreach ($possiblePaths as $path) {
            if ($path !== '' && is_file($path)) {
                $svgContent = (string)file_get_contents($path);
                break;
            }
        }

        if ($svgContent === '') {
            $svgContent = '
            <svg viewBox="0 0 24 24" width="96" height="96" fill="none" stroke="#0284c7" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>';
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $displayTitle; ?></title>
            <style>
                :root {
                    --bg-main: #f0f4f8;
                    --bg-surface: #ffffff;
                    --primary-600: #0284c7;
                    --primary-100: #dbeafe;
                    --primary-900: #0f172a;
                    --text-secondary: #475569;
                    --border-light: #e2e8f0;
                }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background-color: var(--bg-main);
                    color: var(--primary-900);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                }
                .error-card {
                    background: var(--bg-surface);
                    max-width: 560px;
                    width: 100%;
                    padding: 40px 36px;
                    border-radius: 16px;
                    border: 1px solid var(--border-light);
                    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08);
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 20px;
                }
                .error-code {
                    background-color: var(--primary-100);
                    color: var(--primary-600);
                    padding: 6px 14px;
                    border-radius: 999px;
                    font-size: 0.8rem;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                }
                .error-illustration svg { max-height: 180px; width: auto; }
                h1 { font-size: 1.5rem; font-weight: 700; color: var(--primary-900); }
                .error-details {
                    background: #f8fafc;
                    border: 1px solid var(--border-light);
                    border-radius: 10px;
                    padding: 16px;
                    color: var(--text-secondary);
                    font-size: 0.92rem;
                    line-height: 1.5;
                    width: 100%;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <span class="error-code">CÓDIGO HTTP <?= $code; ?></span>
                <div class="error-illustration"><?= $svgContent; ?></div>
                <h1><?= $displayMessage; ?></h1>
                <div class="error-details"><?= $displayDetails; ?></div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
