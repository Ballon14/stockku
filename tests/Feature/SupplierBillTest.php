<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupplierBillTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->kasir = User::factory()->create();
        $this->kasir->assignRole('kasir');

        $category = Category::create([
            'name' => 'Skincare',
            'slug' => 'skincare',
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-TEST-01',
            'name' => 'CV Sumber Rejeki',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Facial Wash 100ml',
            'sku' => 'FW-100',
            'harga_beli' => 20000,
            'harga_jual' => 35000,
            'stok' => 50,
            'min_stok' => 5,
            'satuan' => 'pcs',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_admin_can_access_supplier_bills_index_and_detail(): void
    {
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 5, 'harga' => 20000],
            ],
            'Beli kredit',
            null,
            'credit',
            now()->addDays(14)->toDateString(),
            'FAK-001'
        );

        $responseIndex = $this->actingAs($this->admin)->get(route('supplier-bills.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Tagihan Supplier');
        $responseIndex->assertSee($purchase->invoice_number);

        $responseShow = $this->actingAs($this->admin)->get(route('supplier-bills.show', $purchase));
        $responseShow->assertOk();
        $responseShow->assertSee($purchase->invoice_number);
        $responseShow->assertSee('FAK-001');
        $responseShow->assertSee('Catat Pembayaran');
    }

    public function test_unauthorized_user_cannot_access_supplier_bills(): void
    {
        $response = $this->actingAs($this->kasir)->get(route('supplier-bills.index'));
        $response->assertForbidden();
    }

    public function test_credit_purchase_creates_unpaid_bill(): void
    {
        $dueDate = now()->addDays(30)->toDateString();
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 10, 'harga' => 20000],
            ],
            'Restock tempo',
            null,
            'credit',
            $dueDate,
            'INV-SUP-999'
        );

        $this->assertEquals('credit', $purchase->payment_type);
        $this->assertEquals('unpaid', $purchase->payment_status);
        $this->assertEquals(0, $purchase->paid_amount);
        $this->assertEquals(200000, $purchase->total);
        $this->assertEquals(200000, $purchase->remaining_bill);
        $this->assertEquals('INV-SUP-999', $purchase->supplier_invoice);
        $this->assertEquals($dueDate, Carbon::parse($purchase->due_date)->toDateString());
    }

    public function test_cash_purchase_creates_paid_bill_with_initial_payment(): void
    {
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 5, 'harga' => 20000],
            ],
            'Beli tunai',
            null,
            'cash'
        );

        $this->assertEquals('cash', $purchase->payment_type);
        $this->assertEquals('paid', $purchase->payment_status);
        $this->assertEquals(100000, $purchase->paid_amount);
        $this->assertEquals(0, $purchase->remaining_bill);

        $this->assertCount(1, $purchase->payments);
        $this->assertEquals(100000, $purchase->payments->first()->nominal);
    }

    public function test_record_partial_and_full_payment(): void
    {
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 10, 'harga' => 20000], // 200,000
            ],
            'Tempo 10 botol',
            null,
            'credit',
            now()->addDays(14)->toDateString()
        );

        // Record partial payment 1: 50,000
        $response1 = $this->actingAs($this->admin)->post(route('supplier-bills.payments.store', $purchase), [
            'tanggal_bayar' => now()->toDateString(),
            'nominal' => 50000,
            'metode' => 'transfer',
            'bank_tujuan' => 'BCA',
            'nomor_referensi' => 'TRF-001',
            'catatan' => 'Cicilan 1',
        ]);

        $response1->assertRedirect();
        $purchase->refresh();

        $this->assertEquals('partial', $purchase->payment_status);
        $this->assertEquals(50000, $purchase->paid_amount);
        $this->assertEquals(150000, $purchase->remaining_bill);

        // Record full remaining payment 2: 150,000
        $response2 = $this->actingAs($this->admin)->post(route('supplier-bills.payments.store', $purchase), [
            'tanggal_bayar' => now()->toDateString(),
            'nominal' => 150000,
            'metode' => 'cash',
            'catatan' => 'Pelunasan',
        ]);

        $response2->assertRedirect();
        $purchase->refresh();

        $this->assertEquals('paid', $purchase->payment_status);
        $this->assertEquals(200000, $purchase->paid_amount);
        $this->assertEquals(0, $purchase->remaining_bill);
        $this->assertCount(2, $purchase->payments);
    }

    public function test_cannot_record_payment_exceeding_remaining_bill(): void
    {
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 5, 'harga' => 20000], // 100,000
            ],
            'Tempo',
            null,
            'credit',
            now()->addDays(7)->toDateString()
        );

        $response = $this->actingAs($this->admin)->post(route('supplier-bills.payments.store', $purchase), [
            'tanggal_bayar' => now()->toDateString(),
            'nominal' => 150000, // over 100,000
            'metode' => 'cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('nominal');

        $purchase->refresh();
        $this->assertEquals(0, $purchase->paid_amount);
        $this->assertEquals('unpaid', $purchase->payment_status);
    }

    public function test_purchase_return_reduces_remaining_bill_and_marks_paid_if_covered(): void
    {
        $purchase = app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [
                ['product_id' => $this->product->id, 'qty' => 10, 'harga' => 20000], // 200,000
            ],
            'Tempo 10 pcs',
            null,
            'credit',
            now()->addDays(14)->toDateString()
        );

        // Pay 100,000 first
        app(PurchaseService::class)->recordPayment(
            $purchase,
            100000,
            now()->toDateString(),
            'transfer',
            'BCA',
            'TRF-002',
            null,
            'Bayar separuh'
        );

        $purchase->refresh();
        $this->assertEquals(100000, $purchase->remaining_bill);
        $this->assertEquals('partial', $purchase->payment_status);

        // Now return 5 pcs (worth 100,000)
        $purchaseItem = $purchase->items->first();
        app(PurchaseService::class)->processReturn(
            $purchase,
            [
                ['product_id' => $purchaseItem->product_id, 'qty' => 5],
            ],
            'Barang cacat pabrik',
            now()->toDateString()
        );

        $purchase->refresh();
        // total 200k, return 100k, paid 100k -> remaining 0!
        $this->assertEquals(100000, $purchase->total_returns_amount);
        $this->assertEquals(0, $purchase->remaining_bill);
        $this->assertEquals('paid', $purchase->payment_status);
    }

    public function test_metrics_calculation(): void
    {
        // 1. Overdue bill: 200k, due 5 days ago
        app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->subDays(10)->toDateString(),
            [['product_id' => $this->product->id, 'qty' => 10, 'harga' => 20000]],
            'Overdue',
            null,
            'credit',
            now()->subDays(5)->toDateString()
        );

        // 2. Upcoming bill: 100k, due in 3 days
        app(PurchaseService::class)->createPurchase(
            $this->supplier->id,
            now()->toDateString(),
            [['product_id' => $this->product->id, 'qty' => 5, 'harga' => 20000]],
            'Upcoming',
            null,
            'credit',
            now()->addDays(3)->toDateString()
        );

        $metrics = app(PurchaseService::class)->getSupplierBillMetrics();

        $this->assertEquals(300000, $metrics['total_hutang']);
        $this->assertEquals(200000, $metrics['overdue_amount']);
        $this->assertEquals(1, $metrics['overdue_count']);
        $this->assertEquals(100000, $metrics['upcoming_amount']);
        $this->assertEquals(1, $metrics['upcoming_count']);
    }
}
