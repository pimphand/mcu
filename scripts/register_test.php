<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ParticipantController;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Session;

Session::put('client_id', 1);
Session::put('contract_id', 1);

function callController(string $date): array
{
    $request = Request::create('/participant/register-bulk/' . $date, 'GET');
    $route = new Route('GET', '/participant/register-bulk/{date}', []);
    $route->bind($request);
    $request->setRouteResolver(fn () => $route);

    $response = app(ParticipantController::class)->updateRegisters($request);

    return [$response->getStatusCode(), (array) $response->getData(true)];
}

printf("belum register : %d\n", Participant::whereNull('register_date')->count());

[$code, $body] = callController('2026-10-09');
printf("run #1  -> HTTP %d %s\n", $code, json_encode($body));
printf("register: %d / %d\n", Participant::whereNotNull('register_date')->count(), Participant::count());

$sample = Participant::where('no_form', 14)->first(['no_form', 'register_number', 'register_date']);
printf("no_form 14 -> register_number=%s register_date=%s\n", $sample->register_number, $sample->register_date);
printf("register_number != no_form : %d\n", Participant::whereNotNull('register_date')->whereRaw('register_number <> no_form')->count());

[$code2, $body2] = callController('2026-10-10');
printf("run #2  -> HTTP %d %s (harus 0 peserta, tanggal lama tidak berubah)\n", $code2, json_encode($body2));
printf("tanggal tetap  : %s\n", Participant::where('no_form', 14)->value('register_date'));

[$code3, $body3] = callController('bukan-tanggal');
printf("invalid -> HTTP %d %s\n", $code3, json_encode($body3));
