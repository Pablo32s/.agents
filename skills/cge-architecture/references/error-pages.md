# Guía de Pantallas de Error Unificadas y Reutilizables

Esta guía detalla cómo aplicar la clase unificada de errores `ErrorPage` en sistemas SAGE / CGE (PHP 8.5).

---

## 📌 Conceptos Clave

1. **Inyección Inline de SVGs**: Lee los archivos SVG con `file_get_contents()` y los incrusta en el HTML para evitar enlaces relativos rotos en subdirectorios del servidor. Incluye fallback SVG puro.
2. **Estilo Autónomo**: Contiene su propio bloque `<style>` integrado bajo Material Design 3. No depende de `styles.css`.
3. **Estándar PHP 8.5**: Utiliza `declare(strict_types=1);`, `final class` y expresiones `match` para mapeo limpio de estados HTTP.

---

## 🛠️ Cómo Utilizar la Clase `ErrorPage`

Ubicada en `App\Infrastructure\Ui\Http\ErrorPage`:

```php
ErrorPage::render(int $code, string $message = '', string $details = '');
```

### Códigos HTTP Soportados:
- **401**: No Autorizado / Sesión Expirada
- **403**: Acceso Denegado / Permisos Insuficientes
- **404**: Recurso No Encontrado
- **500**: Error Interno del Servidor
