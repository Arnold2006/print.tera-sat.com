#!/usr/bin/env bash
# =============================================================================
# deploy.sh – Deploy / update print.tera-sat.com
#
# Usage:
#   First-time install:  bash deploy.sh
#   Update only:         bash deploy.sh --update
#
# Options:
#   --update    Pull latest code, fix permissions, skip interactive prompts
#   --no-db     Skip database setup / migration
#   --help      Show this help message
# =============================================================================

set -euo pipefail

# ── Colour helpers ────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
success() { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; }
die()     { error "$*"; exit 1; }
header()  { echo -e "\n${BOLD}${CYAN}==> $*${RESET}"; }

# ── Parse arguments ───────────────────────────────────────────────────────────
UPDATE_MODE=false
SKIP_DB=false

for arg in "$@"; do
    case "$arg" in
        --update)  UPDATE_MODE=true ;;
        --no-db)   SKIP_DB=true ;;
        --help|-h)
            sed -n '2,11p' "$0"
            exit 0
            ;;
        *) die "Unknown argument: $arg" ;;
    esac
done

# ── Resolve project root (directory containing this script) ───────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
PROJECT_ROOT="$SCRIPT_DIR"

echo -e "${BOLD}${CYAN}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║   print.tera-sat.com  –  Deploy Script       ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${RESET}"

# ─────────────────────────────────────────────────────────────────────────────
# 1. Prerequisite checks
# ─────────────────────────────────────────────────────────────────────────────
header "Checking prerequisites"

# PHP
if ! command -v php &>/dev/null; then
    die "PHP is not installed. Install PHP 8.1+ and try again."
fi
PHP_VER=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
PHP_MAJOR=$(php -r 'echo PHP_MAJOR_VERSION;')
PHP_MINOR=$(php -r 'echo PHP_MINOR_VERSION;')
if [[ "$PHP_MAJOR" -lt 8 ]] || { [[ "$PHP_MAJOR" -eq 8 ]] && [[ "$PHP_MINOR" -lt 1 ]]; }; then
    die "PHP 8.1 or higher required. Found: $PHP_VER"
fi
success "PHP $PHP_VER"

# Required PHP extensions
for ext in pdo pdo_mysql fileinfo mbstring; do
    if ! php -r "exit(extension_loaded('$ext') ? 0 : 1);" 2>/dev/null; then
        die "PHP extension '$ext' is not loaded. Install it and try again."
    fi
    success "PHP extension: $ext"
done

# MySQL client (for schema import)
if ! $SKIP_DB; then
    if ! command -v mysql &>/dev/null; then
        warn "mysql client not found – database setup will be skipped."
        warn "Run 'mysql -u <user> -p < database/schema.sql' manually."
        SKIP_DB=true
    fi
fi

# Git (needed for pull in update mode)
if $UPDATE_MODE && ! command -v git &>/dev/null; then
    die "git is not installed but --update was requested."
fi

# ─────────────────────────────────────────────────────────────────────────────
# 2. Pull latest code (update mode)
# ─────────────────────────────────────────────────────────────────────────────
if $UPDATE_MODE; then
    header "Pulling latest code"
    if git -C "$PROJECT_ROOT" rev-parse --is-inside-work-tree &>/dev/null; then
        git -C "$PROJECT_ROOT" pull --ff-only
        success "Repository updated."
    else
        warn "Not a git repository – skipping pull."
    fi
fi

# ─────────────────────────────────────────────────────────────────────────────
# 3. Environment file
# ─────────────────────────────────────────────────────────────────────────────
header "Environment configuration"

ENV_FILE="$PROJECT_ROOT/.env"
ENV_EXAMPLE="$PROJECT_ROOT/.env.example"

if [[ ! -f "$ENV_FILE" ]]; then
    if [[ ! -f "$ENV_EXAMPLE" ]]; then
        die ".env.example not found. Cannot continue."
    fi
    cp "$ENV_EXAMPLE" "$ENV_FILE"
    info "Created .env from .env.example"

    if ! $UPDATE_MODE; then
        echo ""
        warn "Please edit ${BOLD}.env${RESET}${YELLOW} with your settings before continuing."
        echo ""
        echo -e "  Key values to set:"
        echo -e "  ${BOLD}APP_URL${RESET}             – public URL (e.g. https://print.tera-sat.com)"
        echo -e "  ${BOLD}DB_HOST / DB_NAME / DB_USER / DB_PASS${RESET}"
        echo -e "  ${BOLD}ADMIN_USERNAME${RESET}      – admin panel login name"
        echo -e "  ${BOLD}ADMIN_PASSWORD_HASH${RESET} – bcrypt hash (see below)"
        echo ""
        echo -e "  Generate a password hash:"
        echo -e "  ${CYAN}php -r \"echo password_hash('yourpassword', PASSWORD_BCRYPT);\"${RESET}"
        echo ""
        read -rp "Press ENTER when you have finished editing .env … " _
    fi
else
    success ".env already exists – skipping copy."
fi

# Helper: read a value from .env
env_get() {
    local key="$1"
    grep -E "^${key}=" "$ENV_FILE" 2>/dev/null | head -1 | cut -d'=' -f2- | tr -d '\r'
}

