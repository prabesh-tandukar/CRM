<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Contact permissions
            'view-contacts', 'create-contacts', 'edit-contacts', 'delete-contacts', 'import-contacts', 'export-contacts',
            
            // Company permissions
            'view-companies', 'create-companies', 'edit-companies', 'delete-companies', 'import-companies', 'export-companies',
            
            // Lead permissions
            'view-leads', 'create-leads', 'edit-leads', 'delete-leads', 'import-leads', 'export-leads', 'convert-leads',
            
            // Deal permissions
            'view-deals', 'create-deals', 'edit-deals', 'delete-deals', 'import-deals', 'export-deals',
            
            // Product permissions
            'view-products', 'create-products', 'edit-products', 'delete-products',
            
            // Task permissions
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'complete-tasks',
            
            // Activity permissions
            'view-activities', 'create-activities', 'edit-activities', 'delete-activities',
            
            // Document permissions
            'view-documents', 'create-documents', 'edit-documents', 'delete-documents',
            
            // Report permissions
            'view-reports', 'create-reports', 'export-reports',
            
            // User permissions
            'view-users', 'create-users', 'edit-users', 'delete-users',
            
            // Setting permissions
            'manage-settings'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Create roles and assign permissions
        
        // Admin role - has all permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());
        
        // Manager role
        $managerRole = Role::create(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'view-contacts', 'create-contacts', 'edit-contacts', 'import-contacts', 'export-contacts',
            'view-companies', 'create-companies', 'edit-companies', 'import-companies', 'export-companies',
            'view-leads', 'create-leads', 'edit-leads', 'import-leads', 'export-leads', 'convert-leads',
            'view-deals', 'create-deals', 'edit-deals', 'import-deals', 'export-deals',
            'view-products', 'create-products', 'edit-products',
            'view-tasks', 'create-tasks', 'edit-tasks', 'delete-tasks', 'complete-tasks',
            'view-activities', 'create-activities', 'edit-activities', 'delete-activities',
            'view-documents', 'create-documents', 'edit-documents', 'delete-documents',
            'view-reports', 'export-reports',
            'view-users'
        ]);
        
        // Sales Rep role
        $salesRepRole = Role::create(['name' => 'Sales Rep']);
        $salesRepRole->givePermissionTo([
            'view-contacts', 'create-contacts', 'edit-contacts',
            'view-companies', 'create-companies', 'edit-companies',
            'view-leads', 'create-leads', 'edit-leads', 'convert-leads',
            'view-deals', 'create-deals', 'edit-deals',
            'view-products',
            'view-tasks', 'create-tasks', 'edit-tasks', 'complete-tasks',
            'view-activities', 'create-activities', 'edit-activities',
            'view-documents', 'create-documents', 'edit-documents',
            'view-reports'
        ]);
        
        // Customer Service role
        $customerServiceRole = Role::create(['name' => 'Customer Service']);
        $customerServiceRole->givePermissionTo([
            'view-contacts', 'edit-contacts',
            'view-companies',
            'view-deals',
            'view-tasks', 'create-tasks', 'edit-tasks', 'complete-tasks',
            'view-activities', 'create-activities', 'edit-activities',
            'view-documents', 'create-documents'
        ]);
        
        // Read-Only role
        $readOnlyRole = Role::create(['name' => 'Read Only']);
        $readOnlyRole->givePermissionTo([
            'view-contacts',
            'view-companies',
            'view-leads',
            'view-deals',
            'view-products',
            'view-tasks',
            'view-activities',
            'view-documents',
            'view-reports'
        ]);
        
        // Create admin user
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
        
        $adminUser->assignRole('Admin');
        
        // Create demo manager
        $managerUser = User::where('email', 'manager@example.com')->first();
        
        if (!$managerUser) {
            $managerUser = User::create([
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'job_title' => 'Sales Manager',
                'email_verified_at' => now(),
            ]);
        }
        
        $managerUser->assignRole('Manager');
        
        // Create demo sales rep
        $salesUser = User::where('email', 'sales@example.com')->first();
        
        if (!$salesUser) {
            $salesUser = User::create([
                'name' => 'Sales User',
                'email' => 'sales@example.com',
                'password' => Hash::make('password'),
                'job_title' => 'Sales Representative',
                'email_verified_at' => now(),
            ]);
        }
        
        $salesUser->assignRole('Sales Rep');
    }
}