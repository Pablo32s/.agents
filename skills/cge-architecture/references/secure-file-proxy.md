# Protección de Archivos y Patrón Proxy (`verArchivo.php`)

Este documento detalla el estándar para servir archivos adjuntos protegidos en el ecosistema CGE / SAGE sin exponer rutas físicas ni permitir descargas no autorizadas.

---

## 1. Principio: Aislamiento y Rutas Físicas Ocultas

Cada módulo es 100% autónomo y desconoce la existencia de otros módulos:
- **Almacenamiento Aislado**: Cada módulo define su propia ruta en `.env` mediante `STORAGE_PATH` (ej: `/archivos/modulo_actual` o `storage/uploads`).
- **Rutas Reales Ocultas**: Nunca se exponen las rutas del servidor al cliente. Los documentos se solicitan a través del proxy propio de cada módulo: `verArchivo.php?token=TOKEN_CIFRADO`. Todos los módulos usan el mismo nombre de script (`verArchivo.php`) para aumentar la ofuscación.

---

## 2. Pilares de Seguridad del Proxy

1. **Tokens Cifrados (AES)**: El token contiene la ruta del archivo y metadatos cifrados simétricamente con `Crypto::encryptQuery()`. Previene ataques de fuerza bruta y recolección automatizada (scraping).
2. **Authentication-First**: Valida la sesión activa de PHP (`$_SESSION['usuario_id']`) antes de comprobar la existencia del archivo en disco. Sin sesión válida, responde inmediatamente HTTP 401 mediante `ErrorPage`.
3. **Aislamiento de Directorio (Anti-Traversal)**: La ruta solicitada debe pertenecer exclusivamente a la raíz de almacenamiento configurada para este módulo (`STORAGE_PATH`). Queda terminantemente prohibido el acceso a rutas fuera del módulo o secuencias relativas (`../`).
4. **Entrega por Stream Seguro (`readfile`)**: El contenido del archivo se vuelca directamente al flujo de salida con cabeceras `Content-Type: application/pdf` y `Content-Disposition: inline`. El directorio físico de almacenamiento permanece fuera del `DocumentRoot` público.
5. **Migración "On-The-Fly" (Al Vuelo)**: Si el proxy detecta que el archivo está almacenado en un directorio o formato legacy propio del módulo, lo renombra con la nomenclatura estructurada `{idAgente}_{fecha}_{hash}.pdf`, lo mueve a la ruta moderna y actualiza el registro en la base de datos de forma transparente.
