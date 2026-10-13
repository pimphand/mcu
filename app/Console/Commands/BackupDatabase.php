<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database
                            {--disk= : Disk tujuan penyimpanan (default dari config backup.disk)}
                            {--folder= : Folder tujuan di dalam bucket (default dari config backup.folder)}
                            {--keep-local : Simpan juga salinan .sql.gz lokal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database MySQL, kompres gzip, lalu unggah ke Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = $this->option('disk') ?: config('backup.disk');
        $folder = trim($this->option('folder') ?: config('backup.folder'), '/');
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error("Backup hanya mendukung koneksi mysql, koneksi aktif: {$connection}");

            return self::FAILURE;
        }

        $db = config("database.connections.{$connection}");

        $this->info("Membuat dump database \"{$db['database']}\" ...");

        $tmpFile = tempnam(sys_get_temp_dir(), 'dbbackup_').'.sql.gz';

        try {
            $this->dump($db, $tmpFile);
        } catch (\Throwable $e) {
            @unlink($tmpFile);
            $this->error('Gagal membuat dump database: '.$e->getMessage());

            return self::FAILURE;
        }

        $size = filesize($tmpFile);
        $this->info('Dump selesai ('.number_format($size / 1024, 1).' KB).');

        $remote = sprintf(
            '%s/%s_%s.sql.gz',
            $folder,
            $db['database'],
            now()->format('Y-m-d_His')
        );

        $this->info("Mengunggah ke disk \"{$disk}\" -> {$remote} ...");

        try {
            $stream = fopen($tmpFile, 'r');

            if ($stream === false) {
                throw new \RuntimeException('Tidak dapat membuka file dump.');
            }

            Storage::disk($disk)->put($remote, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            $this->ensureFolderMarker($disk, $folder);
        } catch (\Throwable $e) {
            $this->error('Gagal mengunggah backup: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('keep-local') && ! config('backup.keep_local')) {
            @unlink($tmpFile);
        } else {
            $this->line("Salinan lokal disimpan di: {$tmpFile}");
        }

        $this->info('Backup database berhasil diunggah.');

        return self::SUCCESS;
    }

    /**
     * Buat objek penanda folder (nul-byte, diakhiri "/") pada disk R2/S3.
     * Cloudflare R2 merender objek yang key-nya berakhiran "/" sebagai folder,
     * jadi "12 oktober" akan tampil sebagai folder di dashboard.
     */
    protected function ensureFolderMarker(string $disk, string $folder): void
    {
        if (config("filesystems.disks.{$disk}.driver", 'local') === 'local') {
            return;
        }

        Storage::disk($disk)->put($folder.'/', '');
    }

    /**
     * Jalankan mysqldump dan kompres hasilnya ke file tujuan.
     */
    protected function dump(array $db, string $target): void
    {
        $process = new Process([
            config('backup.mysqldump'),
            '--host='.$db['host'],
            '--port='.(string) $db['port'],
            '--user='.$db['username'],
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--skip-comments',
            $db['database'],
        ], null, [
            'MYSQL_PWD' => $db['password'],
        ]);

        $process->setTimeout(3600);

        $handle = gzopen($target, 'wb9');

        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membuka file dump untuk ditulis.');
        }

        try {
            $process->run(function ($type, $buffer) use ($handle) {
                if ($type === Process::OUT) {
                    gzwrite($handle, $buffer);
                }
            });
        } finally {
            gzclose($handle);
        }

        if (! $process->isSuccessful()) {
            @unlink($target);

            throw new \RuntimeException(
                trim($process->getErrorOutput()) ?: 'mysqldump gagal dijalankan.'
            );
        }

        if (! is_file($target) || filesize($target) === 0) {
            throw new \RuntimeException('File dump kosong atau tidak terbentuk.');
        }
    }
}
