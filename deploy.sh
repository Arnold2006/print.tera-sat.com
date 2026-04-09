#!/usr/bin/env bash
# deploy.sh — Pull the latest code, fix file-system ownership / permissions,
#             and apply any pending database migrations.
#
# Run this script (as root or with sudo) from the project root after every
# deployment instead of running "sudo git pull" on its own.  It pulls the
# latest code, transfers ownership of the writable directories to the
# web-server user, and then runs "php upgrade.php" to apply any new SQL
# migrations automatically.
#
# Usage:
#   sudo bash deploy.sh                       # default web-server user (www)
#   sudo WEB_USER=www-data bash deploy.sh     # Debian / Ubuntu
#   sudo WEB_USER=apache     bash deploy.sh   # RHEL / CentOS / AlmaLinux
#
# The script must be run from the root directory of the print.tera-sat.com
# project.

set -euo pipefail

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
# Set WEB_USER to the user your web server runs as.
# Common values:
#   www       — FreeBSD, OpenBSD
#   www-data  — Debian, Ubuntu
#   apache    — RHEL, CentOS, AlmaLinux, Fedora
#   nginx     — some Nginx-only stacks
WEB_USER="${WEB_USER:-www}"

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------
info()  { echo "  --> $*"; }
error() { echo "ERROR: $*" >&2; exit 1; }

# ---------------------------------------------------------------------------
# Guards
# ---------------------------------------------------------------------------
if [[ $EUID -ne 0 ]]; then
    error "This script must be run as root (use sudo)."
fi

if [[ ! -f "public/index.php" ]]; then
    error "Run this script from the print.tera-sat.com project root directory."
fi

if ! id -u "${WEB_USER}" &>/dev/null; then
    error "Web-server user '${WEB_USER}' not found. Set WEB_USER= to the correct user."
fi

# ---------------------------------------------------------------------------
# Steps
# ---------------------------------------------------------------------------
echo "==> Pulling latest code..."
git pull

echo "==> Setting ownership of writable directories to ${WEB_USER}:${WEB_USER}..."
# Only the directories the web server needs write access to are changed.
# This avoids giving the web server unnecessary access to source files.
#
#   storage/uploads/    — temporary uploaded images awaiting order confirmation
#   storage/permanent/  — confirmed order images kept for fulfillment
chown -R "${WEB_USER}:${WEB_USER}" storage/uploads/ storage/permanent/

echo "==> Setting directory and file permissions..."
# Directories: owner read/write/execute, group and others read/execute (755)
chmod -R 755 storage/uploads/ storage/permanent/

echo "==> Running database migrations..."
php upgrade.php

echo ""
echo "==> Deployment complete."
echo "    Web-server user : ${WEB_USER}"
echo "    Writable paths  : storage/uploads/  storage/permanent/"
echo ""
echo "    If this is a fresh installation, copy .env.example to .env,"
echo "    fill in your database credentials, and visit your site to confirm"
echo "    the setup is working."
