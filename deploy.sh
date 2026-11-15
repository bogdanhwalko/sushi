#!/usr/bin/env bash
#
# Деплой 107 Sushi на сервер без Docker (PHP + веб-сервер напряму).
#
# Запуск на сервері з кореня проєкту (від користувача, якому належать файли сайту):
#     bash deploy.sh
#
# Що робить:
#   1. перевіряє PHP, розширення, composer, стан git і налаштування БД;
#   2. вмикає сторінку "Сайт оновлюється";
#   3. git pull обраної гілки (лише fast-forward);
#   4. composer install --no-dev за composer.lock;
#   5. якщо є нові міграції — робить бекап БД і застосовує їх;
#   6. готує папки, чистить опубліковані assets;
#   7. вимикає режим обслуговування і перевіряє, що сайт відповідає.
#
# Опції:
#   -b, --branch NAME   гілка для деплою (за замовчуванням main)
#   --no-backup         не робити бекап БД перед міграціями
#   --skip-pull         не оновлювати код з git (код уже на місці)
#   --no-maintenance    не вмикати сторінку "Сайт оновлюється"
#   -h, --help          довідка
#
# Змінні оточення (необов'язкові):
#   PHP_BIN             шлях до PHP CLI (за замовчуванням: php), напр. /opt/php81/bin/php
#   COMPOSER_BIN        команда composer (за замовчуванням: composer або ./composer.phar)
#   REMOTE              git remote (за замовчуванням: upstream гілки або origin)
#   BACKUP_DIR          куди класти бекапи БД (за замовчуванням: ../sushi-backups поруч із проєктом,
#                       а якщо туди писати не можна — app/runtime/backups)
#   KEEP_BACKUPS        скільки останніх бекапів зберігати (за замовчуванням: 10)
#   SITE_URL            адреса сайту для перевірки після деплою, напр. https://sushi.ua
#   PHP_FPM_RELOAD_CMD  команда перезапуску PHP-FPM, якщо opcache не бачить змін,
#                       напр. "sudo systemctl reload php8.1-fpm"
#
# Приклад:
#   SITE_URL=https://sushi.ua PHP_BIN=/usr/bin/php8.1 bash deploy.sh
#

set -Eeuo pipefail

# Увесь скрипт — в одному блоці { ... }: bash прочитає його повністю до запуску,
# тож git pull, який оновлює сам deploy.sh, не зламає поточне виконання.
{

# ----------------------------------------------------------------------------
# Налаштування
# ----------------------------------------------------------------------------
BRANCH="${BRANCH:-main}"
REMOTE="${REMOTE:-}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-}"
KEEP_BACKUPS="${KEEP_BACKUPS:-10}"
SITE_URL="${SITE_URL:-}"
PHP_FPM_RELOAD_CMD="${PHP_FPM_RELOAD_CMD:-}"

DO_BACKUP=1
DO_PULL=1
DO_MAINTENANCE=1

MIN_PHP_ID=80100          # 8.1.0
MIN_PHP_LABEL="8.1"
REQUIRED_EXTENSIONS=(pdo_mysql mbstring curl fileinfo openssl json ctype)

usage() {
    sed -n '3,40p' "$0" | sed 's/^# \{0,1\}//'
    exit 0
}

ORIG_ARGS=("$@")   # для перезапуску з логуванням (див. нижче)

while [[ $# -gt 0 ]]; do
    case "$1" in
        -b|--branch)      BRANCH="${2:?Вкажіть назву гілки після $1}"; shift 2 ;;
        --no-backup)      DO_BACKUP=0; shift ;;
        --skip-pull)      DO_PULL=0; shift ;;
        --no-maintenance) DO_MAINTENANCE=0; shift ;;
        -h|--help)        usage ;;
        *) echo "Невідома опція: $1 (див. bash deploy.sh --help)" >&2; exit 2 ;;
    esac
