#!/usr/bin/env bash
# =============================================================================
# ERP Procurement MDU - Automated Smoke Test (local XAMPP stack)
#
# Provisions a disposable copy of deploy/ under htdocs + a fresh MySQL/MariaDB
# database, then exercises the full PO lifecycle and the recent bug fixes:
#   .htaccess security, admin123 login, create/edit (no duplicates), PPN toggle,
#   status workflow + activity log, PO attachments (all 4 types), settings
#   logo/signature uploads, and the printed PDF (approval + signature gating).
#
# Requirements: XAMPP (Apache + MySQL + PHP CLI) with root / empty password.
#   Tested under Git Bash on Windows with C:\xampp.
#
# Usage:
#   bash smoke_test.sh            # provision -> test -> teardown
#   KEEP=1 bash smoke_test.sh     # leave the app + DB in place after the run
#   bash smoke_test.sh --skip-lint
# Exit code: 0 = all checks passed, 1 = one or more failed.
# =============================================================================
set -u

# --- Config (override via env) -------------------------------------------------
XAMPP_DIR="${XAMPP_DIR:-/c/xampp}"
HTDOCS_DIR="${HTDOCS_DIR:-$XAMPP_DIR/htdocs}"
APP_DIR_NAME="${APP_DIR_NAME:-erp-mdu-smoketest}"
DB_NAME="${DB_NAME:-procurement_smoketest}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@procurement.mdutama.com}"
ADMIN_PASS="${ADMIN_PASS:-admin123}"
KEEP="${KEEP:-0}"

PHP_BIN="$XAMPP_DIR/php/php.exe"
MYSQL_BIN="$XAMPP_DIR/mysql/bin/mysql.exe"
APP_BASH="$HTDOCS_DIR/$APP_DIR_NAME"
BASE_URL="http://localhost/$APP_DIR_NAME"
JAR="$APP_BASH/_smoke/cookies.txt"

PASS=0
FAIL=0

