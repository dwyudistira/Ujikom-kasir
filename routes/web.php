<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\ProdukController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {
    route::get('/admin/dashboard',[HomeController::class, 'admin'])->name('admin.dashboard');

    //pembelian
    

    //produk 
    route::get('/admin/produk',[ProdukController::class, 'index'])->name('admin.product');
    route::get('/admin/produk/create',[ProdukController::class, 'create'])->name('admin.product.create');
    route::post('/admin/produk/store',[ProdukController::class, 'store'])->name('admin.product.store');
    route::get('/admin/produk/edit',[ProdukController::class, 'edit'])->name('admin.product.edit');
    route::put('/admin/produk/update',[ProdukController::class, 'update'])->name('admin.product.update');
    route::put('/admin/produk/update/stock',[ProdukController::class, 'updateStock'])->name('admin.product.updateStock');
    route::delete('/admin/produk/delete',[ProdukController::class, 'destroy'])->name('admin.product.destroy');
    
    //user 
    route::get('/admin/user',[UserController::class, 'index'])->name('admin.user');
    route::get('/admin/user/create',[UserController::class, 'create'])->name('admin.user.create');
    route::post('/admin/user/store',[UserController::class, 'store'])->name('admin.user.store');
    route::get('/admin/user/edit/{id}',[UserController::class, 'edit'])->name('admin.user.edit');
    route::put('/admin/user/update/{id}',[UserController::class, 'update'])->name('admin.user.update');
    route::delete('/admin/user/delete/{id}',[UserController::class, 'destroy'])->name('admin.user.destroy');

});

Route::middleware(['auth', 'petugas '])->group(function () {
    route::get('/petugas/dashboard',[HomeController::class, 'petugas'])->name('petugas.dashboard');
});



require __DIR__.'/auth.php';
