<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_items_are_paginated(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-rpt@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $category = Category::create(['name' => 'Kategori Rpt', 'slug' => 'kategori-rpt']);

        for ($i = 1; $i <= 30; $i++) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Produk Rpt '.$i,
                'sku' => 'RPT-'.$i,
                'harga_beli' => 1000,
                'harga_jual' => 2000,
                'stok' => 100,
                'min_stok' => 2,
                'satuan' => 'pcs',
                'is_active' => true,
            ]);

            app(SaleService::class)->createSale(
                [['product_id' => $product->id, 'qty' => 1, 'diskon' => 0]],
                0,
                10000
            );
        }

        $data = app(ReportService::class)->getSalesReport(now()->toDateString(), now()->toDateString());

        $this->assertInstanceOf(LengthAwarePaginator::class, $data['items']);
        $this->assertSame(30, $data['items']->total());
        $this->assertSame(25, $data['items']->perPage());
        $this->assertSame(2, $data['items']->lastPage());
        $this->assertSame(30, $data['summary']['total_transactions']);
    }

    public function test_sales_report_can_return_all_items_for_pdf(): void
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir-rpt2@stockku.com', 'password' => 'password']);
        $this->actingAs($user);
        $category = Category::create(['name' => 'Kategori Rpt2', 'slug' => 'kategori-rpt2']);

        for ($i = 1; $i <= 30; $i++) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Produk Rpt2 '.$i,
                'sku' => 'RPT2-'.$i,
                'harga_beli' => 1000,
                'harga_jual' => 2000,
                'stok' => 100,
                'min_stok' => 2,
                'satuan' => 'pcs',
                'is_active' => true,
            ]);

            app(SaleService::class)->createSale(
                [['product_id' => $product->id, 'qty' => 1, 'diskon' => 0]],
                0,
                10000
            );
        }

        $data = app(ReportService::class)->getSalesReport(now()->toDateString(), now()->toDateString(), null, null, false);

        $this->assertInstanceOf(Collection::class, $data['items']);
        $this->assertCount(30, $data['items']);
    }
}
