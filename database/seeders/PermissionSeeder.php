<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Product permissions
            ['name' => 'view_products', 'description' => 'View products'],
            ['name' => 'create_products', 'description' => 'Create new products'],
            ['name' => 'edit_products', 'description' => 'Edit products'],
            ['name' => 'delete_products', 'description' => 'Delete products'],

            // Sale permissions
            ['name' => 'view_sales', 'description' => 'View sales'],
            ['name' => 'create_sales', 'description' => 'Create new sales'],
            ['name' => 'edit_sales', 'description' => 'Edit sales'],
            ['name' => 'delete_sales', 'description' => 'Delete sales'],

            // Devis permissions
            ['name' => 'view_devis', 'description' => 'View devis'],
            ['name' => 'create_devis', 'description' => 'Create new devis'],
            ['name' => 'edit_devis', 'description' => 'Edit devis'],
            ['name' => 'delete_devis', 'description' => 'Delete devis'],
            ['name' => 'send_devis', 'description' => 'Send devis'],
            ['name' => 'accept_devis', 'description' => 'Accept devis'],
            ['name' => 'reject_devis', 'description' => 'Reject devis'],
            ['name' => 'convert_devis', 'description' => 'Convert devis to sale'],

            // Purchase permissions
            ['name' => 'view_purchases', 'description' => 'View purchases'],
            ['name' => 'create_purchases', 'description' => 'Create new purchases'],
            ['name' => 'edit_purchases', 'description' => 'Edit purchases'],
            ['name' => 'delete_purchases', 'description' => 'Delete purchases'],

            // Category permissions
            ['name' => 'view_categories', 'description' => 'View categories'],
            ['name' => 'create_categories', 'description' => 'Create new categories'],
            ['name' => 'edit_categories', 'description' => 'Edit categories'],
            ['name' => 'delete_categories', 'description' => 'Delete categories'],

            // Client permissions
            ['name' => 'view_clients', 'description' => 'View clients'],
            ['name' => 'create_clients', 'description' => 'Create new clients'],
            ['name' => 'edit_clients', 'description' => 'Edit clients'],
            ['name' => 'delete_clients', 'description' => 'Delete clients'],

            // Supplier permissions
            ['name' => 'view_suppliers', 'description' => 'View suppliers'],
            ['name' => 'create_suppliers', 'description' => 'Create new suppliers'],
            ['name' => 'edit_suppliers', 'description' => 'Edit suppliers'],
            ['name' => 'delete_suppliers', 'description' => 'Delete suppliers'],

            // Invoice permissions
            ['name' => 'view_invoices', 'description' => 'View invoices'],
            ['name' => 'generate_invoices', 'description' => 'Generate invoices'],
            ['name' => 'download_invoices', 'description' => 'Download invoices'],

            // Refund permissions
            ['name' => 'view_refunds', 'description' => 'View refunds'],
            ['name' => 'create_refunds', 'description' => 'Create refunds'],
            ['name' => 'delete_refunds', 'description' => 'Delete refunds'],

            // Stock permissions
            ['name' => 'view_stock', 'description' => 'View stock information'],
            ['name' => 'manage_stock', 'description' => 'Manage stock movements'],

            // Dashboard permissions
            ['name' => 'view_dashboard', 'description' => 'View dashboard'],

            // Reports permissions
            ['name' => 'view_financial_reports', 'description' => 'View financial reports'],
            ['name' => 'view_inventory_reports', 'description' => 'View inventory reports'],
            ['name' => 'view_sales_reports', 'description' => 'View sales reports'],
            ['name' => 'view_purchasing_reports', 'description' => 'View purchasing reports'],

            // User management permissions
            ['name' => 'view_users', 'description' => 'View users'],
            ['name' => 'create_users', 'description' => 'Create new users'],
            ['name' => 'edit_users', 'description' => 'Edit users'],
            ['name' => 'delete_users', 'description' => 'Delete users'],
            ['name' => 'manage_permissions', 'description' => 'Manage user permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}
