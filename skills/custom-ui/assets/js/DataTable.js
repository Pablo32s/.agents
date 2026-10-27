/**
 * Custom UI — CgeDataTable (Vanilla JS / Zero Dependencies)
 * Control de Tablas Modernas: Paginación, Búsqueda Global, Filtros por Columna y Sorting
 */
export class CgeDataTable {
  constructor(tableSelector, options = {}) {
    this.table = typeof tableSelector === 'string' ? document.querySelector(tableSelector) : tableSelector;
    if (!this.table) {
      console.warn(`[CgeDataTable] Tabla no encontrada: ${tableSelector}`);
      return;
    }

    this.options = Object.assign({
      pageSize: 10,
      searchSelector: '#filtroBuscar',
      filterPanelSelector: '#panelFiltros',
      paginationSelector: '#paginacion',
      infoSelector: '#infoRegistros',
      pageSizeSelector: '#registrosPorPagina'
    }, options);

    this.currentPage = 1;
    this.pageSize = parseInt(this.options.pageSize, 10);
    this.sortColumn = null;
    this.sortDirection = 'asc'; // 'asc' | 'desc'
    this.allRows = [];
    this.filteredRows = [];

    this.init();
  }

  init() {
    this.tbody = this.table.querySelector('tbody');
    if (!this.tbody) return;

    // Guardar copia original de todas las filas
    this.allRows = Array.from(this.tbody.querySelectorAll('tr'));
    this.filteredRows = [...this.allRows];

    this.setupSorting();
    this.setupSearch();
    this.setupColumnFilters();
    this.setupPageSize();
    this.render();
  }

  setupSorting() {
    const headers = this.table.querySelectorAll('th.sortable');
    headers.forEach((th, index) => {
      th.addEventListener('click', () => {
        const currentDir = th.classList.contains('sort-asc') ? 'desc' : 'asc';
        headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
        th.classList.add(currentDir === 'asc' ? 'sort-asc' : 'sort-desc');

        this.sortColumn = index;
        this.sortDirection = currentDir;
        this.sortRows();
        this.render();
      });
    });
  }

  sortRows() {
    if (this.sortColumn === null) return;
    const colIndex = this.sortColumn;
    const dir = this.sortDirection === 'asc' ? 1 : -1;

    this.filteredRows.sort((rowA, rowB) => {
      const cellA = (rowA.children[colIndex]?.textContent || '').trim().toLowerCase();
      const cellB = (rowB.children[colIndex]?.textContent || '').trim().toLowerCase();

      // Comparación numérica si aplica
      const numA = parseFloat(cellA.replace(/[^0-9.-]/g, ''));
      const numB = parseFloat(cellB.replace(/[^0-9.-]/g, ''));
      if (!isNaN(numA) && !isNaN(numB)) {
        return (numA - numB) * dir;
      }
      return cellA.localeCompare(cellB) * dir;
    });
  }

  setupSearch() {
    const searchInput = document.querySelector(this.options.searchSelector);
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
      this.applyAllFilters();
    });
  }

  setupColumnFilters() {
    const panel = document.querySelector(this.options.filterPanelSelector);
    if (!panel) return;

    const inputs = panel.querySelectorAll('[data-column]');
    inputs.forEach(input => {
      input.addEventListener('change', () => this.applyAllFilters());
      input.addEventListener('input', () => this.applyAllFilters());
    });

    const btnReset = panel.querySelector('#btnLimpiarFiltros');
    if (btnReset) {
      btnReset.addEventListener('click', () => {
        inputs.forEach(input => {
          if (input.tagName === 'SELECT') input.selectedIndex = 0;
          else input.value = '';
        });
        const globalSearch = document.querySelector(this.options.searchSelector);
        if (globalSearch) globalSearch.value = '';
        this.applyAllFilters();
      });
    }
  }

  setupPageSize() {
    const select = document.querySelector(this.options.pageSizeSelector);
    if (!select) return;

    select.addEventListener('change', (e) => {
      this.pageSize = parseInt(e.target.value, 10);
      this.currentPage = 1;
      this.render();
    });
  }

  applyAllFilters() {
    const globalSearch = (document.querySelector(this.options.searchSelector)?.value || '').toLowerCase().trim();
    const panel = document.querySelector(this.options.filterPanelSelector);
    const columnFilters = {};

    if (panel) {
      panel.querySelectorAll('[data-column]').forEach(el => {
        const colIdx = parseInt(el.getAttribute('data-column'), 10);
        const val = el.value.toLowerCase().trim();
        if (val) columnFilters[colIdx] = val;
      });
    }

    this.filteredRows = this.allRows.filter(row => {
      // 1. Filtro global
      const rowText = row.textContent.toLowerCase();
      if (globalSearch && !rowText.includes(globalSearch)) {
        return false;
      }

      // 2. Filtros por columna específica
      for (const [colIdx, val] of Object.entries(columnFilters)) {
        const cellText = (row.children[colIdx]?.textContent || '').toLowerCase();
        if (!cellText.includes(val)) {
          return false;
        }
      }

      return true;
    });

    this.sortRows();
    this.currentPage = 1;
    this.render();
  }

  render() {
    this.tbody.innerHTML = '';
    const total = this.filteredRows.length;
    const start = (this.currentPage - 1) * this.pageSize;
    const end = Math.min(start + this.pageSize, total);
    const pageRows = this.filteredRows.slice(start, end);

    if (pageRows.length === 0) {
      const colSpan = this.table.querySelectorAll('th').length || 6;
      this.tbody.innerHTML = `<tr><td colspan="${colSpan}" style="text-align: center; color: var(--text-muted); padding: 32px;">No se encontraron registros que coincidan con los filtros aplicados.</td></tr>`;
    } else {
      pageRows.forEach(row => this.tbody.appendChild(row));
    }

    this.renderPagination(total, start, end);
  }

  renderPagination(total, start, end) {
    // 1. Información de registros ("Mostrando X a Y de Z")
    const infoEl = document.querySelector(this.options.infoSelector);
    if (infoEl) {
      infoEl.textContent = total > 0 
        ? `Mostrando ${start + 1} a ${end} de ${total} registros`
        : '0 registros encontrados';
    }

    // 2. Controles de paginación
    const paginationEl = document.querySelector(this.options.paginationSelector);
    if (!paginationEl) return;

    paginationEl.innerHTML = '';
    const totalPages = Math.ceil(total / this.pageSize) || 1;

    // Botón Anterior
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '&laquo;';
    prevBtn.disabled = this.currentPage === 1;
    prevBtn.addEventListener('click', () => {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.render();
      }
    });
    paginationEl.appendChild(prevBtn);

    // Botones de Páginas Numéricas
    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `page-btn ${i === this.currentPage ? 'active' : ''}`;
        pageBtn.textContent = i;
        pageBtn.addEventListener('click', () => {
          this.currentPage = i;
          this.render();
        });
        paginationEl.appendChild(pageBtn);
      } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
        const dots = document.createElement('span');
        dots.className = 'page-btn';
        dots.style.border = 'none';
        dots.style.background = 'transparent';
        dots.textContent = '...';
        paginationEl.appendChild(dots);
      }
    }

    // Botón Siguiente
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '&raquo;';
    nextBtn.disabled = this.currentPage === totalPages || totalPages === 0;
    nextBtn.addEventListener('click', () => {
      if (this.currentPage < totalPages) {
        this.currentPage++;
        this.render();
      }
    });
    paginationEl.appendChild(nextBtn);
  }
}
