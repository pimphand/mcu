<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\McuController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportMultipleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\Participant;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login.index');
    Route::get('/peserta/token/{token}', [LoginController::class, 'pesertaToken'])->name('login.peserta.token');
    Route::post('/', [LoginController::class, 'login'])->name('login.post');
    Route::get('/logout', [LoginController::class, 'logout'])->name('login.out');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('welcome.index');

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    });

    Route::prefix('mcu-in-out')->group(function () {
        Route::get('/', [McuController::class, 'mcu'])->name('mcu.index');
        Route::post('/in', [McuController::class, 'mcuIn'])->name('mcu.in');
        Route::post('/out', [McuController::class, 'mcuOut'])->name('mcu.out');
    });


    /** route crud role */
    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('role.index');
        Route::get('/create', [RoleController::class, 'create'])->name('role.create');
        Route::post('/', [RoleController::class, 'store'])->name('role.store');
        Route::get('/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
        Route::put('/{id}', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
        Route::get('/permission/{roleID}', [RoleController::class, 'permission'])->name('role.permission');
        Route::post('/permission', [RoleController::class, 'permissionCreate'])->name('role.permission.store');
    });

    /** route crud user account */
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::get('/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/', [UserController::class, 'store'])->name('user.store');
        Route::get('/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('user.delete');
    });

    /** route crud client */
    Route::prefix('client')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('client.index');
        Route::get('/create', [ClientController::class, 'create'])->name('client.create');
        Route::post('/', [ClientController::class, 'store'])->name('client.store');
        Route::get('/{id}', [ClientController::class, 'edit'])->name('client.edit');
        Route::put('/{id}', [ClientController::class, 'update'])->name('client.update');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('client.delete');
    });

    /** route crud contract */
    Route::prefix('client/contract')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('contract.index');
        Route::get('/create', [ContractController::class, 'create'])->name('contract.create');
        Route::get('/select2', [ContractController::class, 'select2'])->name('contract.select2');
        Route::post('/', [ContractController::class, 'store'])->name('contract.store');
        Route::get('/{id}', [ContractController::class, 'edit'])->name('contract.edit');
        Route::put('/{id}', [ContractController::class, 'update'])->name('contract.update');
        Route::delete('/{id}', [ContractController::class, 'destroy'])->name('contract.delete');
    });

    /** route crud employee */
    Route::prefix('employee')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('/select2', [EmployeeController::class, 'select2'])->name('employee.select2');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('/', [EmployeeController::class, 'store'])->name('employee.store');
        Route::get('/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employee.delete');
    });

    /** route crud department */
    Route::prefix('department')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('department.index');
        Route::get('/select2', [DepartmentController::class, 'select2'])->name('department.select2');
        Route::get('/create', [DepartmentController::class, 'create'])->name('department.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('department.store');
        Route::get('/{id}', [DepartmentController::class, 'edit'])->name('department.edit');
        Route::put('/{id}', [DepartmentController::class, 'update'])->name('department.update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('department.delete');
    });

    /** route crud divisi */
    Route::prefix('divisi')->group(function () {
        Route::get('/', [DivisiController::class, 'index'])->name('divisi.index');
        Route::get('/select2', [DivisiController::class, 'select2'])->name('divisi.select2');
        Route::get('/create', [DivisiController::class, 'create'])->name('divisi.create');
        Route::post('/', [DivisiController::class, 'store'])->name('divisi.store');
        Route::get('/{id}', [DivisiController::class, 'edit'])->name('divisi.edit');
        Route::put('/{id}', [DivisiController::class, 'update'])->name('divisi.update');
        Route::delete('/{id}', [DivisiController::class, 'destroy'])->name('divisi.delete');
    });
    Route::middleware('mcu')->group(function () {

        Route::get('participant-upload-validasi-dokter', [\App\Http\Controllers\UploadFileController::class, 'getValidateDoctor'])->name('upload.validasi.dokter');
        Route::post('participant-upload-validasi-dokter', [\App\Http\Controllers\UploadFileController::class, 'validateDoctor']);
    });

    /** route crud participant */
    Route::prefix('participant')->middleware('mcu')->group(function () {
        Route::get('/', [ParticipantController::class, 'index'])->name('participant.index');
        Route::get('/create', [ParticipantController::class, 'create'])->name('participant.create');
        Route::get('/filter', [ParticipantController::class, 'filter'])->name('participant.filter');
        Route::post('/', [ParticipantController::class, 'store'])->name('participant.store');
        Route::get('/{id}', [ParticipantController::class, 'edit'])->name('participant.edit');
        Route::get('/detail/{id}', [ParticipantController::class, 'detail'])->name('participant.detail');
        Route::put('/{id}', [ParticipantController::class, 'update'])->name('participant.update');
        Route::delete('/{id}', [ParticipantController::class, 'destroy'])->name('participant.delete');
        Route::get('/register/{id}', [ParticipantController::class, 'updateRegister'])->name('participant.update.register');
        Route::get('/scan/{mcuId}', [ParticipantController::class, 'scan'])->name('participant.scan');

        Route::get('/detail/tanda-vital/{id}', [ParticipantController::class, 'detailTandaVital'])->name('participant.detail.tanda.vital');
        Route::put('/detail/tanda-vital/{id}', [ParticipantController::class, 'updateTandaVital'])->name('participant.detail.tanda.vital.update');

        Route::get('/detail/pemeriksaan-fisik/{id}', [ParticipantController::class, 'detailPemeriksaanFisik'])->name('participant.detail.pemeriksaan.fisik');
        Route::put('/detail/pemeriksaan-fisik/{id}', [ParticipantController::class, 'updatePemeriksaanFisik'])->name('participant.detail.pemeriksaan.fisik.update');

        Route::get('/detail/laboratorium/{id}', [ParticipantController::class, 'detailLaboratorium'])->name('participant.detail.laboratorium');
        Route::put('/detail/laboratorium/{id}', [ParticipantController::class, 'updateLaboratorium'])->name('participant.detail.laboratorium.update');

        Route::get('/detail/radiologi/{id}', [ParticipantController::class, 'detailRadiologi'])->name('participant.detail.radiologi');
        Route::put('/detail/radiologi/{id}', [ParticipantController::class, 'updateRadiologi'])->name('participant.detail.radiologi.update');

        Route::get('/detail/audiometri/{id}', [ParticipantController::class, 'detaiLaudiometri'])->name('participant.detail.audiometri');
        Route::put('/detail/audiometri/{id}', [ParticipantController::class, 'updateaudiometri'])->name('participant.detail.audiometri.update');

        Route::get('/detail/spirometri/{id}', [ParticipantController::class, 'detailSpirometri'])->name('participant.detail.spirometri');
        Route::put('/detail/spirometri/{id}', [ParticipantController::class, 'updateSpirometri'])->name('participant.detail.spirometri.update');

        Route::get('/detail/rectal/{id}', [ParticipantController::class, 'detailRectal'])->name('participant.detail.rectal');
        Route::put('/detail/rectal/{id}', [ParticipantController::class, 'updateRectal'])->name('participant.detail.rectal.update');

        Route::get('/detail/ekg/{id}', [ParticipantController::class, 'detailEkg'])->name('participant.detail.ekg');
        Route::put('/detail/ekg/{id}', [ParticipantController::class, 'updateEkg'])->name('participant.detail.ekg.update');

        Route::get('/detail/foto-kamera/{id}', [ParticipantController::class, 'detailFotoKamera'])->name('participant.detail.foto.kamera');
        Route::put('/detail/foto-kamera/{id}', [ParticipantController::class, 'updateFotoKamera'])->name('participant.detail.foto.kamera.update');

        Route::get('/detail/foto-komputer/{id}', [ParticipantController::class, 'detailFotoKomputer'])->name('participant.detail.foto.komputer');
        Route::post('/detail/foto-komputer/{id}', [ParticipantController::class, 'updateFotoKomputer'])->name('participant.detail.foto.komputer.update');


        //import
        Route::post('/import', [ParticipantController::class, 'import'])->name('participant.import');
    });

    Route::prefix('report')->group(function () {
        Route::prefix('pdf')->group(function () {
            Route::get('identitas/{participantId}', [ReportController::class, 'identitas'])->name('report.identitas');
            Route::get('tanda-vital/{participantId}', [ReportController::class, 'tandaVital'])->name('report.tanda.vital');
            Route::get('pemeriksaan-fisik/{participantId}', [ReportController::class, 'pemeriksaanFisik'])->name('report.pemeriksaan.fisik');
            Route::get('laboratorium/{participantId}', [ReportController::class, 'laboratorium'])->name('report.laboratorium');
            Route::get('laboratorium-lab-driver/{participantId}', [ReportController::class, 'laboratoriumLabDriver'])->name('report.laboratorium.lab.driver');
            Route::get('laboratorium-sgpt-ureum/{participantId}', [ReportController::class, 'laboratoriumSgptUreum'])->name('report.laboratorium.sgpt.ureum');
            Route::get('radiologi/{participantId}', [ReportController::class, 'radiologi'])->name('report.radiologi');
            Route::get('audiometri/{participantId}', [ReportController::class, 'audiometri'])->name('report.audiometri');
            Route::get('spirometri/{participantId}', [ReportController::class, 'spirometri'])->name('report.spirometri');
            Route::get('rectal/{participantId}', [ReportController::class, 'rectal'])->name('report.rectal');
            Route::get('ekg/{participantId}', [ReportController::class, 'ekg'])->name('report.ekg');
            Route::get('sticker-lab/{participantId}', [ReportController::class, 'stickerLab'])->name('report.sticker.lab');
            Route::get('sticker-5pcs/{participantId}', [ReportController::class, 'sticker5pcs'])->name('report.sticker.5pcs');
            Route::get('report-register/{id}', [ReportController::class, 'register'])->name('report.register');
            Route::get('report-resume/{id}', [ReportController::class, 'resume'])->name('report.resume');
        });
    });

    Route::middleware('mcu')->get('/participant-register', [ParticipantController::class, 'register'])->name('participant.register');
    Route::middleware('mcu')->get('/multiple-spirometri', [ReportMultipleController::class, 'spirometri'])->name('multiple.spirometri');
    Route::middleware('mcu')->get('/multiple-identitas', [ReportMultipleController::class, 'identitas'])->name('multiple.identitas');
    Route::middleware('mcu')->get('/multiple-ekg', [ReportMultipleController::class, 'ekg'])->name('multiple.ekg');
    Route::middleware('mcu')->get('/multiple-rectal', [ReportMultipleController::class, 'rectal'])->name('multiple.rectal');
    Route::middleware('mcu')->get('/multiple-radiologi', [ReportMultipleController::class, 'radiologi'])->name('multiple.radiologi');
    Route::middleware('mcu')->get('/multiple-pemeriksaan-fisik', [ReportMultipleController::class, 'pemFisik'])->name('multiple.pemFisik');

    Route::middleware('mcu')->get('/participant-print-mcu', action: [ParticipantController::class, 'printMCU'])->name('participant.print.mcu');

    // Route::prefix('pesera')->group(function(){
    //     Route::get('/', [ParticipantController::class,'peserta'])->name('peserta.index');
    // });

    Route::get('report-hasil', [RecapController::class, 'results'])->name('report.results');
});
