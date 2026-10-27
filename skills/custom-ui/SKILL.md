---
name: custom-ui
description: "Trigger: custom-ui, ui, frontend, css, diseno, estilos, componentes, web components, html, interfaz, pantalla, tablas, paginacion, filtros. Exclusively mandated frontend design system for all SAGE development: institutional sage-container, elevated main-card, segmented control pill subtabs, Blue & Slate design tokens, SVG sprite icons (no emojis, no sidebars), modern sticky data tables with pagination and column filters, and Native Web Components."
---

# Custom UI — Sistema de Diseño Exclusivo & Skill Global

> **MANDATO EXCLUSIVO**: Para todo desarrollo frontend, pantallas, componentes y estilos visuales en el ecosistema institucional de SAGE, es **OBLIGATORIO Y EXCLUSIVO** utilizar `custom-ui`. Está estrictamente prohibido utilizar otros frameworks externos, librerías CSS pesadas, estilos monolíticos o componentes que no sigan las directivas y tokens aquí establecidos.

Este skill define las especificaciones canónicas para construir interfaces web modernas, limpias, modulares y de alto rendimiento: elevaciones sutiles, esquinas redondeadas (`8px` - `12px`), paleta armónica **Blue & Slate**, navegación horizontal mediante controles segmentados (pills), tablas interactivas de alta densidad y cero artefactos visuales obsoletos.

---

## 1. Anatomía y Estructura de Pantalla

Todo módulo y pantalla debe estructurarse obligatoriamente bajo esta jerarquía visual de contenedores:

```html
<main class="sage-container">
  <div class="main-card">
    <header class="main-header">
      <div>
        <h2>Título del Módulo</h2>
        <p class="subtitle">Descripción funcional clara y concisa de la herramienta.</p>
      </div>
      <div class="user-chip">
        <span class="user-dot"></span>
        <span>Usuario: Nombre de Usuario</span>
      </div>
    </header>

    <!-- Barra de Pestañas Segmentadas (Pills) -->
    <nav class="subtabs-container" role="tablist">
      <button type="button" class="subtab-btn active" data-tab="seccion1" role="tab" aria-selected="true">
        <svg class="tab-icon" width="18" height="18" viewBox="0 0 24 24"><use href="/public/assets/img/sprite.svg#i-clipboard"></use></svg>
        <span>Sección Principal</span>
      </button>
      <button type="button" class="subtab-btn" data-tab="seccion2" role="tab" aria-selected="false">
        <svg class="tab-icon" width="18" height="18" viewBox="0 0 24 24"><use href="/public/assets/img/sprite.svg#i-book"></use></svg>
        <span>Catálogos y Datos</span>
      </button>
    </nav>

    <!-- Contenedor del contenido activo (100% de ancho útil) -->
    <div id="app-content" class="active-view-container">
      <!-- Vistas o Web Components -->
    </div>
  </div>
</main>
```

### Reglas Invariantes de Cascarón:
1. **`.sage-container`**: Ocupa el 94% del ancho de pantalla con un máximo de `1800px`, centrado horizontalmente con `margin: 0 auto; padding: 24px; box-sizing: border-box;`.
2. **`.main-card`**: Tarjeta envolvente blanca (`background: #ffffff;`), con esquinas redondeadas `border-radius: 12px;`, borde nítido `border: 1px solid #e2e8f0;`, padding interno `24px;` y sombra sutil `box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);`.
3. **Encabezado Jerárquico**:
   - `h2`: `font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 6px; letter-spacing: -0.02em;`.
   - `.subtitle`: `font-size: 0.95rem; color: #64748b; margin: 0 0 20px;`.
   - `.user-chip`: Insignia redondeada (`border-radius: 999px; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 14px; font-size: 0.875rem; color: #475569;`) con punto verde de conexión `.user-dot` (`width: 8px; height: 8px; border-radius: 50%; background: #10b981;`).

---

## 2. Navegación: Segmented Control (Pill Bar)

> **PROHIBICIÓN ESTRICTA**: Está **TERMINANTEMENTE PROHIBIDO** utilizar barras laterales fijas (sidebars verticales) o pestañas planas de navegador con subrayado simple. Los módulos se ejecutan embebidos en el portal institucional (iframe) y los sidebars internos destruyen el espacio horizontal necesario para tablas densas.