done

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/app"
WEB="$ROOT/public_html"
RUNTIME="$APP/runtime"
# Бекапи — поза public_html. Спершу поруч із проєктом (../sushi-backups); якщо туди
# писати не можна (на хостингу проєкт часто лежить прямо в домашній папці) — app/runtime/backups,
# яка недоступна з вебу і не потрапляє в git.
if [[ -z "${BACKUP_DIR:-}" ]]; then
    backup_parent="$(dirname "$ROOT")"
    if [[ -d "$backup_parent/sushi-backups" && -w "$backup_parent/sushi-backups" ]] \
        || [[ ! -e "$backup_parent/sushi-backups" && -w "$backup_parent" ]]; then
        BACKUP_DIR="$backup_parent/sushi-backups"
    else
        BACKUP_DIR="$RUNTIME/backups"
    fi
fi

LOCK_DIR="$RUNTIME/deploy.lock"
MAINT_FLAG="$RUNTIME/maintenance.flag"

mkdir -p "$RUNTIME/logs"

# Вивід дублюється в лог: скрипт перезапускає сам себе через "| tee".
# Не через exec > >(tee ...) — на частині хостингів немає /dev/fd, і так падає.
if [[ -z "${DEPLOY_LOG_FILE:-}" ]]; then
    export DEPLOY_LOG_FILE="$RUNTIME/logs/deploy-$(date +%Y%m%d-%H%M%S).log"
    [[ -t 1 ]] && export DEPLOY_COLOR=1
    set +e
    # tee ігнорує Ctrl+C, щоб у лог потрапило повідомлення про скасування
    bash "$ROOT/$(basename "${BASH_SOURCE[0]}")" ${ORIG_ARGS[@]+"${ORIG_ARGS[@]}"} 2>&1 \
        | (trap '' INT TERM; tee -a "$DEPLOY_LOG_FILE")
    exit "${PIPESTATUS[0]}"
fi
LOG_FILE="$DEPLOY_LOG_FILE"

# ----------------------------------------------------------------------------
# Вивід
# ----------------------------------------------------------------------------
if [[ -n "${DEPLOY_COLOR:-}" ]]; then
    C_BLUE=$'\e[1;34m'; C_GREEN=$'\e[32m'; C_YELLOW=$'\e[33m'; C_RED=$'\e[1;31m'; C_RESET=$'\e[0m'
else
    C_BLUE=''; C_GREEN=''; C_YELLOW=''; C_RED=''; C_RESET=''
fi

STEP_NO=0
step() { STEP_NO=$((STEP_NO + 1)); echo; echo "${C_BLUE}==> [$STEP_NO] $*${C_RESET}"; }
ok()   { echo "  ${C_GREEN}✓${C_RESET} $*"; }
info() { echo "    $*"; }
warn() { echo "  ${C_YELLOW}!${C_RESET} $*"; WARNINGS+=("$*"); }
die()  { echo; echo "${C_RED}✗ $*${C_RESET}" >&2; exit 1; }

WARNINGS=()
MAINT_CREATED=0
LOCK_CREATED=0
PREV_COMMIT=""
NEW_COMMIT=""
BACKUP_FILE=""
MIGRATIONS_APPLIED=0
SITE_INCONSISTENT=0

php_run() { "$PHP_BIN" "$@"; }
yii()     { "$PHP_BIN" "$APP/yii" "$@"; }
git_()    { git -C "$ROOT" "$@"; }

