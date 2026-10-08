<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function backup_log(string $message): void
{
    fwrite(STDOUT, '[backup] ' . $message . PHP_EOL);
}

function backup_fail(string $message): void
{
    fwrite(STDERR, '[backup] ' . $message . PHP_EOL);
    exit(1);
}

function split_sql(string $sql): array
{
    $statements = [];
    $buffer = '';
    $executable = false;
    $length = strlen($sql);
    $i = 0;

    while ($i < $length) {
        $char = $sql[$i];

        if ($char === '#' || ($char === '-' && $i + 1 < $length && $sql[$i + 1] === '-'
            && ($i + 2 >= $length || ctype_space($sql[$i + 2])))) {
            $newline = strpos($sql, "\n", $i);
            if ($newline === false) {
                $buffer .= substr($sql, $i);
                $i = $length;
            } else {
                $buffer .= substr($sql, $i, $newline - $i + 1);
                $i = $newline + 1;
            }
            continue;
        }

        if ($char === '/' && $i + 1 < $length && $sql[$i + 1] === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $end = $end === false ? $length : $end + 2;
            $buffer .= substr($sql, $i, $end - $i);
            if (substr($sql, $i, 3) === '/*!') {
                $executable = true;
            }
            $i = $end;
            continue;
        }

        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $j = $i + 1;
            while ($j < $length) {
                $inner = $sql[$j];
                if ($inner === '\\' && $quote !== '`') {
                    $j += 2;
                    continue;
                }
                if ($inner === $quote) {
                    if ($j + 1 < $length && $sql[$j + 1] === $quote) {
                        $j += 2;
                        continue;
                    }
                    $j++;
                    break;
                }
                $j++;
            }
            $buffer .= substr($sql, $i, $j - $i);
            $executable = true;
            $i = $j;
            continue;
        }

        if ($char === ';') {
            if ($executable && trim($buffer) !== '') {
                $statements[] = trim($buffer);
            }
            $buffer = '';
            $executable = false;
            $i++;
            continue;
        }

        if (!ctype_space($char)) {
            $executable = true;
        }
        $buffer .= $char;
        $i++;
    }

    if ($executable && trim($buffer) !== '') {
        $statements[] = trim($buffer);
    }

    return $statements;
}

function table_count(PDO $pdo): int
{
    return (int) $pdo->query(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()'
    )->fetchColumn();
}

function strip_sql_comments(string $sql): string
{
    $out = '';
    $length = strlen($sql);
    $i = 0;

    while ($i < $length) {
        $char = $sql[$i];

        if ($char === '#') {
            $newline = strpos($sql, "\n", $i);
            $i = $newline === false ? $length : $newline + 1;
            continue;
        }

        if ($char === '-' && $i + 1 < $length && $sql[$i + 1] === '-'
            && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) {
            $newline = strpos($sql, "\n", $i);
            $i = $newline === false ? $length : $newline + 1;
            continue;
        }

        if ($char === '/' && $i + 1 < $length && $sql[$i + 1] === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $i = $end === false ? $length : $end + 2;
            continue;
        }

        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $j = $i + 1;
            while ($j < $length) {
                $inner = $sql[$j];
                if ($inner === '\\' && $quote !== '`') {
                    $j += 2;
                    continue;
                }
                if ($inner === $quote) {
                    if ($j + 1 < $length && $sql[$j + 1] === $quote) {
                        $j += 2;
                        continue;
                    }
                    $j++;
                    break;
                }
                $j++;
            }
            $out .= substr($sql, $i, $j - $i);
            $i = $j;
            continue;
        }

        $out .= $char;
        $i++;
    }

    return $out;
}

$file = getenv('BACKUP_FILE') ?: 'backup.sql.gz';
if (!str_starts_with($file, '/')) {
    $file = base_path($file);
}

if (!is_file($file)) {
    backup_log('tidak ditemukan (' . $file . '), lewati impor');
    exit(0);
}

$force = in_array(
    strtolower((string) (getenv('BACKUP_FORCE') ?: 'false')),
    ['1', 'true', 'yes', 'on'],
    true
);

$pdo = DB::connection()->getPdo();
$marker = storage_path('app/.backup-imported');
$tables = table_count($pdo);

if (!$force && $tables > 0 && is_file($marker)) {
    backup_log('sudah pernah diimpor, lewati (set BACKUP_FORCE=true untuk mengulang)');
    exit(0);
}

$sql = str_ends_with($file, '.gz')
    ? file_get_contents('compress.zlib://' . $file)
    : file_get_contents($file);

if ($sql === false || $sql === '') {
    backup_fail('gagal membaca ' . $file);
}

$statements = split_sql($sql);

if ($statements === []) {
    backup_fail('tidak ada statement SQL di ' . $file);
}

backup_log(sprintf(
    'mengimpor %s (%s, %d statement, %d tabel sudah ada)...',
    basename($file),
    number_format(strlen($sql)) . ' byte SQL',
    count($statements),
    $tables
));

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$skipped = 0;

foreach ($statements as $index => $statement) {
    $code = trim(strip_sql_comments($statement));

    if (preg_match('/\A(?:USE\b|CREATE\s+DATABASE\b|DROP\s+DATABASE\b)/i', $code)) {
        $skipped++;
        continue;
    }

    try {
        $pdo->exec($statement);
    } catch (Throwable $e) {
        backup_fail(
            'statement #' . ($index + 1) . '/' . count($statements) . ' gagal: '
            . $e->getMessage() . "\n" . substr($statement, 0, 400)
        );
    }
}

$tables = table_count($pdo);

file_put_contents($marker, json_encode([
    'file' => basename($file),
    'imported_at' => date('c'),
    'statements' => count($statements),
    'tables' => $tables,
], JSON_PRETTY_PRINT) . PHP_EOL);

backup_log('selesai: ' . (count($statements) - $skipped) . ' statement dieksekusi, '
    . $skipped . ' dilewati (USE/CREATE DATABASE), ' . $tables . ' tabel');
