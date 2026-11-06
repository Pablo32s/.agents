<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Daily rotating file logger with automatic 10-day retention.
 * Writes technical context (stack trace, SQL, file, line, payload) to logs/error-YYYY-MM-DD.log
 * and logs/app-YYYY-MM-DD.log while keeping frontend messages clean for end users.
 * Estándar PHP 8.5.
 */
final class Logger
{
    private const int RETENTION_DAYS = 10;

    private readonly string $logDir;

    public function __construct(?string $logDir = null)
    {
        $defaultDir = dirname(__DIR__, 2) . '/logs';
        $this->logDir = rtrim($logDir ?: $defaultDir, '/\\');
        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0775, true);
        }
        $this->pruneOldLogs();
    }

    public function info(string $message, array $context = []): string
    {
        return $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): string
    {
        return $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): string
    {
        return $this->write('ERROR', $message, $context);
    }

    /**
     * Logs a Throwable with full developer/AI diagnostic context and returns a short traceId.
     */
    public function exception(\Throwable $e, array $extraContext = []): string
    {
        $context = array_merge([
            'exception' => get_class($e),
            'code'      => $e->getCode(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
            'uri'       => $_SERVER['REQUEST_URI'] ?? '',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_id'   => $_SESSION['usuario_id'] ?? ($_SESSION['idUsuario'] ?? null),
        ], $extraContext);

        return $this->write('ERROR', $e->getMessage(), $context);
    }

    private function write(string $level, string $message, array $context = []): string
    {
        try {
            $traceId = bin2hex(random_bytes(4));
        } catch (\Throwable $e) {
            $traceId = substr(md5(uniqid('', true)), 0, 8);
        }

        $timestamp = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $contextJson = !empty($context)
            ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '';

        $line = "[{$timestamp}] [{$level}] [TraceID: {$traceId}] {$message}{$contextJson}" . PHP_EOL;

        $prefix = ($level === 'ERROR') ? 'error-' : 'app-';
        $targetFile = $this->logDir . '/' . $prefix . $today . '.log';
        @file_put_contents($targetFile, $line, FILE_APPEND | LOCK_EX);

        return $traceId;
    }

    /**
     * Deletes daily log files (app-YYYY-MM-DD.log and error-YYYY-MM-DD.log) older than 10 days.
     */
    private function pruneOldLogs(): void
    {
        if (!is_dir($this->logDir)) {
            return;
        }

        $files = @scandir($this->logDir);
        if (!is_array($files)) {
            return;
        }

        $cutoff = strtotime('-' . self::RETENTION_DAYS . ' days');

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (preg_match('/^(?:app|error|debug)-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $fileTime = strtotime($m[1]);
                if ($fileTime !== false && $fileTime < $cutoff) {
                    @unlink($this->logDir . '/' . $file);
                }
            }
        }
    }
}
