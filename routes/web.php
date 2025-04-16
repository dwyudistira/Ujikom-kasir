<?php

use App\Http\Controllers\admin\PembelianController;
use App\Http\Controllers\admin\ProdukController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Petugas\PembelianController as PetugasPembelianController;
use App\Http\Controllers\Petugas\ProdukController as PetugasProdukController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;   

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {
    route::get('/admin/dashboard',[HomeController::class, 'admin'])->name('admin.dashboard');
    Route::get('/chart-data', [HomeController::class, 'getChartData']);

    //pembelian
     Route::get('/admin/pembelian', [PembelianController::class, "index"])->name("admin.pembelian");
     Route::get('/admin/pembelian-create', [PembelianController::class, "create"])->name("admin.pembelian.create");
     Route::post('/admin/pembelian-store', [PembelianController::class, "store"])->name("admin.pembelian.store");
     Route::get('/admin/pembelian-edit', [PembelianController::class, "edit"])->name("admin.pembelian.edit");
     Route::put('/admin/pembelian-update', [PembelianController::class, "update"])->name("admin.pembelian.update");
     Route::delete('/admin/pembelian-destroy', [PembelianController::class, "destroy"])->name("admin.pembelian.destroy");
     Route::get('/admin/export-pembelian', [PembelianController::class, 'export'])->name('admin.pembelian.export');
     Route::get('/export-pdf/{id}', [PembelianController::class, 'exportPdfId'])->name('admin.pembelian.export-pdf-id');
    //produk 
    route::get('/admin/produk',[ProdukController::class, 'index'])->name('admin.product');
    route::get('/admin/produk/create',[ProdukController::class, 'create'])->name('admin.product.create');
    route::post('/admin/produk/store',[ProdukController::class, 'store'])->name('admin.product.store');
    route::get('/admin/produk/edit/{id}',[ProdukController::class, 'edit'])->name('admin.product.edit');
    route::put('/admin/produk/update/{id}',[ProdukController::class, 'update'])->name('admin.product.update');
    route::put('/admin/produk/updateStock',[ProdukController::class, 'updateStock'])->name('admin.product.updateStock');
    route::delete('/admin/produk/delete/{id}',[ProdukController::class, 'destroy'])->name('admin.product.destroy');
    
    //user 
    route::get('/admin/user',[UserController::class, 'index'])->name('admin.user');
    route::get('/admin/user/create',[UserController::class, 'create'])->name('admin.user.create');
    route::post('/admin/user/store',[UserController::class, 'store'])->name('admin.user.store');
    route::get('/admin/user/edit/{id}',[UserController::class, 'edit'])->name('admin.user.edit');
    route::put('/admin/user/update/{id}',[UserController::class, 'update'])->name('admin.user.update');
    route::delete('/admin/user/delete/{id}',[UserController::class, 'destroy'])->name('admin.user.destroy');
});

Route::middleware(['auth', 'petugas'])->group(function () {
    //Dashboard
    Route::get('/petugas/dahboard', [HomeController::class, 'petugas'])->name('petugas.dashboard');

    //pembelian
    Route::get('/petugas/pembelian', [PetugasPembelianController::class, "index"])->name("petugas.pembelian");
    Route::get('/petugas/pembelian/create', [PetugasPembelianController::class, "create"])->name("petugas.pembelian.create");
    Route::post('/petugas/pembelian/detail-create', [PetugasPembelianController::class, "detail"])->name("petugas.pembelian.detail");
    
    //member
    Route::post('/petugas/pembelian/member', [PetugasPembelianController::class, 'storeMember'])->name('petugas.pembelian.member');
    Route::get('/petugas/pembelian/member', [PetugasPembelianController::class, 'memberPage'])->name('petugas.pembelian.memberPage');
    Route::get('/petugas/pembelian/receipt-member', [PetugasPembelianController::class, 'receiptMember'])->name('petugas.pembelian.receipt_member');
    Route::post('/pembelian/simpan-member', [PetugasPembelianController::class, 'simpanMember'])->name('petugas.pembelian.simpan_member');

    //non member
    Route::get('/petugas/pembelian/receipt', [PetugasPembelianController::class, 'receiptNonMember'])->name('petugas.pembelian.receipt');
    Route::post('/petugas/pembelian/receipt-store', [PetugasPembelianController::class, 'storeNonMember'])->name('petugas.pembelian.receipt_store');

    //Export 
    Route::get('/petugas/pembelian/export-pdf', [PetugasPembelianController::class, 'exportPdf'])->name('petugas.pembelian.export-pdf');
    Route::get('/export-pdf/{id}', [PetugasPembelianController::class, 'exportPdfId'])->name('petugas.pembelian.export-pdf-id');


    //product
    Route::get('/petugas/product', [PetugasProdukController::class, "index"])->name("petugas.product");
});

require __DIR__.'/auth.php';
