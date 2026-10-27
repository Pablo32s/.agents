# Generación y Combinación de PDFs (Document Assembly)

Este documento detalla el estándar para el procesamiento, conversión y fusión de documentos adjuntos en el ecosistema CGE / SAGE.

---

## 1. Responsable del Componente: `SharedFileStorage.php`

El procesamiento y ensamblado de archivos se centraliza en `App\Infrastructure\Storage\SharedFileStorage.php`.

### Capacidades:
1. **Soporte Multiformato**: Permite subir tanto PDFs como imágenes (JPG, PNG). Las imágenes se convierten dinámicamente a PDF utilizando archivos temporales del sistema antes de la combinación.
2. **Combinación Robusta con Ghostscript**: Cuando un usuario adjunta múltiples archivos (por ejemplo, anverso y reverso de un documento en archivos separados), el sistema los unifica en un único documento PDF final.

---

## 2. Banderas de Optimización de Ghostscript

Para garantizar que el archivo final sea liviano, compatible y apto para lectura web rápida sin consumir ancho de banda excesivo, se deben utilizar estrictamente las siguientes banderas en el comando de Ghostscript:

```bash
gs -q -dNOPAUSE -dBATCH \
   -sDEVICE=pdfwrite \
   -dPDFSETTINGS=/ebook \
   -dCompatibilityLevel=1.4 \
   -sOutputFile={output.pdf} \
   {input1.pdf} {input2.pdf}
```

### Justificación Técnica:
- `-dPDFSETTINGS=/ebook`: Comprime las imágenes internas a una resolución de 150 DPI equilibrando nitidez y peso de descarga.
- `-dCompatibilityLevel=1.4`: Asegura compatibilidad con visores de PDF estándar y legacy.
