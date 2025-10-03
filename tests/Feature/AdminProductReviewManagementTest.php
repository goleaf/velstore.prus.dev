<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminProductReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create(['code' => config('app.locale')]);
    }

    public function test_reviews_data_endpoint_supports_advanced_filters(): void
    {
        $admin = User::factory()->create();
        $product = Product::factory()->create();
        ProductTranslation::factory()->for($product)->create([
            'language_code' => config('app.locale'),
            'locale' => config('app.locale'),
            'name' => 'Aurora Helmet',
        ]);

        $customer = Customer::factory()->create(['name' => 'Taylor Swift']);

        $matchingReview = ProductReview::factory()->for($product)->for($customer)->create([
            'rating' => 5,
            'is_approved' => true,
            'review' => 'Outstanding quality and finish',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        ProductReview::factory()->for($product)->create([
            'rating' => 2,
            'is_approved' => false,
            'review' => 'Arrived damaged',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        ProductReview::factory()->for($product)->create([
            'rating' => 4,
            'is_approved' => true,
            'review' => null,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'status' => 'approved',
            'rating_min' => 4,
            'rating_max' => 5,
            'has_review' => 1,
            'product_name' => 'Aurora',
            'date_from' => Carbon::now()->subDays(3)->toDateString(),
            'date_to' => Carbon::now()->toDateString(),
        ]));

        $response->assertOk();

        $data = collect($response->json('data'));

        $this->assertCount(1, $data);
        $this->assertSame($matchingReview->id, $data->first()['id']);
        $this->assertSame('Taylor Swift', $data->first()['customer_name']);
    }

    public function test_bulk_actions_allow_approving_and_deleting_reviews(): void
    {
        $admin = User::factory()->create();
        $product = Product::factory()->create();
        ProductTranslation::factory()->for($product)->create(['language_code' => config('app.locale'), 'locale' => config('app.locale')]);

        $pendingReviews = ProductReview::factory()->count(2)->for($product)->create([
            'is_approved' => false,
        ]);

        $approvedReview = ProductReview::factory()->for($product)->create([
            'is_approved' => true,
        ]);

        $this->actingAs($admin);

        $approveResponse = $this->postJson(route('admin.reviews.bulk-action'), [
            'action' => 'approve',
            'review_ids' => $pendingReviews->pluck('id')->all(),
        ]);

        $approveResponse
            ->assertOk()
            ->assertJson(['success' => true, 'action' => 'approve']);

        foreach ($pendingReviews as $review) {
            $this->assertTrue($review->fresh()->is_approved);
        }

        $deleteResponse = $this->postJson(route('admin.reviews.bulk-action'), [
            'action' => 'delete',
            'review_ids' => [$approvedReview->id],
        ]);

        $deleteResponse
            ->assertOk()
            ->assertJson(['success' => true, 'action' => 'delete']);

        $this->assertDatabaseMissing('product_reviews', ['id' => $approvedReview->id]);
    }

    public function test_metrics_endpoint_returns_overview_data(): void
    {
        $admin = User::factory()->create();
        $product = Product::factory()->create();
        ProductTranslation::factory()->for($product)->create(['language_code' => config('app.locale'), 'locale' => config('app.locale')]);

        ProductReview::factory()->for($product)->create([
            'rating' => 4,
            'is_approved' => true,
        ]);

        ProductReview::factory()->for($product)->create([
            'rating' => 2,
            'is_approved' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.metrics'));

        $response->assertOk();

        $this->assertSame(2, $response->json('metrics.total'));
        $this->assertSame(1, $response->json('metrics.approved'));
        $this->assertSame(1, $response->json('metrics.pending'));
        $this->assertEquals(3.0, round((float) $response->json('metrics.average_rating'), 1));

        $this->assertNotEmpty($response->json('top_products'));
        $this->assertNotEmpty($response->json('recent_reviews'));
    }
}