win() { local p="$1"; if [[ "$p" == /c/* ]]; then echo "C:/${p#/c/}"; else echo "$p"; fi; }

say()   { printf '\n== %s ==\n' "$*"; }
ok()    { PASS=$((PASS+1)); printf '  \033[32mPASS\033[0m %s\n' "$*"; }
fail()  { FAIL=$((FAIL+1)); printf '  \033[31mFAIL\033[0m %s\n' "$*"; }
check() { # check <description> <expected-substring-present?> file_or_string
  local desc="$1" needle="$2" hay="$3"
  if grep -qF -- "$needle" <<<"$hay"; then ok "$desc"; else fail "$desc (missing: $needle)"; fi
}
check_absent() {
  local desc="$1" needle="$2" hay="$3"
  if grep -qF -- "$needle" <<<"$hay"; then fail "$desc (unexpected: $needle)"; else ok "$desc"; fi
}

json_val() { # json_val <file> <php-expr-data>  -> echoes value (uses $d for decoded array)
  "$PHP_BIN" -r '$d = json_decode(file_get_contents($argv[1]), true); eval("echo " . $argv[2] . ";");' "$(win "$1")" "$2"
}

require_tools() {
  [[ -x "$PHP_BIN" ]]   || { echo "PHP CLI not found: $PHP_BIN"; exit 2; }
  [[ -x "$MYSQL_BIN" ]] || { echo "MySQL CLI not found: $MYSQL_BIN"; exit 2; }
  command -v curl >/dev/null || { echo "curl not found"; exit 2; }
}

lint() {
  say "Lint PHP files"
  local bad=0 f
  while IFS= read -r f; do
    if ! "$PHP_BIN" -l "$f" >/dev/null 2>&1; then
      echo "  syntax error: $f"; bad=$((bad+1))
    fi
  done < <(find deploy -name '*.php')
  if [[ $bad -eq 0 ]]; then ok "all PHP files lint clean"; else fail "$bad PHP file(s) have syntax errors"; fi
}

provision() {
  say "Provision local environment"
  rm -rf "$APP_BASH"
  cp -r deploy "$APP_BASH" || { echo "copy failed"; exit 2; }
  # Point the copied config at the local database
  local cfg="$APP_BASH/api/config.php"
  sed -i "s/define('DB_USER', 'USERNAME_DATABASE_ANDA');/define('DB_USER', '$DB_USER');/" "$cfg"
  sed -i "s/define('DB_PASS', 'PASSWORD_DATABASE_ANDA');/define('DB_PASS', '$DB_PASS');/" "$cfg"
  sed -i "s/define('DB_NAME', 'NAMA_DATABASE_ANDA');/define('DB_NAME', '$DB_NAME');/" "$cfg"
  sed -i "s|define('APP_URL', 'http://procurement.mdutama.com');|define('APP_URL', '$BASE_URL');|" "$cfg"
  mkdir -p "$APP_BASH/_smoke"

  "$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -e \
    "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" || { echo "mysql create failed"; exit 2; }
  "$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" "$DB_NAME" < "$APP_BASH/database.sql" || { echo "mysql import failed"; exit 2; }
  ok "app copied to $APP_BASH and database '$DB_NAME' seeded"
}

teardown() {
  say "Teardown"
  rm -rf "$APP_BASH"
  "$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -e "DROP DATABASE IF EXISTS $DB_NAME;"
  ok "removed $APP_BASH and dropped $DB_NAME"
}

# ---------------------------------------------------------------------------
curl_get()  { curl -s -b "$JAR" -c "$JAR" "$@"; }
curl_post() { curl -s -b "$JAR" -c "$JAR" -H 'Content-Type: application/json' "$@"; }

# ---------------------------------------------------------------------------
main() {
  require_tools
  [[ "${1:-}" == "--skip-lint" ]] || lint
  provision

  local SMW
  SMW="$(win "$APP_BASH/_smoke")"

  # 1) .htaccess behaviour
  say "1. .htaccess"
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL/login.php")
  [[ "$code" == "200" ]] && ok "login.php -> 200" || fail "login.php -> $code"
  code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL/uploads/evil.php")
  [[ "$code" == "403" ]] && ok "uploads/*.php blocked (403)" || fail "uploads/*.php -> $code"
  code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE_URL/api/config.php")
  [[ "$code" == "403" ]] && ok "direct api/config.php blocked (403)" || fail "api/config.php -> $code"

  # 2) Login with the documented admin123
  say "2. Authentication"
  curl_post -d "{\"action\":\"login\",\"email\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASS\"}" "$BASE_URL/api/auth.php" > "$APP_BASH/_smoke/login.json"
  local login_ok
  login_ok=$(json_val "$APP_BASH/_smoke/login.json" 'var_export($d["success"], true)')
  [[ "$login_ok" == "true" ]] && ok "login with $ADMIN_PASS succeeds" || fail "login failed: $(cat "$APP_BASH/_smoke/login.json")"

  # 3) Create a PPN PO, then a Non-PPN PO
  say "3. PO creation + PPN toggle"
  curl_post -d '{"vendor_id":1,"project_id":1,"top":"Net 30","delivery_location":"Jakarta","status":"Draft","ppn_type":"ppn","quotation_attached":true,"approved":false,"notes":"smoke: ppn PO","items":[{"item_name":"Smoke PPN Item","quantity":4,"unit":"Pcs","price":50000}]}' "$BASE_URL/api/po.php" > "$APP_BASH/_smoke/po_ppn.json"
  local po_ppn po_ppn_num
  po_ppn=$(json_val "$APP_BASH/_smoke/po_ppn.json" '$d["data"]["id"]')
  po_ppn_num=$(json_val "$APP_BASH/_smoke/po_ppn.json" '$d["data"]["po_number"]')
  [[ -n "$po_ppn" ]] && ok "created PPN PO id=$po_ppn ($po_ppn_num)" || fail "create PPN PO failed"

  curl_post -d '{"vendor_id":2,"project_id":2,"top":"COD","delivery_location":"Bekasi","status":"Draft","ppn_type":"non","quotation_attached":false,"approved":false,"notes":"smoke: non ppn PO","items":[{"item_name":"Smoke Non-PPN Item","quantity":10,"unit":"Pcs","price":10000}]}' "$BASE_URL/api/po.php" > "$APP_BASH/_smoke/po_non.json"
  local po_non
  po_non=$(json_val "$APP_BASH/_smoke/po_non.json" '$d["data"]["id"]')
  [[ -n "$po_non" ]] && ok "created Non-PPN PO id=$po_non" || fail "create Non-PPN PO failed"

  local row
  row=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT CONCAT(ppn_percent,':',ppn_amount,':',grand_total) FROM $DB_NAME.purchase_orders WHERE id=$po_ppn;")
  [[ "$row" == "11.00:22000.00:222000.00" ]] && ok "PPN PO totals (11%, 22.000, 222.000)" || fail "PPN PO totals: $row"
  row=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT CONCAT(ppn_percent,':',ppn_amount,':',grand_total) FROM $DB_NAME.purchase_orders WHERE id=$po_non;")
  [[ "$row" == "0.00:0.00:100000.00" ]] && ok "Non-PPN PO totals (0% PPN)" || fail "Non-PPN PO totals: $row"

  # 4) Edit flow: prefill page + PUT update (no duplicate)
  say "4. Edit flow"
  local form_html
  form_html=$(curl_get "$BASE_URL/po_create.php?id=$po_ppn")
  check "edit page loads" "Edit Purchase Order" "$form_html"
  check "edit page prefills PO number" "value=\"$po_ppn_num\"" "$form_html"
  check "edit page prefills items" "Smoke PPN Item" "$form_html"
  check "edit page prefills notes" "smoke: ppn PO" "$form_html"

  local before_count
  before_count=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e "SELECT COUNT(*) FROM $DB_NAME.purchase_orders;")
  curl -s -X PUT -b "$JAR" -H 'Content-Type: application/json' -d "{\"id\":$po_ppn,\"vendor_id\":3,\"project_id\":1,\"top\":\"Net 60\",\"delivery_location\":\"Surabaya\",\"status\":\"Printed\",\"ppn_type\":\"ppn\",\"quotation_attached\":false,\"approved\":true,\"notes\":\"smoke: edited via PUT\",\"items\":[{\"item_name\":\"Smoke Edited Item\",\"quantity\":2,\"unit\":\"Box\",\"price\":250000}]}" "$BASE_URL/api/po.php" > "$APP_BASH/_smoke/po_put.json"
  local put_ok
  put_ok=$(json_val "$APP_BASH/_smoke/po_put.json" 'var_export($d["success"], true)')
  [[ "$put_ok" == "true" ]] && ok "PUT update succeeds" || fail "PUT update failed: $(cat "$APP_BASH/_smoke/po_put.json")"
  local after_count dup_count
  after_count=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e "SELECT COUNT(*) FROM $DB_NAME.purchase_orders;")
  [[ "$after_count" == "$before_count" ]] && ok "no duplicate PO created on edit ($after_count rows)" || fail "row count changed: $before_count -> $after_count"
  dup_count=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e "SELECT COUNT(*) FROM $DB_NAME.purchase_orders WHERE po_number='$po_ppn_num';")
  [[ "$dup_count" == "1" ]] && ok "PO number unique ($po_ppn_num x1)" || fail "duplicate PO number count: $dup_count"
  row=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT CONCAT(status,':',approved,':',quotation_attached,':',notes) FROM $DB_NAME.purchase_orders WHERE id=$po_ppn;")
  [[ "$row" == "Printed:1:0:smoke: edited via PUT" ]] && ok "edit persisted status/flags/notes" || fail "edit persisted values: $row"

  # 5) Status workflow
  say "5. Status workflow (Draft -> Printed -> Signed -> Invoiced -> Completed)"
  local st
  for st in Printed Signed Invoiced Completed; do
    curl -s -X PUT -b "$JAR" -H 'Content-Type: application/json' -d "{\"action\":\"status\",\"id\":$po_ppn,\"status\":\"$st\"}" "$BASE_URL/api/po.php" > "$APP_BASH/_smoke/status.json"
    st_ok=$(json_val "$APP_BASH/_smoke/status.json" 'var_export($d["success"], true)')
    [[ "$st_ok" == "true" ]] && ok "status -> $st" || fail "status -> $st failed: $(cat "$APP_BASH/_smoke/status.json")"
  done
  st_ok=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e "SELECT status FROM $DB_NAME.purchase_orders WHERE id=$po_ppn;")
  [[ "$st_ok" == "Completed" ]] && ok "final status is Completed" || fail "final status: $st_ok"

  # 6) Activity log
  say "6. Activity log"
  local logs
  logs=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT action FROM $DB_NAME.po_activity_log WHERE po_id=$po_ppn ORDER BY id;")
  check "log has 'created'"        "created"         "$logs"
  check "log has 'updated'"        "updated"         "$logs"
  check "log has 'status_changed'" "status_changed"  "$logs"
  local detail_html
  detail_html=$(curl_get "$BASE_URL/po_detail.php?id=$po_ppn")
  check "detail page renders status control" "Simpan Status" "$detail_html"
  check "detail page shows activity log"     "Status PO berubah" "$detail_html"

  # 7) Uploads: all 4 PO attachment types + settings logo/signature
  say "7. Uploads"
  printf 'dummy invoice'  > "$APP_BASH/_smoke/invoice.pdf"
  printf 'dummy quotation' > "$APP_BASH/_smoke/quotation.pdf"
  printf 'dummy signed po' > "$APP_BASH/_smoke/wet.pdf"
  printf 'dummy support'  > "$APP_BASH/_smoke/support.pdf"
  printf 'dummy logo'     > "$APP_BASH/_smoke/logo.png"
  printf 'dummy signature' > "$APP_BASH/_smoke/sig.png"
  local up
  up=$(curl -s -b "$JAR" -F "po_id=$po_ppn" -F "type=invoice_supplier" -F "file=@$SMW/invoice.pdf;type=application/pdf" "$BASE_URL/api/uploads.php")
  check "upload invoice_supplier"   '"success":true' "$up"
  up=$(curl -s -b "$JAR" -F "po_id=$po_ppn" -F "type=quotation" -F "file=@$SMW/quotation.pdf;type=application/pdf" "$BASE_URL/api/uploads.php")
  check "upload quotation"          '"success":true' "$up"
  up=$(curl -s -b "$JAR" -F "po_id=$po_ppn" -F "type=wet_signature" -F "file=@$SMW/wet.pdf;type=application/pdf" "$BASE_URL/api/uploads.php")
  check "upload wet_signature (PDF)" '"success":true' "$up"
  up=$(curl -s -b "$JAR" -F "po_id=$po_ppn" -F "type=supporting" -F "file=@$SMW/support.pdf;type=application/pdf" "$BASE_URL/api/uploads.php")
  check "upload supporting"         '"success":true' "$up"
  local att
  att=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT GROUP_CONCAT(type ORDER BY id SEPARATOR ',') FROM $DB_NAME.po_attachments WHERE po_id=$po_ppn;")
  [[ "$att" == "invoice_supplier,quotation,wet_signature,supporting" ]] && ok "all 4 attachments with ENUM types" || fail "attachment types: $att"

  up=$(curl -s -b "$JAR" -F "file=@$SMW/logo.png;type=image/png" "$BASE_URL/api/uploads.php?type=logo")
  check "upload logo (?type=logo)"   '"success":true' "$up"
  up=$(curl -s -b "$JAR" -F "file=@$SMW/sig.png;type=image/png" "$BASE_URL/api/uploads.php?type=signatures")
  check "upload signature (?type=signatures)" '"success":true' "$up"
  local setvals
  setvals=$("$MYSQL_BIN" -u "$DB_USER" --password="$DB_PASS" -N -e \
    "SELECT CONCAT(IFNULL((SELECT setting_value FROM $DB_NAME.system_settings WHERE setting_key='logo_file'),''),'|',IFNULL((SELECT setting_value FROM $DB_NAME.system_settings WHERE setting_key='signature_file'),''));")
  [[ "$setvals" == *"uploads/logo/"*"|"*"uploads/signatures/"* ]] && ok "logo_file + signature_file stored" || fail "settings files: $setvals"

  # 8) Printed PDF: PPN row gating + approval/signature gating
  say "8. Printed PDF"
  local print_html
  print_html=$(curl_get "$BASE_URL/pratinjau_cetak_po_pdf.php?id=$po_non")
  check_absent "non-PPN PO has no PPN row"    "PPN ("      "$print_html"
  check        "unapproved PO marked as not approved" "Belum Disetujui" "$print_html"
  print_html=$(curl_get "$BASE_URL/pratinjau_cetak_po_pdf.php?id=$po_ppn")
  check "approved+PPN PO shows PPN row"       "PPN (11%)"  "$print_html"
  check "approved PO shows uploaded signature" "uploads/signatures/" "$print_html"
  check_absent "approved PO no longer 'Belum Disetujui'" "Belum Disetujui" "$print_html"

  # 9) Settings save/load round-trip (no revert to defaults)
  say "9. Settings persistence"
  curl -s -X PUT -b "$JAR" -H 'Content-Type: application/json' \
    -d '{"company_name":"Perusahaan Smoke Test","signatory_name":"Pejabat Smoke Test","signature_position":"left"}' \
    "$BASE_URL/api/settings.php" > "$APP_BASH/_smoke/set_put.json"
  local set_ok
  set_ok=$(json_val "$APP_BASH/_smoke/set_put.json" 'var_export($d["success"], true)')
  [[ "$set_ok" == "true" ]] && ok "settings PUT succeeds" || fail "settings PUT failed: $(cat "$APP_BASH/_smoke/set_put.json")"
  local page
  page=$(curl_get "$BASE_URL/settings.php")
  check "input renders saved company name"        'value="Perusahaan Smoke Test"' "$page"
  check "input renders saved signatory name"      'value="Pejabat Smoke Test"'      "$page"
  check "live preview shows saved company name"   'id="previewCompanyName">Perusahaan Smoke Test' "$page"
  check "signature position preview aligns left"  'id="preview-auth-block">' "$page"
  grep -qF 'class="flex justify-start transition-all duration-300" id="preview-auth-block"' <<<"$page" \
    && ok "auth block positioned per saved signature_position (left)" \
    || fail "auth block position not persisted (left)"
  check "left radio reflects saved signature_position" 'id="pos-left" name="sig_pos" type="radio" value="left" checked' "$page"

  # Summary
  say "Summary"
  printf '  passed: %d   failed: %d\n' "$PASS" "$FAIL"
  if [[ "$FAIL" -eq 0 ]]; then
    printf '  \033[32mALL CHECKS PASSED\033[0m\n'
  else
    printf '  \033[31m%s CHECK(S) FAILED\033[0m\n' "$FAIL"
  fi

  if [[ "$KEEP" == "1" ]]; then
    echo "  (KEEP=1) environment left running: $BASE_URL  db: $DB_NAME"
  else
    teardown
  fi
  exit $(( FAIL > 0 ? 1 : 0 ))
}

main "${@:-}"
