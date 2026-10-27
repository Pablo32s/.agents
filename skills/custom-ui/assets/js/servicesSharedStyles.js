/**
 * servicesSharedStyles.js — estilos compartidos para Shadow DOM (custom-ui).
 * Solo clases usadas por los componentes de competenciaDocente.
 * Todos los colores provienen de design-tokens.css (Blue & Slate). Sin #ffffff.
 */
export const servicesSharedStyles = `
  :host {
    display: block;
    font-family: var(--font-sans);
    color: var(--text-primary);
    box-sizing: border-box;
  }

  *, *::before, *::after { box-sizing: inherit; }

  svg.icon {
    width: 1em;
    height: 1em;
    vertical-align: -0.125em;
    flex-shrink: 0;
  }

  /* Segmented Control / Pill Bar (estándar SAGE) */
  .subtabs-container {
    display: inline-flex;
    align-items: center;
    background-color: var(--slate-100, #f1f5f9);
    padding: 4px;
    border-radius: 10px;
    border: 1px solid var(--border-light, #e2e8f0);
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
    padding: 9px 20px;
    font-family: inherit;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-muted, #64748b);
    cursor: pointer;
    outline: none;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    user-select: none;
  }

  .subtab-btn:hover {
    color: var(--text-primary, #0f172a);
    background-color: rgba(255, 255, 255, 0.65);
  }

  .subtab-btn.active {
    background-color: var(--bg-surface, #ffffff);
    color: var(--text-primary, #0f172a);
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
    cursor: default;
  }

  .subtab-btn svg {
    flex-shrink: 0;
    stroke: currentColor;
    fill: none;
    width: 16px;
    height: 16px;
    transition: stroke 0.18s ease;
  }

  .subtab-btn.active svg {
    stroke: var(--primary-600, #0284c7);
  }

  /* ---- Layout / containers ---- */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 8px;
  }

  .page-title {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }

  .page-subtitle {
    color: var(--text-secondary);
    font-size: var(--text-sm);
    margin: 0 0 var(--space-4);
  }

  .content-panel {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    flex: 1;
    min-width: 0;
  }

  /* ---- Forms ---- */
  .form-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    max-width: 760px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: var(--space-4);
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group.full { grid-column: 1 / -1; }

  label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
  }

  input, select, textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    font-family: inherit;
    background-color: var(--slate-50);
    color: var(--text-primary);
    transition: var(--transition);
  }

  input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--primary-600);
    background-color: var(--bg-surface);
    box-shadow: 0 0 0 3px var(--primary-100);
  }

  textarea { min-height: 90px; resize: vertical; }

  .form-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light);
  }

  /* ---- Buttons ---- */
  .btn, .btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 16px;
    font-size: 0.875rem;
    font-weight: 600;
    font-family: inherit;
    border-radius: var(--radius-md);
    cursor: pointer;
    border: 1px solid transparent;
    transition: var(--transition);
    line-height: 1.25;
  }

  .btn-primary { background-color: var(--primary-600); color: var(--slate-50); }
  .btn-primary:hover:not(:disabled) { background-color: var(--primary-700); transform: translateY(-1px); box-shadow: var(--shadow-md); }

  .btn-secondary { background-color: var(--bg-subtle); color: var(--text-secondary); border-color: var(--border-color); }
  .btn-secondary:hover { background-color: var(--primary-100); color: var(--primary-900); border-color: var(--primary-200); }

  .btn:disabled, .btn-primary:disabled, .btn-secondary:disabled { opacity: 0.6; cursor: not-allowed; }

  .action-btn {
    background: transparent;
    border: 1.5px solid transparent;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
  }

  .action-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
  }

  .action-btn.text-primary, .btn-edit {
    color: #0284c7;
    background-color: #f0f9ff;
    border: 1.5px solid #7dd3fc;
    box-shadow: 0 1px 2px rgba(2, 132, 199, 0.05);
  }

  .action-btn.text-primary:hover, .btn-edit:hover {
    background-color: #e0f2fe;
    border-color: #38bdf8;
    color: #0369a1;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.15);
  }

  .action-btn.text-danger, .btn-delete {
    color: #dc2626;
    background-color: #fff5f5;
    border: 1.5px solid #f87171;
    box-shadow: 0 1px 2px rgba(220, 38, 38, 0.05);
  }

  .action-btn.text-danger:hover, .btn-delete:hover {
    background-color: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.15);
  }

  /* ---- Toolbar / search ---- */
  .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    margin-bottom: 16px;
  }

  .search-container { margin-bottom: 16px; }

  .search-box {
    display: flex;
    align-items: center;
    min-width: 260px;
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 2px 8px;
  }

  .search-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
    background: var(--bg-surface, #ffffff);
    color: var(--text-primary);
    transition: var(--transition);
  }

  .search-input:focus {
    outline: none;
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
  }

  /* ---- Filter panel ---- */
  .filter-panel {
    background: var(--bg-subtle, #f8fafc);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .filter-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-light);
    padding-bottom: 8px;
  }

  .filter-panel-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--primary-900);
  }

  .filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
    align-items: flex-end;
  }

  /* ---- Tables (custom-ui estándar) ---- */
  .table-container,
  .table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
    max-height: 55vh;
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 8px;
    background: var(--bg-surface, #ffffff);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.03);
  }

  .table-responsive::-webkit-scrollbar,
  .table-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }

  .table-responsive::-webkit-scrollbar-track,
  .table-container::-webkit-scrollbar-track {
    background: #f8fafc;
  }

  .table-responsive::-webkit-scrollbar-thumb,
  .table-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
  }

  .table-responsive::-webkit-scrollbar-thumb:hover,
  .table-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  table, table.data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 0.88rem;
  }

  table.data-table thead,
  thead {
    background-color: #f8fafc;
  }

  th {
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

  td {
    padding: 12px 16px;
    border-bottom: 1px solid #eeeeee;
    color: #0f172a;
    vertical-align: middle;
  }

  tbody tr { transition: var(--transition, all 0.15s ease); }
  tbody tr:hover { background-color: #f8fafc; }
  tbody tr:last-child td { border-bottom: none; }

  th.sortable { cursor: pointer; user-select: none; position: relative; padding-right: 22px; }
  th.sortable:hover { background-color: #e2e8f0; color: #0f172a; }
  th.sortable::after { content: '↕'; position: absolute; right: 8px; opacity: 0.35; font-size: 0.8rem; }
  th.sort-asc::after { content: '▲'; opacity: 1; color: #0284c7; }
  th.sort-desc::after { content: '▼'; opacity: 1; color: #0284c7; }

  .table-spinner {
    width: 32px; height: 32px; margin: 32px auto;
    border: 3px solid var(--border-light);
    border-top-color: #0284c7;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin { to { transform: rotate(360deg); } }

  /* ---- Badges ---- */
  .badge-success {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 600; line-height: 1;
    background-color: var(--success-bg); color: var(--success-text);
    border: 1px solid var(--success-border);
  }

  .badge-warning {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 600; line-height: 1;
    background-color: var(--warning-bg); color: var(--warning-text);
    border: 1px solid var(--warning-border);
  }

  .badge-danger {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 600; line-height: 1;
    background-color: var(--danger-bg); color: var(--danger-text);
    border: 1px solid var(--danger-border);
  }

  .badge-info {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 600; line-height: 1;
    background-color: var(--info-bg); color: var(--info-text);
    border: 1px solid var(--info-border);
  }

  .badge-division {
    display: inline-block;
    background-color: #f1f5f9;
    color: #1e293b;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1.35;
    white-space: nowrap;
    border: 1px solid #e2e8f0;
  }

  /* ---- Table footer / pagination ---- */
  .table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 8px 8px;
    font-size: 0.85rem;
    color: #64748b;
  }

  .records-selector { display: inline-flex; align-items: center; gap: 6px; }
  .records-selector select {
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 0.85rem;
    color: #0f172a;
    background: #ffffff;
    outline: none;
  }

  .pagination { display: flex; align-items: center; gap: 4px; }

  .page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 6px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .page-btn:hover:not(:disabled) {
    background: #f1f5f9;
    border-color: #94a3b8;
  }

  .page-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    font-weight: 700;
  }

  .page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  /* ---- States ---- */
  .empty, .loading {
    text-align: center;
    padding: var(--space-8);
    color: var(--text-muted);
  }

  :host-context(.sr-only) { display: none; }
`;
