# Ofuscación de IDs y Parámetros GET (Anti-IDOR y Privacidad)

Este documento define la regla estricta de seguridad para la exposición de identificadores de base de datos y parámetros de consulta en el ecosistema CGE / SAGE.

---

## 1. Principio Fundamental: Zero-Trust Frontend

El frontend (HTML, atributos `data-*`, botones, llamadas JavaScript o URLs) **NUNCA debe conocer los IDs numéricos autoincrementales reales** de la base de datos (`id=1234`), ni tampoco debe exponer parámetros semánticos en texto plano por GET (`?documento=30111222&idAgente=555`).

### Riesgos que previene:
1. **Ataques IDOR (Insecure Direct Object References)**: Evita que un usuario modifique o consulte registros ajenos alterando secuencialmente un número (`id=1234` -> `id=1235`).
2. **Fuga de Información Semántica y PII**: Los números de documento, legajos o IDs de agentes quedan expuestos en el historial del navegador, proxies intermedios, cabeceras `Referer` y logs de acceso del servidor.

---

## 2. Ofuscación de Identificadores (Crypto::encryptId / decryptId)

Todo ID que viaje hacia el cliente debe cifrarse con la clase `App\Core\Security\Crypto`:

### En el Backend al enviar datos al Frontend:
```php
use App\Core\Security\Crypto;

$entidadData = [
    'id'     => Crypto::encryptId($registro['id']), // Ej: "k9_mX2pA..."
    'codigo' => $registro['codigo'],
    'estado' => $registro['estado']
];
```

### En el Frontend (HTML / JS):
El botón o enlace solo maneja el token opaco:
```html
<button class="btn-editar" data-id="k9_mX2pA...">Modificar</button>
```

### En el Backend al recibir la mutación:
El Front Controller (`public/api.php`) o el UseCase descifra automáticamente el token:
```php
$realId = Crypto::decryptId($payload['id']);
if ($realId === null) {
    throw new \InvalidArgumentException("Identificador inválido.");
}
// Ahora es seguro ejecutar: UPDATE tbl_modulo SET estado = :estado WHERE id = :id
```

---

## 3. Bloque de Parámetros GET Cifrados (`?q=TOKEN`)

Prohibido armar URLs con parámetros semánticos visibles:
❌ `index.php?action=detalle&param1=valor&id=1234`

Todo el bloque de consulta debe encapsularse en un único parámetro cifrado `q`:
✅ `index.php?q=w8e7f6a5b4c3d2e1...`

### Generación en PHP:
```php
$url = 'index.php?' . http_build_query([
    'q' => Crypto::encryptQuery([
        'action' => 'detalle',
        'filtro' => 'activos',
        'id'     => $entidadId
    ])
]);
```

### Desempaquetado Automático en `public/api.php`:
`public/api.php` intercepta `$_GET['q']`, descifra el payload y lo inyecta limpiamente en el mapa de parámetros de la petición de forma transparente para los UseCases.