La navegación entre submódulos o vistas secundarias debe utilizar **Segmented Controls (Pills)**:

```css
.subtabs-container {
  display: inline-flex;
  align-items: center;
  background-color: #f1f5f9;
  padding: 4px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
  gap: 6px;
  user-select: none;
  max-width: 100%;
  overflow-x: auto;
}

.subtab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  border-radius: 7px;
  padding: 9px 22px;
  font-size: 0.92rem;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  outline: none;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
  user-select: none;
}

.subtab-btn:hover {
  color: #0f172a;
  background-color: rgba(255, 255, 255, 0.65);
}

.subtab-btn.active {
  background-color: #ffffff;
  color: #0f172a;
  font-weight: 600;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
  cursor: default;
}

.subtab-btn .tab-icon {
  flex-shrink: 0;
  stroke: currentColor;
  fill: none;
  width: 18px;
  height: 18px;
  transition: stroke 0.18s ease;
}

.subtab-btn.active .tab-icon {
  stroke: #0284c7;
}
```

---

## 3. Iconografía Institucional (Sprite SVG Exclusivo)

> **PROHIBICIÓN ESTRICTA**: CERO emojis de navegador (prohibido `📋`, `🎓`, `📁`, `🔍`, `✏️`, `🗑️`). Todo ícono debe ser vectorial desde el archivo unificado `public/assets/img/sprite.svg`.

- Estructura del ícono:
  ```html
  <svg class="tab-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <use href="/public/assets/img/sprite.svg#i-home"></use>
  </svg>
  ```
- Símbolos canónicos disponibles en el sprite:
  - Navegación: `#i-home`, `#i-clipboard`, `#i-book`, `#i-graduation`, `#i-users`, `#i-link`, `#i-layers`.
  - Acciones: `#i-plus`, `#i-edit`, `#i-trash`, `#i-search`, `#i-settings`, `#i-check`, `#i-x`.
  - Estados: `#i-check-circle`, `#i-alert`, `#i-info`, `#i-x-circle`.

---

## 4. Tablas de Datos de Alta Densidad

Las tablas deben presentar estética limpia, profesional y moderna:

```css
.table-responsive {
  width: 100%;
  overflow-x: auto;
  overflow-y: auto;
  max-height: 55vh;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

table.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.875rem;
}

table.data-table th {
  padding: 12px 16px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  color: #475569;
  background-color: #f8fafc;
  position: sticky;
  top: 0;
  z-index: 5;
  box-shadow: 0 1px 0 #e2e8f0;
}

table.data-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #eeeeee;
  color: #0f172a;
  vertical-align: middle;
}

table.data-table tbody tr:hover {
  background-color: #f8fafc;
}

table.data-table tbody tr:last-child td {
  border-bottom: none;
}
```

### Búsqueda, Filtros y Paginación
- **Buscador General**: Input con padding amplio y halo de foco azul suave:
  ```css
  .search-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #0f172a;
    background: #ffffff;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .search-input:focus {
    outline: none;
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
  }
  ```
- **Panel de Filtros Avanzados (`.filter-panel`)**: Fondo `#f8fafc`, borde `#e2e8f0`, grid dinámico por columnas.
- **Pie de Tabla (`.table-footer`)**:
  - Selector de registros: `<select id="page-size">`.
  - Botones de página redondeados (`.page-btn` con `border-radius: 6px;` y clase `.active` con fondo primario).

---

## 5. Badges y Botones de Acción

### Badges de Estado
- `.badge-status-active`: Fondo `#dcfce7`, texto `#15803d`, borde `#bbf7d0`, con punto verde.
- `.badge-status-inactive`: Fondo `#fee2e2`, texto `#b91c1c`, borde `#fecaca`, con punto rojo.
- `.badge-division`: Fondo `#f1f5f9`, texto `#1e293b`, borde `#e2e8f0`, texto en semibold institucional.

