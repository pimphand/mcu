<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    |
    | Disk filesystem tempat hasil backup database disimpan. Default diarahkan
    | ke disk "r2" (Cloudflare R2) yang dikonfigurasi di config/filesystems.php.
    |
    */

    'disk' => env('BACKUP_DISK', 'r2'),

    /*
    |--------------------------------------------------------------------------
    | Backup Folder
    |--------------------------------------------------------------------------
    |
    | Folder (prefix) di dalam bucket tempat file backup disimpan.
    |
    */

    'folder' => env('BACKUP_FOLDER', '12 oktober'),

    /*
    |--------------------------------------------------------------------------
    | mysqldump Binary
    |--------------------------------------------------------------------------
    |
    | Lokasi binary mysqldump yang dipakai untuk mengekspor database.
    |
    */

    'mysqldump' => env('BACKUP_MYSQLDUMP', 'mysqldump'),

    /*
    |--------------------------------------------------------------------------
    | Keep Local Copy
    |--------------------------------------------------------------------------
    |
    | Bila true, salinan .sql.gz lokal tidak dihapus setelah berhasil diunggah.
    |
    */

    'keep_local' => env('BACKUP_KEEP_LOCAL', false),

];
