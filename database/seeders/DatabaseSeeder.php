<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $kasirRole = Role::create(['name' => 'kasir']);
        $karyawanRole = Role::create(['name' => 'karyawan']);


        // Create Permissions
        $permissions = [
            'manage-users', 'manage-products', 'manage-categories', 'manage-suppliers', 'manage-employees',
            'manage-sales', 'manage-purchases', 'manage-attendance', 'manage-reports',
            'view-pos', 'view-sales', 'view-reports', 'view-attendance',
            'create-leave-request', 'approve-leave-request',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        $adminRole->givePermissionTo(Permission::all());
        $kasirRole->givePermissionTo(['view-pos', 'manage-sales', 'view-sales', 'view-attendance', 'create-leave-request']);
        $karyawanRole->givePermissionTo(['view-attendance', 'create-leave-request']);


        // Create Users & Employees
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@makmurjaya.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        Employee::create([
            'user_id' => $admin->id,
            'nama' => 'Administrator',
            'jabatan' => 'Admin',
            'no_kontak' => '081234567890',
            'tanggal_masuk' => '2024-01-01',
        ]);

        $kasir1 = User::create([
            'name' => 'Siti Kasir',
            'email' => 'kasir1@makmurjaya.com',
            'password' => Hash::make('password'),
        ]);
        $kasir1->assignRole('kasir');
        Employee::create([
            'user_id' => $kasir1->id,
            'nama' => 'Siti Kasir',
            'jabatan' => 'Kasir',
            'no_kontak' => '081234567891',
            'tanggal_masuk' => '2024-03-15',
        ]);

        $kasir2 = User::create([
            'name' => 'Budi Kasir',
            'email' => 'kasir2@makmurjaya.com',
            'password' => Hash::make('password'),
        ]);
        $kasir2->assignRole('kasir');
        Employee::create([
            'user_id' => $kasir2->id,
            'nama' => 'Budi Kasir',
            'jabatan' => 'Kasir',
            'no_kontak' => '081234567892',
            'tanggal_masuk' => '2024-05-01',
        ]);

        $staffUsers = [];
        $staffNames = [
            ['name' => 'Andi Staff', 'email' => 'staff1@makmurjaya.com'],
            ['name' => 'Dewi Staff', 'email' => 'staff2@makmurjaya.com'],
            ['name' => 'Rudi Staff', 'email' => 'staff3@makmurjaya.com'],
        ];

        foreach ($staffNames as $i => $staff) {
            $user = User::create([
                'name' => $staff['name'],
                'email' => $staff['email'],
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('karyawan');
            $emp = Employee::create([
                'user_id' => $user->id,
                'nama' => $staff['name'],
                'jabatan' => 'Staff Gudang',
                'no_kontak' => '08123456789'.($i + 3),
                'tanggal_masuk' => '2024-0'.($i + 2).'-01',
            ]);
            $staffUsers[] = ['user' => $user, 'employee' => $emp];
        }



        // Categories
        $categories = [
            ['name' => 'Makanan', 'slug' => 'makanan', 'description' => 'Produk makanan ringan & berat'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'description' => 'Minuman kemasan & segar'],
            ['name' => 'Kebersihan', 'slug' => 'kebersihan', 'description' => 'Produk kebersihan rumah tangga'],
            ['name' => 'Sembako', 'slug' => 'sembako', 'description' => 'Kebutuhan pokok sehari-hari'],
            ['name' => 'Alat Tulis', 'slug' => 'alat-tulis', 'description' => 'Peralatan tulis dan kantor'],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[] = Category::create($cat);
        }

        // Suppliers
        $suppliers = [
            Supplier::create(['name' => 'PT Indofood Sukses Makmur', 'code' => 'SUP-001', 'phone' => '021-5795-8822', 'email' => 'supplier@indofood.com', 'contact_person' => 'Bpk. Salim', 'address' => 'Jakarta']),
            Supplier::create(['name' => 'PT Wings Surya', 'code' => 'SUP-002', 'phone' => '031-8431-234', 'email' => 'supplier@wings.com', 'contact_person' => 'Ibu Rahma', 'address' => 'Surabaya']),
            Supplier::create(['name' => 'CV Aneka Jaya', 'code' => 'SUP-003', 'phone' => '0274-567890', 'email' => 'anekajaya@email.com', 'contact_person' => 'Bpk. Joko', 'address' => 'Yogyakarta']),
        ];

        // Products
        $products = [
            ['category_id' => $catModels[0]->id, 'name' => 'Indomie Goreng', 'sku' => 'MKN-001', 'barcode' => '8886008101053', 'harga_beli' => 2500, 'harga_jual' => 3500, 'stok' => 100, 'min_stok' => 20, 'satuan' => 'pcs'],
            ['category_id' => $catModels[0]->id, 'name' => 'Chitato Original 68g', 'sku' => 'MKN-002', 'barcode' => '8886467100014', 'harga_beli' => 8000, 'harga_jual' => 11000, 'stok' => 50, 'min_stok' => 10, 'satuan' => 'pcs'],
            ['category_id' => $catModels[0]->id, 'name' => 'Roti Sari Roti Tawar', 'sku' => 'MKN-003', 'barcode' => '8992801888001', 'harga_beli' => 12000, 'harga_jual' => 15000, 'stok' => 30, 'min_stok' => 5, 'satuan' => 'pcs'],
            ['category_id' => $catModels[1]->id, 'name' => 'Aqua 600ml', 'sku' => 'MNM-001', 'barcode' => '8886008101237', 'harga_beli' => 2000, 'harga_jual' => 3000, 'stok' => 200, 'min_stok' => 50, 'satuan' => 'botol'],
            ['category_id' => $catModels[1]->id, 'name' => 'Teh Pucuk Harum 350ml', 'sku' => 'MNM-002', 'barcode' => '8996001600146', 'harga_beli' => 2500, 'harga_jual' => 4000, 'stok' => 80, 'min_stok' => 20, 'satuan' => 'botol'],
            ['category_id' => $catModels[1]->id, 'name' => 'Coca Cola 390ml', 'sku' => 'MNM-003', 'barcode' => '5000112602333', 'harga_beli' => 4000, 'harga_jual' => 6000, 'stok' => 60, 'min_stok' => 15, 'satuan' => 'botol'],
            ['category_id' => $catModels[2]->id, 'name' => 'Sabun Cuci Sunlight 800ml', 'sku' => 'KBR-001', 'barcode' => '8999999527679', 'harga_beli' => 10000, 'harga_jual' => 14000, 'stok' => 40, 'min_stok' => 10, 'satuan' => 'botol'],
            ['category_id' => $catModels[2]->id, 'name' => 'Pewangi So Klin 900ml', 'sku' => 'KBR-002', 'barcode' => '8998866602136', 'harga_beli' => 12000, 'harga_jual' => 16000, 'stok' => 35, 'min_stok' => 8, 'satuan' => 'botol'],
            ['category_id' => $catModels[3]->id, 'name' => 'Beras Premium 5kg', 'sku' => 'SMB-001', 'barcode' => '8991102220019', 'harga_beli' => 55000, 'harga_jual' => 65000, 'stok' => 25, 'min_stok' => 5, 'satuan' => 'karung'],
            ['category_id' => $catModels[3]->id, 'name' => 'Gula Pasir 1kg', 'sku' => 'SMB-002', 'barcode' => '8991102330015', 'harga_beli' => 12000, 'harga_jual' => 15000, 'stok' => 40, 'min_stok' => 10, 'satuan' => 'kg'],
            ['category_id' => $catModels[3]->id, 'name' => 'Minyak Goreng Bimoli 2L', 'sku' => 'SMB-003', 'barcode' => '8991102440012', 'harga_beli' => 28000, 'harga_jual' => 34000, 'stok' => 20, 'min_stok' => 5, 'satuan' => 'botol'],
            ['category_id' => $catModels[4]->id, 'name' => 'Pulpen Standard AE7', 'sku' => 'ATK-001', 'barcode' => '8992858112002', 'harga_beli' => 2000, 'harga_jual' => 3500, 'stok' => 3, 'min_stok' => 10, 'satuan' => 'pcs'],
        ];

        $productModels = [];
        foreach ($products as $prod) {
            $productModels[] = Product::create($prod);
        }

        // Create sample purchases
        $purchase1 = Purchase::create([
            'invoice_number' => 'PUR-20240801-0001',
            'supplier_id' => $suppliers[0]->id,
            'user_id' => $admin->id,
            'tanggal' => '2024-08-01',
            'total' => 250000,
            'status' => 'received',
            'keterangan' => 'Pembelian rutin',
        ]);

        PurchaseItem::create(['purchase_id' => $purchase1->id, 'product_id' => $productModels[0]->id, 'qty' => 100, 'harga' => 2500, 'subtotal' => 250000]);

        // Create sample sales
        $saleDate = Carbon::now()->subDays(2);
        for ($i = 0; $i < 5; $i++) {
            $sale = Sale::create([
                'invoice_number' => 'INV-'.$saleDate->copy()->addDays($i)->format('Ymd').'-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $i % 2 === 0 ? $kasir1->id : $kasir2->id,
                'subtotal' => 0,
                'diskon' => 0,
                'grand_total' => 0,
                'bayar' => 0,
                'kembalian' => 0,
                'status' => 'completed',
                'created_at' => $saleDate->copy()->addDays($i)->setHour(rand(8, 17)),
                'updated_at' => $saleDate->copy()->addDays($i)->setHour(rand(8, 17)),
            ]);

            $subtotal = 0;
            $itemCount = rand(2, 4);
            $usedProducts = [];

            for ($j = 0; $j < $itemCount; $j++) {
                do {
                    $prod = $productModels[array_rand($productModels)];
                } while (in_array($prod->id, $usedProducts));
                $usedProducts[] = $prod->id;

                $qty = rand(1, 3);
                $itemSub = $prod->harga_jual * $qty;
                $subtotal += $itemSub;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $prod->id,
                    'qty' => $qty,
                    'harga' => $prod->harga_jual,
                    'harga_beli' => $prod->harga_beli,
                    'diskon' => 0,
                    'subtotal' => $itemSub,
                ]);

                StockMovement::create([
                    'product_id' => $prod->id,
                    'type' => 'out',
                    'qty' => $qty,
                    'stok_sebelum' => $prod->stok,
                    'stok_sesudah' => $prod->stok - $qty,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'keterangan' => 'Penjualan '.$sale->invoice_number,
                    'user_id' => $sale->user_id,
                    'created_at' => $sale->created_at,
                ]);
            }

            $sale->update([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal,
                'bayar' => ceil($subtotal / 10000) * 10000,
                'kembalian' => ceil($subtotal / 10000) * 10000 - $subtotal,
            ]);
        }

        // Create sample attendances
        $allEmployees = Employee::all();
        for ($day = 5; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($allEmployees as $emp) {
                Attendance::create([
                    'employee_id' => $emp->id,
                    'tanggal' => $date->toDateString(),
                    'clock_in' => $date->copy()->setHour(rand(7, 9))->setMinute(rand(0, 59))->format('H:i:s'),
                    'clock_out' => $day === 0 ? null : $date->copy()->setHour(rand(16, 18))->setMinute(rand(0, 59))->format('H:i:s'),
                    'status' => 'hadir',
                ]);
            }
        }
    }
}
