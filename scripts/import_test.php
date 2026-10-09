<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Imports\UsersImport;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

Session::put('client_id', 1);
Session::put('contract_id', 1);

DB::table('users')->insertOrIgnore(['id' => 1, 'name' => 'Tester', 'username' => 'tester', 'password' => 'x', 'is_active' => 1]);
DB::table('clients')->insertOrIgnore(['id' => 1, 'code' => 'CLI-1', 'name' => 'Test Client']);
DB::table('contracts')->insertOrIgnore(['id' => 1, 'code' => 'CTR-1', 'name' => 'Test Contract', 'client_id' => 1]);

$file = $argv[1] ?? base_path('DATA PESERTA MCU PT COSMO TECHNOLOGY 2026-OKT.xlsx');

Excel::import(new UsersImport(1, 1, 'a'), $file);

$total = Participant::count();
$withPlanName = Participant::whereNotNull('plan_name')->where('plan_name', '<>', '')->count();
$withFlag = Participant::where('plan_u', 1)->orWhere('plan_r', 1)->count();

echo "total participants : {$total}\n";
echo "rows with plan_name: {$withPlanName}\n";
echo "rows with plan_u/r : {$withFlag}\n\n";

echo "no_form | name | plan_name | u a e s r\n";
foreach (Participant::orderBy('no_form')->get() as $p) {
    if ($p->plan_name || $p->plan_u || $p->plan_r || in_array($p->no_form, [14, 66, 110, 301], true)) {
        printf(
            "%7s | %-24s | %-14s | %d %d %d %d %d\n",
            $p->no_form,
            mb_substr((string) $p->name, 0, 24),
            $p->plan_name,
            $p->plan_u,
            $p->plan_a,
            $p->plan_e,
            $p->plan_s,
            $p->plan_r
        );
    }
}
