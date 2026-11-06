<?php

declare(strict_types=1);

namespace App\Infrastructure\Ui\Http;

/**
 * Clase unificada para el renderizado de pantallas de error.
 * Estándar PHP 8.5 y Material Design 3.
 */
final class ErrorPage
{
    /**
     * Renderiza una pantalla de error autocompletada y detiene la ejecución.
     *
     * @param int $code Código HTTP (401, 403, 404, 500)
     * @param string $message Mensaje principal del error
     * @param string $details Detalles técnicos o instrucciones adicionales
     */
    public static function render(int $code, string $message = '', string $details = ''): void
    {
        if (!headers_sent()) {
            http_response_code($code);
        }

        $config = match ($code) {
            401 => [
                'title'   => '401 - No Autorizado',
                'message' => 'Sesión Expirada o Inválida',
                'details' => 'No tenés permisos para acceder a esta sección. Por favor, reingresá al sistema SAGE.'
            ],
            403 => [
                'title'   => '403 - Acceso Denegado',
                'message' => 'Permisos Insuficientes',
                'details' => 'Tu rol actual no posee permisos de consulta para el recurso seleccionado.'
            ],
            404 => [
                'title'   => '404 - No Encontrado',
                'message' => 'Página no Encontrada',
                'details' => 'El recurso o sección solicitado no existe en esta versión del módulo.'
            ],
            default => [
                'title'   => '500 - Error Interno',
                'message' => 'Error del Servidor',
                'details' => 'Ocurrió un problema inesperado al procesar tu solicitud. El equipo técnico ha sido notificado.'
            ],
        };

        $displayTitle   = htmlspecialchars($config['title']);
        $displayMessage = htmlspecialchars($message !== '' ? $message : $config['message']);
        $displayDetails = htmlspecialchars($details !== '' ? $details : $config['details']);

        $svgFilename = in_array($code, [401, 403, 404, 500]) ? "{$code}.svg" : '500.svg';
        $svgContent = '';

        $possiblePaths = [
            ($_SERVER['DOCUMENT_ROOT'] ?? '') . "/error/img/{$svgFilename}",
            dirname(__DIR__, 4) . "/img/{$svgFilename}",
            ($_SERVER['DOCUMENT_ROOT'] ?? '') . "/img/{$svgFilename}",
        ];

        foreach ($possiblePaths as $path) {
            if ($path !== '' && file_exists($path)) {
                $svgContent = file_get_contents($path);
                break;
            }
        }

        if (empty($svgContent)) {
            $svgContent = '
            <svg viewBox="0 0 24 24" width="120" height="120" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" stroke="var(--md-sys-color-primary)"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke="var(--md-sys-color-primary)"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke="var(--md-sys-color-primary)"/>
            </svg>';
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $displayTitle; ?></title>
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --md-sys-color-primary: #6750A4;
                    --md-sys-color-on-primary: #FFFFFF;
                    --md-sys-color-primary-container: #EADDFF;
                    --md-sys-color-on-primary-container: #21005D;
                    --md-sys-color-surface: #FEF7FF;
                    --md-sys-color-surface-container: #F3EDF7;
                    --md-sys-color-outline: #79747E;
                    --md-sys-color-outline-variant: #CAC4D0;
                    --md-sys-color-error: #B3261E;
                    --elevation-2: 0px 2px 6px 2px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.3);
                }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Roboto', sans-serif;
                    background: linear-gradient(135deg, var(--md-sys-color-surface) 0%, var(--md-sys-color-surface-container) 100%);
                    color: #1C1B1F; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
                }
                .error-card {
                    background: #FFFFFF; max-width: 600px; width: 100%; padding: 48px; border-radius: 28px;
                    box-shadow: var(--elevation-2); text-align: center; display: flex; flex-direction: column;
                    align-items: center; gap: 28px; animation: cardFadeIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
                }
                .error-code {
                    background-color: var(--md-sys-color-primary-container); color: var(--md-sys-color-on-primary-container);
                    padding: 6px 16px; border-radius: 100px; font-size: 14px; font-weight: 700; letter-spacing: 0.5px;
                }
                .error-illustration { width: 100%; max-width: 340px; height: auto; display: flex; justify-content: center; align-items: center; }
                .error-illustration svg { width: 100%; height: auto; max-height: 240px; animation: floatIllustration 5s ease-in-out infinite; }
                .error-info { display: flex; flex-direction: column; gap: 12px; width: 100%; }
                .error-info h1 { font-size: 26px; font-weight: 700; color: #1C1B1F; }
                .error-details {
                    background: var(--md-sys-color-surface-container); border: 1px solid var(--md-sys-color-outline-variant);
                    border-radius: 16px; padding: 18px; color: #49454F; font-size: 14px; line-height: 1.6; text-align: left; width: 100%;
                }
                @keyframes cardFadeIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
                @keyframes floatIllustration { 0% { transform: translateY(0px); } 50% { transform: translateY(-8px); } 100% { transform: translateY(0px); } }
                @media (max-width: 480px) { .error-card { padding: 32px 20px; } .error-info h1 { font-size: 22px; } }
            </style>
        </head>
        <body>
            <div class="error-card">
                <span class="error-code">CÓDIGO HTTP <?php echo $code; ?></span>
                <div class="error-illustration"><?php echo $svgContent; ?></div>
                <div class="error-info">
                    <h1><?php echo $displayMessage; ?></h1>
                    <div class="error-details"><?php echo $displayDetails; ?></div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