cleanup() {
    local code=$?
    local keep_maintenance=0

    # Впало після оновлення коду — сайт може бути напівоновленим (новий код без міграцій тощо).
    # Відвідувачам краще бачити "Оновлюємо сайт", ніж помилки й неробочі замовлення.
    if [[ $code -ne 0 && $MAINT_CREATED -eq 1 && $SITE_INCONSISTENT -eq 1 ]]; then
        keep_maintenance=1
    elif [[ $MAINT_CREATED -eq 1 ]]; then
        rm -f "$MAINT_FLAG"
        echo "    режим обслуговування вимкнено"
    fi
    if [[ $LOCK_CREATED -eq 1 ]]; then
        rmdir "$LOCK_DIR" 2>/dev/null || true
    fi

    if [[ $code -ne 0 ]]; then
        echo
        echo "${C_RED}Деплой НЕ завершено (код $code).${C_RESET} Лог: $LOG_FILE"
        if [[ $keep_maintenance -eq 1 ]]; then
            echo
            echo "${C_YELLOW}Сайт ЗАЛИШЕНО в режимі обслуговування${C_RESET} — оновлення обірвалось посередині."
            echo "  • виправте причину і запустіть:  bash deploy.sh"
            echo "  • відкрити сайт як є (на свій ризик):  rm \"$MAINT_FLAG\""
        fi
        if [[ -n "$PREV_COMMIT" && -n "$NEW_COMMIT" && "$PREV_COMMIT" != "$NEW_COMMIT" ]]; then
            echo "  • повернути попередню версію коду:"
            echo "        git -C \"$ROOT\" reset --hard $PREV_COMMIT"
            echo "        bash deploy.sh --skip-pull"
            [[ $MIGRATIONS_APPLIED -eq 1 ]] && echo "    (міграції вже застосовано — відкат коду їх не скасує)"
        fi
        if [[ -n "$BACKUP_FILE" ]]; then
            echo "Бекап БД перед міграціями: $BACKUP_FILE"
        fi
    fi
}
trap cleanup EXIT
trap 'exit 130' INT TERM

echo "Деплой 107 Sushi — $(date '+%Y-%m-%d %H:%M:%S')"
echo "Проєкт: $ROOT"
echo "Лог:    $LOG_FILE"

# ----------------------------------------------------------------------------
step "Перевірка оточення"
# ----------------------------------------------------------------------------
if ! mkdir "$LOCK_DIR" 2>/dev/null; then
    die "Інший деплой уже виконується (є $LOCK_DIR).
  Якщо попередній деплой обірвався і зараз нічого не запущено — видаліть цю папку:
      rmdir \"$LOCK_DIR\""
fi
LOCK_CREATED=1

if [[ "$(id -u)" -eq 0 ]]; then
    warn "Скрипт запущено від root — нові файли належатимуть root, і PHP може не мати до них доступу. Краще запускати від власника файлів сайту."
fi

git_ rev-parse --is-inside-work-tree >/dev/null 2>&1 || die "$ROOT — не git-репозиторій."
ok "git: $(git --version | awk '{print $3}')"

command -v "$PHP_BIN" >/dev/null 2>&1 || die "PHP не знайдено ($PHP_BIN). Вкажіть шлях: PHP_BIN=/шлях/до/php bash deploy.sh"
php_run -r "exit(PHP_VERSION_ID >= $MIN_PHP_ID ? 0 : 1);" \
    || die "Потрібен PHP $MIN_PHP_LABEL+, а $PHP_BIN — $(php_run -r 'echo PHP_VERSION;'). Вкажіть інший: PHP_BIN=/шлях/до/php8.1"
ok "PHP $(php_run -r 'echo PHP_VERSION;')"

