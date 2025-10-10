<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableManagementSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can access the /tables/manage route
     */
    public function test_admin_can_access_tables_manage_route()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Authenticate as admin
        $this->actingAs($admin);

        // Try to access the tables management route
        $response = $this->get('/tables/manage');

        // Admin should have access (200 OK or 302 if redirecting to form)
        $response->assertStatus(200);
    }

    /**
     * Test that cashier cannot access the /tables/manage route
     */
    public function test_cashier_cannot_access_tables_manage_route()
    {
        // Create a cashier user
        $cashier = User::factory()->create([
            'role' => 'cashier'
        ]);

        // Authenticate as cashier
        $this->actingAs($cashier);

        // Try to access the tables management route
        $response = $this->get('/tables/manage');

        // Cashier should be forbidden or redirected
        // Laravel typically redirects unauthorized users to login (302) or returns 403
        $response->assertStatus(403); // Access denied
    }

    /**
     * Test that unauthenticated user cannot access the /tables/manage route
     */
    public function test_unauthenticated_user_cannot_access_tables_manage_route()
    {
        // Don't authenticate any user
        $response = $this->get('/tables/manage');

        // Unauthenticated users should be redirected to login
        $response->assertRedirect('/login');
    }

    /**
     * Test that admin can perform table management actions
     */
    public function test_admin_can_perform_table_management_actions()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Authenticate as admin
        $this->actingAs($admin);

        // Test creating a table (assuming POST /tables endpoint exists)
        $response = $this->post('/tables', [
            'name' => 'Test Table A',
            'hourly_rate' => 15000,
            'status' => 'available'
        ]);

        // Should be successful for admin
        // The exact status depends on the application's implementation
        // If the route requires authentication and permission checks, it should allow admin
        $response->assertStatus(302); // Assuming it redirects after successful creation
        
        // If we're testing a JSON API endpoint that returns data directly:
        // $response->assertStatus(201); // Created
    }

    /**
     * Test that cashier cannot perform table management actions
     */
    public function test_cashier_cannot_perform_table_management_actions()
    {
        // Create a cashier user
        $cashier = User::factory()->create([
            'role' => 'cashier'
        ]);

        // Authenticate as cashier
        $this->actingAs($cashier);

        // Try to create a table (this should fail for cashier)
        $response = $this->post('/tables', [
            'name' => 'Unauthorized Table',
            'hourly_rate' => 15000,
            'status' => 'available'
        ]);

        // Cashier should not be allowed to create tables
        $response->assertStatus(403); // Forbidden
    }
}