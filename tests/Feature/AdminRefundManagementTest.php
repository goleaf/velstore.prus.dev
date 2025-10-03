<?php

namespace Tests\Feature;

use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRefundManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $admin = User::factory()->create();
        $this->actingAs($admin);
    }

    public function test_index_displays_filters_and_stats(): void
    {
        Refund::factory()->count(2)->create();

        $response = $this->get(route('admin.refunds.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.refunds.index')
            ->assertViewHas('stats', function ($stats) {
                return is_array($stats)
                    && array_key_exists('total', $stats)
                    && array_key_exists('completed', $stats)
                    && array_key_exists('refunded_amount', $stats);
            })
            ->assertSee(__('cms.refunds.filters_title'))
            ->assertSee(__('cms.refunds.summary_total_count'));
    }

    public function test_admin_can_filter_refunds_by_status(): void
    {
        $completedRefund = Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
        ]);
        Refund::factory()->create([
            'status' => Refund::STATUS_PENDING,
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'status' => [Refund::STATUS_COMPLETED],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);

        $this->assertCount(1, $data);
        $this->assertStringContainsString(__('cms.refunds.status_labels.completed'), $data[0]['status']);
        $this->assertSame($completedRefund->id, (int) $data[0]['id']);
    }

    public function test_admin_can_filter_refunds_by_date_range(): void
    {
        Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        $recentRefund = Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'date_from' => now()->subDays(3)->toDateString(),
                'date_to' => now()->toDateString(),
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);
        $ids = array_map(static fn ($row) => (int) $row['id'], $data);

        $this->assertContains($recentRefund->id, $ids);
    }
}