missing=()
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    php_run -r "exit(extension_loaded('$ext') ? 0 : 1);" || missing+=("$ext")
done
[[ ${#missing[@]} -eq 0 ]] || die "Не вистачає розширень PHP: ${missing[*]}"
ok "розширення PHP: ${REQUIRED_EXTENSIONS[*]}"

# стискання фото шапки (адмінка → «Шапка сайту»): не обов'язково, але без нього фото зберігаються як є
image_lib="$(php_run -r 'if (extension_loaded("imagick")) { echo "Imagick", in_array("WEBP", Imagick::queryFormats("WEBP")) ? " (WebP)" : " (JPEG)"; } elseif (extension_loaded("gd")) { echo "GD", function_exists("imagewebp") ? " (WebP)" : " (JPEG)"; }')"
if [[ -n "$image_lib" ]]; then
    ok "стискання фото: $image_lib"
else
    warn "немає imagick і gd — фото шапки зберігатимуться без стискання (увімкніть imagick у cPanel → Select PHP Version)"
fi

# composer: явно задана команда → composer у PATH → ./composer.phar → завантажити composer.phar
if [[ -n "$COMPOSER_BIN" ]]; then
    read -r -a COMPOSER_CMD <<< "$COMPOSER_BIN"
elif command -v composer >/dev/null 2>&1; then
    COMPOSER_CMD=(composer)
elif [[ -f "$ROOT/composer.phar" ]]; then
    COMPOSER_CMD=("$PHP_BIN" "$ROOT/composer.phar")
else
    info "composer не знайдено — завантажую composer.phar у $ROOT"
    command -v curl >/dev/null 2>&1 || die "Немає ні composer, ні curl для його завантаження."
    tmp_installer="$(mktemp)"
    curl -fsSL https://getcomposer.org/installer -o "$tmp_installer"
    expected_sig="$(curl -fsSL https://composer.github.io/installer.sig)"
    actual_sig="$(php_run -r "echo hash_file('sha384', '$tmp_installer');")"
    if [[ "$expected_sig" != "$actual_sig" ]]; then
        rm -f "$tmp_installer"
        die "Підпис інсталятора composer не збігся — завантаження скасовано."
    fi
    php_run "$tmp_installer" --quiet --install-dir="$ROOT" --filename=composer.phar
    rm -f "$tmp_installer"
    COMPOSER_CMD=("$PHP_BIN" "$ROOT/composer.phar")
fi
ok "composer: $("${COMPOSER_CMD[@]}" --version --no-ansi 2>/dev/null | head -1)"

# Локальні зміни у файлах з репозиторію зламають git pull — їх має не бути.
dirty="$(git_ status --porcelain --untracked-files=no)"
if [[ -n "$dirty" ]]; then
    die "На сервері змінено файли з репозиторію:
$dirty

  Git pull не може їх перезаписати. Що робити:
   • налаштування БД перенесіть у app/config/db-local.php (зразок: db-local.example.php),
     секрети та інші налаштування — у app/config/web-local.php (зразок: web-local.example.php);
   • потім поверніть файли до версії з репозиторію:  git checkout -- <файл>
   • переглянути зміни:  git diff"
fi
ok "робоча копія чиста"

if [[ ! -f "$APP/config/db-local.php" ]]; then
    die "Немає app/config/db-local.php з налаштуваннями БД сервера.
  У репозиторії db.php налаштований під локальний Docker (host=mariadb-s), на сервері він не спрацює.
  Створіть файл (зразок — app/config/db-local.example.php; якщо його ще немає, він з'явиться після git pull):

<?php
return [
    'class' => 'yii\\db\\Connection',
    'dsn' => 'mysql:host=localhost;dbname=НАЗВА_БД',
    'username' => 'КОРИСТУВАЧ',
    'password' => 'ПАРОЛЬ',
    'charset' => 'utf8mb4',
];"
fi
ok "налаштування БД: app/config/db-local.php"

# папку для бекапів перевіряємо одразу, а не посеред деплою
if [[ $DO_BACKUP -eq 1 ]]; then
    if ! mkdir -p "$BACKUP_DIR" 2>/dev/null || [[ ! -w "$BACKUP_DIR" ]]; then
        die "Немає прав на запис у папку для бекапів БД: $BACKUP_DIR
  Вкажіть іншу:  BACKUP_DIR=/шлях/до/папки bash deploy.sh"
    fi
    chmod 700 "$BACKUP_DIR" 2>/dev/null || true
    ok "бекапи БД: $BACKUP_DIR"
fi

# Режим dev на продакшні вмикає детальні помилки — попереджаємо.
cli_env="$(php_run -r "require '$APP/config/env.php'; echo YII_ENV;")"
if [[ "$cli_env" != "prod" ]]; then
    warn "YII_ENV=$cli_env у консолі сервера. На продакшні має бути prod (змінну YII_ENV краще не задавати взагалі)."
fi
if grep -qsiE '^\s*SetEnv\s+YII_ENV\s+dev' "$WEB/.htaccess"; then
    warn "У public_html/.htaccess задано SetEnv YII_ENV dev — сайт показуватиме детальні помилки відвідувачам."
fi

# ----------------------------------------------------------------------------
step "Отримання змін з git"
# ----------------------------------------------------------------------------
PREV_COMMIT="$(git_ rev-parse HEAD)"
incoming=""

if [[ $DO_PULL -eq 1 ]]; then
    if [[ -z "$REMOTE" ]]; then
        upstream="$(git_ rev-parse --abbrev-ref "$BRANCH@{upstream}" 2>/dev/null || true)"
        if [[ -n "$upstream" ]]; then
            REMOTE="${upstream%%/*}"
        elif git_ remote | grep -qx origin; then
            REMOTE="origin"
        else
            REMOTE="$(git_ remote | head -1)"
        fi
    fi
    [[ -n "$REMOTE" ]] || die "У репозиторії не налаштовано жодного git remote."

    git_ fetch --prune "$REMOTE" "$BRANCH" || die "Не вдалося виконати git fetch $REMOTE $BRANCH."

    current_branch="$(git_ rev-parse --abbrev-ref HEAD)"
    if [[ "$current_branch" != "$BRANCH" ]]; then
        info "перемикаюсь з гілки $current_branch на $BRANCH"
        if git_ show-ref --verify --quiet "refs/heads/$BRANCH"; then
            git_ checkout "$BRANCH"
        else
            git_ checkout -b "$BRANCH" --track "$REMOTE/$BRANCH"
        fi
    fi

    incoming="$(git_ log --oneline "HEAD..$REMOTE/$BRANCH")"
    if [[ -z "$incoming" ]]; then
        ok "нових комітів немає ($REMOTE/$BRANCH) — оновлю залежності та міграції на поточній версії"
    else
        info "нові коміти ($REMOTE/$BRANCH):"
        echo "$incoming" | sed 's/^/      /'
    fi
else
    info "пропущено (--skip-pull)"
fi

# ----------------------------------------------------------------------------
step "Режим обслуговування"
# ----------------------------------------------------------------------------
if [[ $DO_MAINTENANCE -eq 1 ]]; then
    date '+%Y-%m-%d %H:%M:%S' > "$MAINT_FLAG"
    MAINT_CREATED=1
    ok "увімкнено — відвідувачі бачать сторінку \"Оновлюємо сайт\""
else
    info "пропущено (--no-maintenance)"
fi

# Звідси сайт може бути в проміжному стані (новий код, старі залежності чи схема БД).
# Якщо деплой далі впаде, сторінка обслуговування лишиться — див. cleanup().
SITE_INCONSISTENT=1

if [[ $DO_PULL -eq 1 && -n "$incoming" ]]; then
    git_ merge --ff-only "$REMOTE/$BRANCH" \
        || die "Не вдалося оновити код без злиття (fast-forward): гілка на сервері розійшлася з $REMOTE/$BRANCH.
  Перевірте:  git -C \"$ROOT\" status ;  git -C \"$ROOT\" log --oneline --graph -10 --all"
fi
NEW_COMMIT="$(git_ rev-parse HEAD)"
ok "код: ${PREV_COMMIT:0:8} → ${NEW_COMMIT:0:8} ($(git_ log -1 --format=%s))"

# ----------------------------------------------------------------------------
step "Залежності composer"
# ----------------------------------------------------------------------------
[[ -f "$APP/composer.lock" ]] || warn "Немає app/composer.lock — версії пакетів будуть підібрані заново. Закомітьте composer.lock."

"${COMPOSER_CMD[@]}" install \
    --working-dir="$APP" \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    || die "composer install завершився з помилкою."
ok "залежності встановлено (без dev-пакетів)"

# ----------------------------------------------------------------------------
step "База даних"
# ----------------------------------------------------------------------------
yii migrate/history 1 --interactive=0 >/dev/null 2>&1 \
    || die "Немає підключення до БД. Перевірте app/config/db-local.php
  (деталі: $PHP_BIN app/yii migrate/history 1)"
ok "підключення до БД є"

new_migrations="$(yii migrate/new --interactive=0 2>&1 || true)"
if grep -q "No new migrations found" <<< "$new_migrations"; then
    ok "нових міграцій немає"
else
    info "нові міграції:"
    grep -E '^\s+m[0-9]{6}_' <<< "$new_migrations" | sed 's/^\s*/      /' || true

    if [[ $DO_BACKUP -eq 1 ]]; then
        dump_bin="$(command -v mariadb-dump || command -v mysqldump || true)"
        [[ -n "$dump_bin" ]] || die "Для бекапу БД потрібен mysqldump (або mariadb-dump), але його не знайдено.
  Встановіть клієнт MySQL або запустіть з --no-backup, якщо бекап зроблено іншим способом."

        # параметри підключення беремо з того самого конфігу, що й сайт
        db_info="$(php_run -r '
            $c = require $argv[1];
            $dsn = $c["dsn"] ?? "";
            preg_match("/host=([^;]+)/", $dsn, $h);
            preg_match("/port=([0-9]+)/", $dsn, $p);
            preg_match("/dbname=([^;]+)/", $dsn, $d);
            echo ($h[1] ?? "localhost"), "\n", ($p[1] ?? "3306"), "\n", ($d[1] ?? ""), "\n",
                 ($c["username"] ?? ""), "\n", ($c["password"] ?? ""), "\n";
        ' "$APP/config/db.php")" || die "Не вдалося прочитати налаштування БД з app/config/db-local.php."
        mapfile -t db <<< "$db_info"
        db_host="${db[0]:-localhost}"; db_port="${db[1]:-3306}"; db_name="${db[2]:-}"; db_user="${db[3]:-}"; db_pass="${db[4]:-}"
        [[ -n "$db_name" ]] || die "Не вдалося визначити назву БД з dsn у db-local.php."

        BACKUP_FILE="$BACKUP_DIR/sushi-$(date +%Y%m%d-%H%M%S)-${PREV_COMMIT:0:8}.sql.gz"

        info "бекап БД $db_name → $BACKUP_FILE"
        # пароль через змінну оточення, щоб він не світився в списку процесів
        if ! MYSQL_PWD="$db_pass" "$dump_bin" \
                --host="$db_host" --port="$db_port" --user="$db_user" \
                --single-transaction --quick --routines --triggers --no-tablespaces \
                "$db_name" | gzip > "$BACKUP_FILE"; then
            rm -f "$BACKUP_FILE"
            BACKUP_FILE=""
            die "Бекап БД не вдався — міграції не застосовано."
        fi
        chmod 600 "$BACKUP_FILE" 2>/dev/null || true
        ok "бекап: $(du -h "$BACKUP_FILE" | cut -f1)"

        # залишаємо лише KEEP_BACKUPS останніх бекапів
        old_list="$(ls -1t "$BACKUP_DIR"/sushi-*.sql.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) || true)"
        removed=0
        while IFS= read -r old; do
            if [[ -n "$old" && -f "$old" && "$old" == "$BACKUP_DIR"/sushi-*.sql.gz ]]; then
                rm -f "$old"
                removed=$((removed + 1))
            fi
        done <<< "$old_list"
        [[ $removed -eq 0 ]] || info "видалено старих бекапів: $removed (зберігаю $KEEP_BACKUPS)"
    else
        warn "бекап БД перед міграціями пропущено (--no-backup)"
    fi

    yii migrate --interactive=0 || die "Міграції завершились з помилкою."
    MIGRATIONS_APPLIED=1
    ok "міграції застосовано"
fi

# ----------------------------------------------------------------------------
step "Файли та кеш"
# ----------------------------------------------------------------------------
for dir in "$RUNTIME" "$WEB/assets" "$WEB/uploads/products" "$WEB/uploads/hero"; do
    mkdir -p "$dir"
    if [[ -w "$dir" ]]; then
        ok "запис дозволено: ${dir#"$ROOT"/}"
    else
        warn "немає прав на запис: ${dir#"$ROOT"/} — PHP не зможе зберігати файли"
    fi
done

# Опубліковані asset-бандли (адмінка, yii.js) — перегенеруються при першому запиті.
find "$WEB/assets" -mindepth 1 -maxdepth 1 -type d -exec rm -rf {} +
ok "опубліковані assets очищено"

# Покинуті гостьові кошики (див. також cron у app/commands/CartController.php).
if cleanup_out="$(yii cart/cleanup --interactive=0 2>&1)"; then
    ok "${cleanup_out##*: } поз. з покинутих гостьових кошиків видалено"
else
    warn "не вдалося очистити старі кошики: $cleanup_out"
fi

# Кнопка «Прийняте» під замовленнями в Telegram-групах: боти мають знати адресу сайту.
# Безпечно повторювати; чужий вебхук на боті не перезаписується.
if [[ "$SITE_URL" == https://* ]]; then
    if webhook_out="$(yii telegram/webhook "$SITE_URL" --interactive=0 2>&1)"; then
        ok "Telegram: кнопки під замовленнями підключено"
    else
        warn "Telegram: кнопки підключено не до всіх ботів:
$webhook_out"
    fi
else
    info "Telegram-кнопки не перевірено (потрібен SITE_URL=https://…; або адмінка → «Групи Telegram» → «Підключити кнопки»)"
fi

# Кеш даних (app/runtime/cache) свідомо не чиститься: там лічильники захисту
# від перебору паролів і спаму замовленнями.

if [[ -n "$PHP_FPM_RELOAD_CMD" ]]; then
    info "перезапуск PHP-FPM: $PHP_FPM_RELOAD_CMD"
    bash -c "$PHP_FPM_RELOAD_CMD" || warn "команда перезапуску PHP-FPM завершилась з помилкою"
fi

# ----------------------------------------------------------------------------
step "Завершення"
# ----------------------------------------------------------------------------
if [[ $MAINT_CREATED -eq 1 ]]; then
    rm -f "$MAINT_FLAG"
    MAINT_CREATED=0
    ok "режим обслуговування вимкнено"
fi

if [[ -n "$SITE_URL" ]]; then
    if command -v curl >/dev/null 2>&1; then
        body_file="$(mktemp)"
        code="$(curl -sS -o "$body_file" -w '%{http_code}' --max-time 20 "$SITE_URL" || echo "000")"
        if [[ "$code" == "200" ]]; then
            ok "сайт відповідає: $SITE_URL → 200"
            if grep -q 'yii-debug-toolbar' "$body_file"; then
                warn "на сайті видно debug-панель Yii — сайт працює в режимі dev!"
            fi
        else
            warn "сайт відповів кодом $code ($SITE_URL) — перевірте вручну та лог app/runtime/logs/app.log"
        fi
        rm -f "$body_file"
    else
        warn "curl не знайдено — перевірку сайту пропущено"
    fi
else
    info "перевірку сайту пропущено (задайте SITE_URL=https://ваш-домен)"
fi

echo
echo "${C_GREEN}Деплой завершено.${C_RESET}"
echo "  Версія:   ${PREV_COMMIT:0:8} → ${NEW_COMMIT:0:8}"
[[ $MIGRATIONS_APPLIED -eq 1 ]] && echo "  Міграції: застосовано"
[[ -n "$BACKUP_FILE" ]] && echo "  Бекап БД: $BACKUP_FILE"
echo "  Лог:      $LOG_FILE"
if [[ ${#WARNINGS[@]} -gt 0 ]]; then
    echo
    echo "${C_YELLOW}Попередження:${C_RESET}"
    for w in "${WARNINGS[@]}"; do echo "  • $w"; done
fi

exit 0
}
