# Panduan Pengembangan Modul - Metinca Application

> Dokumentasi ini ditujukan untuk developer yang akan mengembangkan modul baru atau memodifikasi modul yang sudah ada di aplikasi Metinca.

## Daftar Isi

1. [Arsitektur Aplikasi](#1-arsitektur-aplikasi)
2. [Struktur Direktori Modul](#2-struktur-direktori-modul)
3. [Membuat Modul Baru](#3-membuat-modul-baru)
4. [Membuat Migration](#4-membuat-migration)
5. [Membuat Model](#5-membuat-model)
6. [Membuat Repository](#6-membuat-repository)
7. [Membuat Service](#7-membuat-service)
8. [Membuat Controller](#8-membuat-controller)
9. [Membuat Routes](#9-membuat-routes)
10. [Membuat Views](#10-membuat-views)
11. [Mengatur Permissions (RBAC)](#11-mengatur-permissions-rbac)
12. [Mendaftarkan Modul](#12-mendaftarkan-modul)
13. [Testing Modul](#13-testing-modul)
14. [Best Practices](#14-best-practices)
15. [Contoh Lengkap: Membuat Modul Inventory](#15-contoh-lengkap-membuat-modul-inventory)

---

## 1. Arsitektur Aplikasi

Aplikasi Metinca menggunakan arsitektur **Modular Monolith** dengan design pattern:

- **Repository Pattern**: Abstraksi akses database
- **Service Pattern**: Business logic terpisah dari controller
- **RBAC (Role-Based Access Control)**: Menggunakan Spatie Laravel Permission

### Alur Request

```
Request → Route → Middleware → Controller → Service → Repository → Model → Database
                                    ↓
                                  View
```

### Keuntungan Arsitektur Ini

1. **Separation of Concerns**: Setiap layer punya tanggung jawab masing-masing
2. **Testable**: Mudah di-unit test karena dependency injection
3. **Maintainable**: Kode terorganisir dan mudah dipahami
4. **Scalable**: Mudah ditambah fitur baru tanpa mengubah kode existing

---

## 2. Struktur Direktori Modul

```
app/Modules/
├── Core/                           # Modul inti (base classes)
│   ├── Contracts/
│   │   └── RepositoryInterface.php
│   └── Repositories/
│       └── BaseRepository.php
│
└── NamaModul/                      # Contoh: Repair, Inventory, HR
    ├── Controllers/
    │   ├── DashboardController.php
    │   └── [Resource]Controller.php
    ├── Models/
    │   └── [NamaModel].php
    ├── Repositories/
    │   ├── Contracts/
    │   │   └── [NamaModel]RepositoryInterface.php
    │   └── [NamaModel]Repository.php
    ├── Services/
    │   ├── Contracts/
    │   │   └── [NamaModel]ServiceInterface.php
    │   └── [NamaModel]Service.php
    ├── Requests/
    │   └── [Store/Update][NamaModel]Request.php
    ├── Exports/
    │   └── [NamaModel]Export.php
    ├── Notifications/
    │   └── [Nama]Notification.php
    ├── Console/
    │   └── Commands/
    │       └── [Nama]Command.php
    ├── Resources/
    │   └── views/
    │       ├── layouts/
    │       │   └── module.blade.php
    │       ├── dashboard.blade.php
    │       └── [resource]/
    │           ├── index.blade.php
    │           ├── create.blade.php
    │           └── show.blade.php
    ├── Routes/
    │   └── web.php
    └── Providers/
        └── [NamaModul]ServiceProvider.php
```

---

## 3. Membuat Modul Baru

### Langkah 1: Buat Struktur Direktori

```bash
# Ganti "Inventory" dengan nama modul kamu
mkdir -p app/Modules/Inventory/{Controllers,Models,Repositories/Contracts,Services/Contracts,Requests,Resources/views/layouts,Routes,Providers}
```

### Langkah 2: Buat Service Provider

File: `app/Modules/Inventory/Providers/InventoryServiceProvider.php`

```php
<?php

namespace App\Modules\Inventory\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Interface ke Implementation
        $this->app->bind(
            \App\Modules\Inventory\Repositories\Contracts\ProductRepositoryInterface::class,
            \App\Modules\Inventory\Repositories\ProductRepository::class
        );

        // Bind Service Interface ke Implementation
        $this->app->bind(
            \App\Modules\Inventory\Services\Contracts\ProductServiceInterface::class,
            \App\Modules\Inventory\Services\ProductService::class
        );
    }

    public function boot(): void
    {
        // Register Routes
        $this->registerRoutes();

        // Register Views dengan namespace 'inventory'
        $this->loadViewsFrom(
            __DIR__ . '/../Resources/views',
            'inventory'
        );

        // Register Commands (jika ada)
        if ($this->app->runningInConsole()) {
            $this->commands([
                // \App\Modules\Inventory\Console\Commands\SyncStockCommand::class,
            ]);
        }
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'module:inventory'])
            ->prefix('inventory')
            ->name('inventory.')
            ->group(__DIR__ . '/../Routes/web.php');
    }
}
```

---

## 4. Membuat Migration

### Langkah 1: Generate Migration

```bash
php artisan make:migration create_products_table
```

### Langkah 2: Edit Migration

File: `database/migrations/xxxx_xx_xx_create_products_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Kode unik produk
            $table->string('code', 50)->unique();

            // Informasi produk
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit', 20)->default('pcs'); // pcs, kg, meter, dll

            // Harga dan stok
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0); // minimum stok sebelum warning

            // Status
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');

            // Foreign keys (jika ada relasi)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // Untuk soft delete

            // Index untuk pencarian cepat
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### Langkah 3: Jalankan Migration

```bash
php artisan migrate
```

### Tips Migration

```php
// Menambah kolom ke tabel existing
Schema::table('products', function (Blueprint $table) {
    $table->string('sku')->nullable()->after('code');
});

// Mengubah tipe kolom (perlu doctrine/dbal)
Schema::table('products', function (Blueprint $table) {
    $table->text('description')->change();
});

// Menghapus kolom
Schema::table('products', function (Blueprint $table) {
    $table->dropColumn('sku');
});
```

---

## 5. Membuat Model

File: `app/Modules/Inventory/Models/Product.php`

```php
<?php

namespace App\Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    // =========================================
    // CONSTANTS
    // =========================================

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DISCONTINUED = 'discontinued';

    // =========================================
    // FILLABLE & CASTS
    // =========================================

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'unit',
        'price',
        'stock',
        'min_stock',
        'status',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // =========================================
    // SCOPES (untuk query yang sering dipakai)
    // =========================================

    /**
     * Scope untuk filter berdasarkan berbagai parameter
     */
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['category'] ?? null, fn($q, $category) =>
                $q->where('category', $category)
            )
            ->when($filters['search'] ?? null, fn($q, $search) =>
                $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                })
            )
            ->when($filters['low_stock'] ?? false, fn($q) =>
                $q->whereColumn('stock', '<=', 'min_stock')
            );
    }

    /**
     * Scope untuk produk aktif saja
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // =========================================
    // ACCESSORS & MUTATORS
    // =========================================

    /**
     * Generate kode produk otomatis
     */
    public static function generateCode(string $category = 'GEN'): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    /**
     * Cek apakah stok rendah
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Format harga dengan Rupiah
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Badge class untuk status
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-success',
            self::STATUS_INACTIVE => 'bg-warning',
            self::STATUS_DISCONTINUED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
```

---

## 6. Membuat Repository

Repository adalah layer abstraksi untuk akses database. Keuntungannya:
- Memisahkan logic query dari business logic
- Mudah di-mock untuk testing
- Konsisten dalam cara akses data

### Langkah 1: Buat Interface

File: `app/Modules/Inventory/Repositories/Contracts/ProductRepositoryInterface.php`

```php
<?php

namespace App\Modules\Inventory\Repositories\Contracts;

use App\Models\User;
use App\Modules\Core\Contracts\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Get products dengan filter dan pagination
     */
    public function getFiltered(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find product by code
     */
    public function findByCode(string $code): ?object;

    /**
     * Get products dengan stok rendah
     */
    public function getLowStockProducts(): Collection;

    /**
     * Get products by category
     */
    public function getByCategory(string $category): Collection;

    /**
     * Update stok produk
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): bool;
}
```

### Langkah 2: Buat Implementation

File: `app/Modules/Inventory/Repositories/ProductRepository.php`

```php
<?php

namespace App\Modules\Inventory\Repositories;

use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Get products dengan filter dan pagination
     */
    public function getFiltered(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['creator']) // Eager load relationships
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString(); // Pertahankan query string di pagination
    }

    /**
     * Find product by code
     */
    public function findByCode(string $code): ?object
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Get products dengan stok rendah
     */
    public function getLowStockProducts(): Collection
    {
        return $this->model
            ->active()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
    }

    /**
     * Get products by category
     */
    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->where('category', $category)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Update stok produk
     */
    public function updateStock(int $id, int $quantity, string $operation = 'add'): bool
    {
        $product = $this->find($id);

        if (!$product) {
            return false;
        }

        if ($operation === 'add') {
            $product->stock += $quantity;
        } elseif ($operation === 'subtract') {
            $product->stock = max(0, $product->stock - $quantity);
        } else {
            $product->stock = $quantity; // set langsung
        }

        return $product->save();
    }
}
```

---

## 7. Membuat Service

Service adalah tempat business logic. Controller hanya menerima request dan memanggil service.

### Langkah 1: Buat Interface

File: `app/Modules/Inventory/Services/Contracts/ProductServiceInterface.php`

```php
<?php

namespace App\Modules\Inventory\Services\Contracts;

use App\Models\User;
use App\Modules\Inventory\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    /**
     * Get filtered products
     */
    public function getFilteredProducts(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create new product
     */
    public function createProduct(array $data, User $creator): Product;

    /**
     * Update existing product
     */
    public function updateProduct(Product $product, array $data): Product;

    /**
     * Delete product
     */
    public function deleteProduct(Product $product): bool;

    /**
     * Adjust stock (tambah/kurang)
     */
    public function adjustStock(Product $product, int $quantity, string $type, User $actor, ?string $notes = null): Product;

    /**
     * Get low stock alert
     */
    public function getLowStockAlert(): array;
}
```

### Langkah 2: Buat Implementation

File: `app/Modules/Inventory/Services/ProductService.php`

```php
<?php

namespace App\Modules\Inventory\Services;

use App\Models\User;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Repositories\Contracts\ProductRepositoryInterface;
use App\Modules\Inventory\Services\Contracts\ProductServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $repository
    ) {}

    /**
     * Get filtered products
     */
    public function getFilteredProducts(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters, $perPage);
    }

    /**
     * Create new product
     */
    public function createProduct(array $data, User $creator): Product
    {
        return DB::transaction(function () use ($data, $creator) {
            // Generate kode jika tidak ada
            if (empty($data['code'])) {
                $data['code'] = Product::generateCode($data['category'] ?? 'GEN');
            }

            $data['created_by'] = $creator->id;

            $product = $this->repository->create($data);

            // Log aktivitas (opsional)
            activity()
                ->performedOn($product)
                ->causedBy($creator)
                ->log('Created product: ' . $product->name);

            return $product;
        });
    }

    /**
     * Update existing product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $this->repository->update($product->id, $data);

            return $product->fresh();
        });
    }

    /**
     * Delete product (soft delete)
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->repository->delete($product->id);
    }

    /**
     * Adjust stock (tambah/kurang)
     *
     * @param string $type 'in' untuk masuk, 'out' untuk keluar
     */
    public function adjustStock(
        Product $product,
        int $quantity,
        string $type,
        User $actor,
        ?string $notes = null
    ): Product {
        return DB::transaction(function () use ($product, $quantity, $type, $actor, $notes) {
            $beforeStock = $product->stock;

            // Update stok
            if ($type === 'in') {
                $product->stock += $quantity;
            } else {
                // Validasi stok cukup
                if ($product->stock < $quantity) {
                    throw new \InvalidArgumentException('Stok tidak mencukupi');
                }
                $product->stock -= $quantity;
            }

            $product->save();

            // Catat movement
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'before_stock' => $beforeStock,
                'after_stock' => $product->stock,
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            return $product;
        });
    }

    /**
     * Get low stock alert
     */
    public function getLowStockAlert(): array
    {
        $lowStockProducts = $this->repository->getLowStockProducts();

        return [
            'count' => $lowStockProducts->count(),
            'products' => $lowStockProducts,
        ];
    }
}
```

---

## 8. Membuat Controller

Controller harus **tipis** (thin controller). Hanya menerima request, validasi, panggil service, dan return response.

File: `app/Modules/Inventory/Controllers/ProductController.php`

```php
<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Requests\StoreProductRequest;
use App\Modules\Inventory\Requests\UpdateProductRequest;
use App\Modules\Inventory\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductServiceInterface $productService
    ) {}

    /**
     * Display a listing of products
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'category', 'search', 'low_stock']);
        $products = $this->productService->getFilteredProducts($filters);

        // Get unique categories untuk filter dropdown
        $categories = Product::distinct()->pluck('category')->filter();

        return view('inventory::products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): View
    {
        return view('inventory::products.create');
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->createProduct(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('inventory.products.show', $product)
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): View
    {
        $product->load(['creator', 'stockMovements.creator']);

        return view('inventory::products.show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the product
     */
    public function edit(Product $product): View
    {
        return view('inventory::products.edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()
            ->route('inventory.products.show', $product)
            ->with('status', 'Produk berhasil diupdate.');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product);

        return redirect()
            ->route('inventory.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    /**
     * Adjust stock
     */
    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->productService->adjustStock(
                $product,
                $validated['quantity'],
                $validated['type'],
                $request->user(),
                $validated['notes'] ?? null
            );

            return back()->with('status', 'Stok berhasil diupdate.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }
    }
}
```

### Membuat Form Request

File: `app/Modules/Inventory/Requests/StoreProductRequest.php`

```php
<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inventory.create');
    }

    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,discontinued',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'unit.required' => 'Satuan wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok awal wajib diisi.',
            'code.unique' => 'Kode produk sudah digunakan.',
        ];
    }
}
```

---

## 9. Membuat Routes

File: `app/Modules/Inventory/Routes/web.php`

```php
<?php

use App\Modules\Inventory\Controllers\DashboardController;
use App\Modules\Inventory\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inventory Module Routes
|--------------------------------------------------------------------------
|
| Semua route di sini sudah memiliki:
| - Prefix: /inventory
| - Name prefix: inventory.
| - Middleware: web, auth, module:inventory
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Products CRUD
Route::prefix('products')->name('products.')->group(function () {

    // List semua produk
    Route::get('/', [ProductController::class, 'index'])
        ->name('index')
        ->middleware('permission:inventory.view');

    // Form tambah produk
    Route::get('/create', [ProductController::class, 'create'])
        ->name('create')
        ->middleware('permission:inventory.create');

    // Simpan produk baru
    Route::post('/', [ProductController::class, 'store'])
        ->name('store')
        ->middleware('permission:inventory.create');

    // Export ke Excel
    Route::get('/export', [ProductController::class, 'export'])
        ->name('export')
        ->middleware('permission:inventory.export');

    // Detail produk
    Route::get('/{product}', [ProductController::class, 'show'])
        ->name('show')
        ->middleware('permission:inventory.view');

    // Form edit produk
    Route::get('/{product}/edit', [ProductController::class, 'edit'])
        ->name('edit')
        ->middleware('permission:inventory.update');

    // Update produk
    Route::put('/{product}', [ProductController::class, 'update'])
        ->name('update')
        ->middleware('permission:inventory.update');

    // Hapus produk
    Route::delete('/{product}', [ProductController::class, 'destroy'])
        ->name('destroy')
        ->middleware('permission:inventory.delete');

    // Adjust stok
    Route::post('/{product}/adjust-stock', [ProductController::class, 'adjustStock'])
        ->name('adjust-stock')
        ->middleware('permission:inventory.adjust-stock');
});

// Stock Movements (opsional, untuk history)
Route::prefix('movements')->name('movements.')->group(function () {
    Route::get('/', [StockMovementController::class, 'index'])
        ->name('index')
        ->middleware('permission:inventory.view');
});
```

---

## 10. Membuat Views

### Layout Modul

File: `app/Modules/Inventory/Resources/views/layouts/module.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Inventory Module</title>
    <link rel="shortcut icon" href="{{ asset('assets/compiled/svg/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/iconly.css') }}">
    @stack('styles')
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="app">
        <div id="sidebar">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="logo">
                            <a href="{{ route('inventory.dashboard') }}">
                                <i class="bi bi-box-seam fs-3 text-primary"></i>
                                <span class="ms-2 fw-bold">Inventory</span>
                            </a>
                        </div>
                        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                            <div class="form-check form-switch fs-6">
                                <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                            </div>
                        </div>
                        <div class="sidebar-toggler x">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-title">Inventory Module</li>

                        {{-- Dashboard --}}
                        <li class="sidebar-item {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('inventory.dashboard') }}" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        {{-- Products --}}
                        <li class="sidebar-item has-sub {{ request()->routeIs('inventory.products.*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-box"></i>
                                <span>Products</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item {{ request()->routeIs('inventory.products.index') ? 'active' : '' }}">
                                    <a href="{{ route('inventory.products.index') }}" class="submenu-link">All Products</a>
                                </li>
                                @can('inventory.create')
                                <li class="submenu-item {{ request()->routeIs('inventory.products.create') ? 'active' : '' }}">
                                    <a href="{{ route('inventory.products.create') }}" class="submenu-link">Add New</a>
                                </li>
                                @endcan
                            </ul>
                        </li>

                        {{-- Stock Movements --}}
                        @can('inventory.view')
                        <li class="sidebar-item {{ request()->routeIs('inventory.movements.*') ? 'active' : '' }}">
                            <a href="{{ route('inventory.movements.index') }}" class='sidebar-link'>
                                <i class="bi bi-arrow-left-right"></i>
                                <span>Stock Movements</span>
                            </a>
                        </li>
                        @endcan

                        <li class="sidebar-title">Actions</li>

                        {{-- Back to Main Dashboard --}}
                        <li class="sidebar-item">
                            <a href="{{ route('dashboard') }}" class='sidebar-link'>
                                <i class="bi bi-arrow-left-circle"></i>
                                <span>Back to Main</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="main">
            <header>
                <nav class="navbar navbar-expand navbar-light navbar-top">
                    <div class="container-fluid">
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3"></i>
                        </a>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-auto mb-lg-0">
                                {{-- Notifications --}}
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link active dropdown-toggle text-gray-600" href="#"
                                        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                        <i class="bi bi-bell bi-sub fs-4"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                                        <li class="dropdown-header">
                                            <h6>Notifications</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#">No notifications</a></li>
                                    </ul>
                                </li>
                            </ul>

                            {{-- User Menu --}}
                            <div class="dropdown">
                                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="user-menu d-flex">
                                        <div class="user-name text-end me-3">
                                            <h6 class="mb-0 text-gray-600">{{ auth()->user()->name }}</h6>
                                            <p class="mb-0 text-sm text-gray-600">{{ auth()->user()->getModuleRole('inventory') ?? 'User' }}</p>
                                        </div>
                                        <div class="user-img d-flex align-items-center">
                                            <div class="avatar avatar-md bg-primary text-white d-flex align-items-center justify-content-center">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 11rem">
                                    <li>
                                        <h6 class="dropdown-header">Hello, {{ auth()->user()->name }}!</h6>
                                    </li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li>
                                        <form id="formLogout">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <div class="main-content">
                {{-- Flash Messages --}}
                @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>{{ date('Y') }} &copy; Metinca Inventory Module</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/compiled/js/app.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Logout handler
        document.getElementById('formLogout').addEventListener('submit', function(e){
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Confirm Logout',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    App.loading('Logging out...');
                    App.ajax('{{ route('logout') }}', 'POST', new FormData(form)).then(response => {
                        App.closeLoading();
                        window.location.href = '{{ route('login') }}';
                    }).catch(error => {
                        App.closeLoading();
                        window.location.href = '{{ route('login') }}';
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
```

### View Index (List)

File: `app/Modules/Inventory/Resources/views/products/index.blade.php`

```blade
@extends('inventory::layouts.module')

@section('title', 'Products')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Products</h3>
                <p class="text-subtitle text-muted">Manage your inventory products</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Product List</h4>
                <div>
                    @can('inventory.export')
                    <a href="{{ route('inventory.products.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </a>
                    @endcan
                    @can('inventory.create')
                    <a href="{{ route('inventory.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search name or code..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category }}" {{ ($filters['category'] ?? '') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="discontinued" {{ ($filters['status'] ?? '') === 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="lowStock" {{ !empty($filters['low_stock']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lowStock">Low Stock Only</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td><code>{{ $product->code }}</code></td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category ?? '-' }}</td>
                                <td>{{ $product->formatted_price }}</td>
                                <td>
                                    <span class="{{ $product->isLowStock() ? 'text-danger fw-bold' : '' }}">
                                        {{ $product->stock }} {{ $product->unit }}
                                    </span>
                                    @if($product->isLowStock())
                                    <i class="bi bi-exclamation-triangle text-danger" title="Low stock!"></i>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $product->statusBadgeClass() }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('inventory.products.show', $product) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('inventory.update')
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No products found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
```

### View Create (Form)

File: `app/Modules/Inventory/Resources/views/products/create.blade.php`

```blade
@extends('inventory::layouts.module')

@section('title', 'Add Product')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Add New Product</h3>
                <p class="text-subtitle text-muted">Create a new product in inventory</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.products.index') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Product Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.products.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="code" class="form-label">Product Code</label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}" placeholder="Auto-generate if empty">
                                <small class="text-muted">Leave empty to auto-generate</small>
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category') }}" list="categoryList">
                                <datalist id="categoryList">
                                    <option value="Electronics">
                                    <option value="Mechanical">
                                    <option value="Raw Materials">
                                    <option value="Spare Parts">
                                </datalist>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="pcs" {{ old('unit') === 'pcs' ? 'selected' : '' }}>Pcs (Pieces)</option>
                                    <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                                    <option value="meter" {{ old('unit') === 'meter' ? 'selected' : '' }}>Meter</option>
                                    <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="box" {{ old('unit') === 'box' ? 'selected' : '' }}>Box</option>
                                </select>
                                @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="price" class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', 0) }}" min="0" step="0.01" required>
                                @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="stock" class="form-label">Initial Stock <span class="text-danger">*</span></label>
                                <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror"
                                    value="{{ old('stock', 0) }}" min="0" required>
                                @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="min_stock" class="form-label">Minimum Stock</label>
                                <input type="number" name="min_stock" id="min_stock" class="form-control @error('min_stock') is-invalid @enderror"
                                    value="{{ old('min_stock', 0) }}" min="0">
                                <small class="text-muted">Alert when stock below this</small>
                                @error('min_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
```

---

## 11. Mengatur Permissions (RBAC)

### Struktur Permission

Format: `[module].[action]`

Contoh untuk modul Inventory:
- `inventory.view` - Lihat data
- `inventory.create` - Tambah data baru
- `inventory.update` - Edit data
- `inventory.delete` - Hapus data
- `inventory.export` - Export data
- `inventory.adjust-stock` - Adjust stok

### Membuat Seeder

File: `database/seeders/InventoryPermissionsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InventoryPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Inventory Module Permissions
        $permissions = [
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.export',
            'inventory.adjust-stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Inventory Module Roles

        // Staff - hanya view
        $staff = Role::firstOrCreate(['name' => 'inventory.staff', 'guard_name' => 'web']);
        $staff->syncPermissions(['inventory.view']);

        // Operator - view, create, adjust stock
        $operator = Role::firstOrCreate(['name' => 'inventory.operator', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'inventory.view',
            'inventory.create',
            'inventory.adjust-stock',
        ]);

        // Supervisor - semua kecuali delete
        $supervisor = Role::firstOrCreate(['name' => 'inventory.supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.export',
            'inventory.adjust-stock',
        ]);

        // Manager - full access
        $manager = Role::firstOrCreate(['name' => 'inventory.manager', 'guard_name' => 'web']);
        $manager->syncPermissions($permissions);

        $this->command->info('Inventory permissions and roles created!');
    }
}
```

### Jalankan Seeder

```bash
php artisan db:seed --class=InventoryPermissionsSeeder
```

### Assign Role ke User

```php
// Di tinker atau seeder
$user = User::find(1);
$user->assignRole('inventory.operator');

// Atau multiple roles
$user->assignRole(['inventory.operator', 'repair.technician']);

// Cek role
$user->hasRole('inventory.operator'); // true/false

// Cek permission
$user->can('inventory.create'); // true/false
```

### Menggunakan Permission di Code

```php
// Di Controller
public function create()
{
    $this->authorize('inventory.create'); // Throws 403 if not allowed
    // atau
    if (!auth()->user()->can('inventory.create')) {
        abort(403);
    }
}

// Di Blade
@can('inventory.create')
    <a href="{{ route('inventory.products.create') }}" class="btn btn-primary">Add New</a>
@endcan

// Di Route
Route::get('/create', [ProductController::class, 'create'])
    ->middleware('permission:inventory.create');
```

---

## 12. Mendaftarkan Modul

### Langkah 1: Tambah ke Config

File: `config/modules.php`

```php
<?php

return [
    'modules' => [
        'repair' => [
            'provider' => \App\Modules\Repair\Providers\RepairServiceProvider::class,
            'enabled' => true,
            'display_name' => 'Repair',
            'category' => 'maintenance',
            'icon' => 'bi-wrench-adjustable',
            'sidebar' => [
                ['label' => 'Dashboard', 'route' => 'repair.dashboard', 'icon' => 'bi-grid-fill'],
                ['label' => 'Reports', 'route' => 'repair.reports.index', 'icon' => 'bi-file-earmark-text'],
            ],
        ],

        // TAMBAHKAN MODUL BARU DI SINI
        'inventory' => [
            'provider' => \App\Modules\Inventory\Providers\InventoryServiceProvider::class,
            'enabled' => true,
            'display_name' => 'Inventory',
            'category' => 'production',
            'icon' => 'bi-box-seam',
            'sidebar' => [
                ['label' => 'Dashboard', 'route' => 'inventory.dashboard', 'icon' => 'bi-grid-fill'],
                ['label' => 'Products', 'route' => 'inventory.products.index', 'icon' => 'bi-box'],
                ['label' => 'Stock Movements', 'route' => 'inventory.movements.index', 'icon' => 'bi-arrow-left-right'],
            ],
        ],
    ],

    'categories' => [
        'maintenance' => ['label' => 'Maintenance', 'color' => 'primary'],
        'production' => ['label' => 'Production', 'color' => 'success'],
        'hr' => ['label' => 'Human Resources', 'color' => 'info'],
    ],
];
```

### Langkah 2: Register Service Provider

File: `app/Providers/ModuleServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modules = config('modules.modules', []);

        foreach ($modules as $key => $config) {
            if ($config['enabled'] && class_exists($config['provider'])) {
                $this->app->register($config['provider']);
            }
        }
    }

    public function boot(): void
    {
        //
    }
}
```

### Langkah 3: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 13. Testing Modul

### Manual Testing Checklist

```markdown
## Checklist Testing Modul [Nama Modul]

### Authentication & Authorization
- [ ] User tanpa akses tidak bisa masuk modul
- [ ] User dengan role staff hanya bisa view
- [ ] User dengan role operator bisa create
- [ ] User dengan role supervisor bisa update
- [ ] User dengan role manager bisa delete
- [ ] Super admin bisa akses semua

### CRUD Operations
- [ ] List data tampil dengan benar
- [ ] Filter berfungsi
- [ ] Pagination berfungsi
- [ ] Create data berhasil
- [ ] Validasi form berfungsi
- [ ] Edit data berhasil
- [ ] Delete data berhasil (soft delete)
- [ ] Export Excel berhasil

### UI/UX
- [ ] Sidebar aktif sesuai halaman
- [ ] Breadcrumb benar
- [ ] Flash message tampil
- [ ] Error message tampil
- [ ] Responsive di mobile
- [ ] Dark mode berfungsi
```

### Testing dengan Tinker

```bash
php artisan tinker
```

```php
// Test repository
$repo = app(\App\Modules\Inventory\Repositories\Contracts\ProductRepositoryInterface::class);
$repo->getFiltered(['status' => 'active'], 10);

// Test service
$service = app(\App\Modules\Inventory\Services\Contracts\ProductServiceInterface::class);
$user = \App\Models\User::first();
$service->createProduct([
    'name' => 'Test Product',
    'unit' => 'pcs',
    'price' => 10000,
    'stock' => 100,
], $user);

// Test permissions
$user = \App\Models\User::where('email', 'operator@test.com')->first();
$user->can('inventory.create'); // true
$user->can('inventory.delete'); // false
```

---

## 14. Best Practices

### DO (Lakukan)

1. **Gunakan Type Hints**
   ```php
   public function createProduct(array $data, User $creator): Product
   ```

2. **Gunakan Dependency Injection**
   ```php
   public function __construct(
       protected ProductRepositoryInterface $repository
   ) {}
   ```

3. **Gunakan DB Transaction untuk operasi multiple**
   ```php
   return DB::transaction(function () use ($data) {
       // multiple database operations
   });
   ```

4. **Validasi di Form Request, bukan di Controller**
   ```php
   public function store(StoreProductRequest $request)
   ```

5. **Gunakan Constants untuk nilai tetap**
   ```php
   public const STATUS_ACTIVE = 'active';
   ```

6. **Eager Load relationships untuk avoid N+1**
   ```php
   Product::with(['creator', 'category'])->get();
   ```

7. **Gunakan Scope untuk query yang sering dipakai**
   ```php
   $query->active()->filter($filters);
   ```

### DON'T (Hindari)

1. **Jangan taruh business logic di Controller**
   ```php
   // BAD
   public function store(Request $request)
   {
       $product = new Product();
       $product->code = 'PRD-' . time(); // Business logic!
       // ...
   }

   // GOOD - pindahkan ke Service
   public function store(StoreProductRequest $request)
   {
       $product = $this->productService->createProduct($request->validated(), $request->user());
   }
   ```

2. **Jangan gunakan Query langsung di Controller**
   ```php
   // BAD
   $products = Product::where('status', 'active')->get();

   // GOOD - gunakan Repository
   $products = $this->repository->getFiltered(['status' => 'active']);
   ```

3. **Jangan hardcode values**
   ```php
   // BAD
   if ($product->status === 'active')

   // GOOD
   if ($product->status === Product::STATUS_ACTIVE)
   ```

4. **Jangan skip validation**
   ```php
   // BAD
   $data = $request->all();

   // GOOD
   $data = $request->validated();
   ```

5. **Jangan lupa handle errors**
   ```php
   try {
       $this->service->adjustStock(...);
   } catch (\InvalidArgumentException $e) {
       return back()->withErrors(['quantity' => $e->getMessage()]);
   }
   ```

---

## 15. Contoh Lengkap: Membuat Modul Inventory

Berikut step-by-step membuat modul Inventory dari awal:

### Step 1: Buat Struktur Direktori

```bash
mkdir -p app/Modules/Inventory/{Controllers,Models,Repositories/Contracts,Services/Contracts,Requests,Resources/views/{layouts,products},Routes,Providers,Exports}
```

### Step 2: Buat Migration

```bash
php artisan make:migration create_products_table
php artisan make:migration create_stock_movements_table
```

Edit migration sesuai kebutuhan, lalu:

```bash
php artisan migrate
```

### Step 3: Buat Model

- `app/Modules/Inventory/Models/Product.php`
- `app/Modules/Inventory/Models/StockMovement.php`

### Step 4: Buat Repository Interface & Implementation

- `app/Modules/Inventory/Repositories/Contracts/ProductRepositoryInterface.php`
- `app/Modules/Inventory/Repositories/ProductRepository.php`

### Step 5: Buat Service Interface & Implementation

- `app/Modules/Inventory/Services/Contracts/ProductServiceInterface.php`
- `app/Modules/Inventory/Services/ProductService.php`

### Step 6: Buat Controller

- `app/Modules/Inventory/Controllers/DashboardController.php`
- `app/Modules/Inventory/Controllers/ProductController.php`

### Step 7: Buat Form Requests

- `app/Modules/Inventory/Requests/StoreProductRequest.php`
- `app/Modules/Inventory/Requests/UpdateProductRequest.php`

### Step 8: Buat Routes

- `app/Modules/Inventory/Routes/web.php`

### Step 9: Buat Views

- `app/Modules/Inventory/Resources/views/layouts/module.blade.php`
- `app/Modules/Inventory/Resources/views/dashboard.blade.php`
- `app/Modules/Inventory/Resources/views/products/index.blade.php`
- `app/Modules/Inventory/Resources/views/products/create.blade.php`
- `app/Modules/Inventory/Resources/views/products/show.blade.php`
- `app/Modules/Inventory/Resources/views/products/edit.blade.php`

### Step 10: Buat Service Provider

- `app/Modules/Inventory/Providers/InventoryServiceProvider.php`

### Step 11: Daftarkan Modul

1. Tambah ke `config/modules.php`
2. Buat seeder permissions
3. Jalankan seeder

### Step 12: Clear Cache & Test

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan route:list --path=inventory
```

---

## Referensi

- [Laravel Documentation](https://laravel.com/docs)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Repository Pattern in Laravel](https://asperbrothers.com/blog/implement-repository-pattern-in-laravel/)

---

## Bantuan

Jika ada pertanyaan atau kendala, silakan hubungi:
- Lead Developer: [nama@email.com]
- Dokumentasi Internal: [link ke confluence/notion]

---

*Dokumentasi ini terakhir diupdate: {{ date('d F Y') }}*