### Botones
- **Primario (`.btn-primary`)**:
  ```css
  .btn-primary {
    background-color: #0284c7;
    color: #ffffff;
    border: none;
    padding: 9px 18px;
    border-radius: 6px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .btn-primary:hover {
    background-color: #0369a1;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);
  }
  ```
- **Secundario (`.btn-secondary`)**:
  ```css
  .btn-secondary {
    background-color: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 9px 16px;
    border-radius: 6px;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
  }
  .btn-secondary:hover {
    background-color: #f1f5f9;
    color: #0f172a;
  }
  ```
- **Acciones en Fila (`.action-btn`)**:
  ```css
  .action-btn {
    background: transparent;
    border: 1.5px solid transparent;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .action-btn.text-primary {
    color: #0284c7;
    background-color: #f0f9ff;
    border-color: #7dd3fc;
  }
  .action-btn.text-primary:hover {
    background-color: #e0f2fe;
    border-color: #38bdf8;
  }
  .action-btn.text-danger {
    color: #dc2626;
    background-color: #fff5f5;
    border-color: #f87171;
  }
  .action-btn.text-danger:hover {
    background-color: #fee2e2;
    border-color: #ef4444;
  }
  ```

---

## 6. Design Tokens Canónicos (`design-tokens.css`)

```css
:root {
  /* Fondos y Superficies */
  --bg-main: #f0f4f8;
  --bg-surface: #ffffff;
  --bg-subtle: #f8fafc;
  --border-color: #cbd5e1;
  --border-light: #e2e8f0;

  /* Azules SAGE */
  --primary-50:  #eff6ff;
  --primary-100: #dbeafe;
  --primary-200: #bfdbfe;
  --primary-500: #3b82f6;
  --primary-600: #0284c7;
  --primary-700: #0369a1;
  --primary-800: #1e40af;
  --primary-900: #0f172a;

  /* Slate Neutro */
  --text-primary: #0f172a;
  --text-secondary: #475569;
  --text-muted: #64748b;

  /* Estados */
  --success-bg: #dcfce7;
  --success-text: #15803d;
  --success-border: #bbf7d0;
  --warning-bg: #fef3c7;
  --warning-text: #92400e;
  --warning-border: #fde68a;
  --danger-bg: #fee2e2;
  --danger-text: #b91c1c;
  --danger-border: #fecaca;

  /* Radios y Sombras */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.08);
  --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.1);
  --transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}
```

---

## 7. Modelos de Implementación Frontend

### Modelo A: Web Components con Shadow DOM
Se utiliza cuando las vistas encapsulan su propia lógica e importan el template literal CSS compartido:
```javascript
import { servicesSharedStyles } from '../servicesSharedStyles.js';
import { icon } from '../Icons.js';

export class SageViewComponent extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: 'open' });
  }

  connectedCallback() {
    this.render();
  }

  render() {
    this.shadowRoot.innerHTML = `
      <style>
        ${servicesSharedStyles}
      </style>
      <div class="content-panel">
        <div class="toolbar">
          <div class="search-container">
            <input type="text" class="search-input" placeholder="Buscar registros...">
          </div>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Código</th><th>Descripción</th><th>Acciones</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    `;
  }
}
customElements.define('sage-view-component', SageViewComponent);
```

### Modelo B: Vistas Modulares Estándar
Vistas HTML/PHP vinculadas a `design-tokens.css` y orquestadas con el controlador Vanilla `DataTable.js`.

---

## 8. Checklist de Validación Obligatoria

Antes de dar por finalizada cualquier interfaz o componente:
- [ ] ¿El cascarón principal está envuelto en `.sage-container` y `.main-card`?
- [ ] ¿La navegación utiliza el Segmented Control `.subtabs-container` con `.subtab-btn`?
- [ ] ¿Se eliminaron completamente los sidebars laterales internos?
- [ ] ¿Todos los íconos provienen de `sprite.svg` y no hay ningún emoji de navegador?
- [ ] ¿Las tablas tienen encabezados sticky con fondo `#f8fafc` y hover en filas?
- [ ] ¿Los botones de acción en tabla usan `.action-btn` estilizados (primary o danger)?
- [ ] ¿Los inputs tienen focus ring azul suave de 3px sin contorno predeterminado del navegador?