DB_HOST=$(env_get DB_HOST)
DB_NAME=$(env_get DB_NAME)
DB_USER=$(env_get DB_USER)
DB_PASS=$(env_get DB_PASS)

: "${DB_HOST:=localhost}"
: "${DB_NAME:=print_service}"
: "${DB_USER:=root}"

# ─────────────────────────────────────────────────────────────────────────────
# 4. Create storage directories & set permissions
# ─────────────────────────────────────────────────────────────────────────────
header "Storage directories"

for dir in storage/uploads storage/permanent; do
    mkdir -p "$PROJECT_ROOT/$dir"
    chmod 755 "$PROJECT_ROOT/$dir"
    success "Directory ready: $dir"
done

# Ensure .gitkeep files exist (keep directories in git)
touch "$PROJECT_ROOT/storage/uploads/.gitkeep"
touch "$PROJECT_ROOT/storage/permanent/.gitkeep"

# ─────────────────────────────────────────────────────────────────────────────
# 5. Set file permissions
# ─────────────────────────────────────────────────────────────────────────────
header "File permissions"

# PHP source files: readable, not executable
find "$PROJECT_ROOT/src"     -type f -name "*.php" -exec chmod 644 {} +
find "$PROJECT_ROOT/public"  -type f -exec chmod 644 {} +
find "$PROJECT_ROOT/config"  -type f -exec chmod 640 {} +   # tighter on config

# .env contains secrets – restrict to owner only
chmod 600 "$ENV_FILE"
success ".env permissions: 600"

# This script itself must be executable
chmod +x "$PROJECT_ROOT/deploy.sh"
success "Permissions set."

# ─────────────────────────────────────────────────────────────────────────────
# 6. Database setup / migration
# ─────────────────────────────────────────────────────────────────────────────
if ! $SKIP_DB; then
    header "Database setup"

    SCHEMA_FILE="$PROJECT_ROOT/database/schema.sql"
    if [[ ! -f "$SCHEMA_FILE" ]]; then
        die "database/schema.sql not found."
    fi

    # Build mysql connection args
    MYSQL_ARGS=(-h "$DB_HOST" -u "$DB_USER")
    [[ -n "${DB_PASS:-}" ]] && MYSQL_ARGS+=(-p"$DB_PASS")

    # Test connection
    if ! mysql "${MYSQL_ARGS[@]}" -e "SELECT 1;" &>/dev/null; then
        warn "Cannot connect to MySQL with the credentials in .env."
        warn "Skipping automatic schema import."
        warn "Run manually: mysql -u $DB_USER -p < database/schema.sql"
    else
        mysql "${MYSQL_ARGS[@]}" < "$SCHEMA_FILE"
        success "Schema imported into database '${DB_NAME}'."
    fi
fi

# ─────────────────────────────────────────────────────────────────────────────
# 7. Validate PHP syntax
# ─────────────────────────────────────────────────────────────────────────────
header "PHP syntax check"

SYNTAX_OK=true
while IFS= read -r -d '' phpfile; do
    if ! php -l "$phpfile" &>/dev/null; then
        error "Syntax error in: $phpfile"
        php -l "$phpfile" 2>&1 | tail -1
        SYNTAX_OK=false
    fi
done < <(find "$PROJECT_ROOT/src" "$PROJECT_ROOT/public" "$PROJECT_ROOT/config" \
              -name "*.php" -print0 2>/dev/null)

if $SYNTAX_OK; then
    success "All PHP files pass syntax check."
else
    die "Fix the PHP syntax errors above and re-run deploy.sh."
fi

# ─────────────────────────────────────────────────────────────────────────────
# 8. Apache hint (non-blocking)
# ─────────────────────────────────────────────────────────────────────────────
header "Apache configuration hint"

echo -e "  Point your virtual-host document root to:"
echo -e "  ${BOLD}${PROJECT_ROOT}/public${RESET}"
echo ""
echo -e "  Minimal virtual-host snippet:"
echo -e "  ${CYAN}<VirtualHost *:80>"
echo -e "      ServerName print.tera-sat.com"
echo -e "      DocumentRoot ${PROJECT_ROOT}/public"
echo -e "      <Directory ${PROJECT_ROOT}/public>"
echo -e "          AllowOverride All"
echo -e "          Require all granted"
echo -e "      </Directory>"
echo -e "  </VirtualHost>${RESET}"
echo ""
echo -e "  Enable mod_rewrite if not already active:"
echo -e "  ${CYAN}a2enmod rewrite && systemctl reload apache2${RESET}"

# ─────────────────────────────────────────────────────────────────────────────
# Done
# ─────────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════╗"
echo -e "║   Deployment complete!                       ║"
echo -e "╚══════════════════════════════════════════════╝${RESET}"
echo ""
APP_URL=$(env_get APP_URL)
: "${APP_URL:=http://localhost}"
echo -e "  Application URL : ${BOLD}${APP_URL}${RESET}"
echo -e "  Admin panel     : ${BOLD}${APP_URL}/?page=admin&action=login${RESET}"
echo ""
