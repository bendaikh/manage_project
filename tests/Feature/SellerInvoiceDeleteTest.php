<?php

namespace Tests\Feature;

use App\Models\SellerInvoice;
use App\Models\WeeklySellerInvoice;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerInvoiceDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $superadminRole = Role::create(['name' => 'superadmin']);
        $adminRole = Role::create(['name' => 'admin']);
        $sellerRole = Role::create(['name' => 'seller']);
        
        // Create users
        $this->superadmin = User::factory()->create();
        $this->superadmin->roles()->attach($superadminRole);
        
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
        
        $this->seller = User::factory()->create();
        $this->seller->roles()->attach($sellerRole);
        
        // Create test invoices
        $this->dailyInvoice = SellerInvoice::factory()->create([
            'seller' => 'Test Seller',
            'invoice_date' => '2024-01-15',
            'order_count' => 5,
            'total_amount' => 1000,
            'pdf_path' => 'invoices/sellers/test-daily.pdf'
        ]);
        
        $this->weeklyInvoice = WeeklySellerInvoice::factory()->create([
            'seller' => 'Test Seller',
            'week_start_date' => '2024-01-15',
            'week_end_date' => '2024-01-21',
            'order_count' => 10,
            'total_amount' => 2000,
            'status' => 'approved',
            'pdf_path' => 'invoices/sellers/weekly/test-weekly.pdf'
        ]);
    }

    public function test_superadmin_can_delete_daily_invoice(): void
    {
        // Create a fake PDF file
        Storage::put($this->dailyInvoice->pdf_path, 'fake pdf content');
        
        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/seller-invoices/{$this->dailyInvoice->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Seller invoice deleted successfully']);
        
        // Verify invoice is deleted from database
        $this->assertDatabaseMissing('seller_invoices', [
            'id' => $this->dailyInvoice->id
        ]);
        
        // Verify PDF file is deleted
        $this->assertFalse(Storage::exists($this->dailyInvoice->pdf_path));
    }

    public function test_superadmin_can_delete_weekly_invoice(): void
    {
        // Create a fake PDF file
        Storage::put($this->weeklyInvoice->pdf_path, 'fake pdf content');
        
        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/weekly-seller-invoices/{$this->weeklyInvoice->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Weekly invoice deleted successfully']);
        
        // Verify invoice is deleted from database
        $this->assertDatabaseMissing('weekly_seller_invoices', [
            'id' => $this->weeklyInvoice->id
        ]);
        
        // Verify PDF file is deleted
        $this->assertFalse(Storage::exists($this->weeklyInvoice->pdf_path));
    }

    public function test_admin_cannot_delete_daily_invoice(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/seller-invoices/{$this->dailyInvoice->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized access.']);
        
        // Verify invoice still exists
        $this->assertDatabaseHas('seller_invoices', [
            'id' => $this->dailyInvoice->id
        ]);
    }

    public function test_admin_cannot_delete_weekly_invoice(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/weekly-seller-invoices/{$this->weeklyInvoice->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized access.']);
        
        // Verify invoice still exists
        $this->assertDatabaseHas('weekly_seller_invoices', [
            'id' => $this->weeklyInvoice->id
        ]);
    }

    public function test_seller_cannot_delete_daily_invoice(): void
    {
        $response = $this->actingAs($this->seller)
            ->deleteJson("/seller-invoices/{$this->dailyInvoice->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized access.']);
        
        // Verify invoice still exists
        $this->assertDatabaseHas('seller_invoices', [
            'id' => $this->dailyInvoice->id
        ]);
    }

    public function test_seller_cannot_delete_weekly_invoice(): void
    {
        $response = $this->actingAs($this->seller)
            ->deleteJson("/weekly-seller-invoices/{$this->weeklyInvoice->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized access.']);
        
        // Verify invoice still exists
        $this->assertDatabaseHas('weekly_seller_invoices', [
            'id' => $this->weeklyInvoice->id
        ]);
    }

    public function test_delete_daily_invoice_handles_missing_pdf_gracefully(): void
    {
        // Don't create the PDF file - test that deletion still works
        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/seller-invoices/{$this->dailyInvoice->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Seller invoice deleted successfully']);
        
        // Verify invoice is deleted from database
        $this->assertDatabaseMissing('seller_invoices', [
            'id' => $this->dailyInvoice->id
        ]);
    }

    public function test_delete_weekly_invoice_handles_missing_pdf_gracefully(): void
    {
        // Don't create the PDF file - test that deletion still works
        $response = $this->actingAs($this->superadmin)
            ->deleteJson("/weekly-seller-invoices/{$this->weeklyInvoice->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Weekly invoice deleted successfully']);
        
        // Verify invoice is deleted from database
        $this->assertDatabaseMissing('weekly_seller_invoices', [
            'id' => $this->weeklyInvoice->id
        ]);
    }
}
