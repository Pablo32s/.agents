#!/usr/bin/env bash
# ==============================================================================
# SAGE / CGE & AGENTS - Universal Shell Environment & Aliases
# ==============================================================================
# Este archivo es agnóstico y versionado en Git (~/.agents/aliases.sh).
# Carga dinámicamente las variables locales de ~/.agents/.env si existe.
# ==============================================================================

# 1. Cargar variables de entorno locales de la máquina
if [ -f "${HOME}/.agents/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source "${HOME}/.agents/.env"
    set +a
fi

# 2. Asegurar directorios de binarios en el PATH
export PATH="${HOME}/.local/bin:${HOME}/.agents/bin:${PATH}"

# 3. Aliases de Sincronización y Skills
alias sync-skills='sync-skills'

# 4. Aliases de SAGE / CGE Sync
alias cge_sync='cge-sync'
alias cge-sync='cge-sync'
alias cge_pull='cge-sync pull'
alias cge-pull='cge-sync pull'
alias cge_push='cge-sync push'
alias cge-push='cge-sync push'
alias cge_rsync='cge-sync'

# 5. Aliases de Git y Linter CGE
alias cge_git='cge-git'
alias cge-git='cge-git'
alias cge_lint='cge-lint'
alias cge-lint='cge-lint'
alias phplint='cge-lint'

# 6. Aliases de Herramientas de Inspección y Edición
alias readfile='readfile'
alias cge_read='readfile'
alias cge-read='readfile'
alias writefile='writefile'
alias cge_write='writefile'
alias cge-write='writefile'
alias cge_find='cge-find'
alias cge-find='cge-find'
alias findfile='cge-find'

# 7. Testing y Workers Locales
alias cge_test='cge-test'
alias cge-test='cge-test'
alias pyexec='pyexec'
alias pyrun='pyexec'
alias worker='worker'
alias cbm='cbm'
