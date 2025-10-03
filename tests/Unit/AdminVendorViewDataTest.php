<?php

namespace Tests\Unit;

use App\Models\Vendor;
use App\Support\Vendors\AdminVendorViewData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVendorViewDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_builds_index_view_data_with_stats_and_filters(): void
    {
        Vendor::factory()->count(2)->create(['status' => 'active']);
        Vendor::factory()->create(['status' => 'banned']);

        $viewData = AdminVendorViewData::forIndex();
        $stats = $viewData['stats'];

        $this->assertSame(3, $stats['total']);
        $this->assertSame(2, $stats['breakdown']['active']);
        $this->assertSame(0, $stats['breakdown']['inactive']);
        $this->assertSame(1, $stats['breakdown']['banned']);
        $this->assertSame(67, $stats['percentages']['active']);
        $this->assertSame(0, $stats['percentages']['inactive']);
        $this->assertSame(33, $stats['percentages']['banned']);

        $this->assertArrayHasKey('active', $viewData['statusOptions']);
        $this->assertSame(['status' => '', 'search' => ''], $viewData['filters']);
    }

    /** @test */
    public function it_provides_form_defaults_for_create_view(): void
    {
        $formData = AdminVendorViewData::forCreate();

        $this->assertSame('active', $formData['defaultStatus']);
        $this->assertArrayHasKey('inactive', $formData['statusOptions']);
        $this->assertArrayHasKey('banned', $formData['statusOptions']);
    }
}
