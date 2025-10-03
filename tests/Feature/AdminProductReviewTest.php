<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithTranslation(string $name = 'Demo Product'): Product
    {
        $product = Product::factory()->create();

        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_code' => config('app.locale'),
            'locale' => config('app.locale'),
            'name' => $name,
        ]);

        return $product->fresh(['translation']);
    }

    public function test_admin_can_view_reviews_index(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->get(route('admin.reviews.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.reviews.index');
    }

    public function test_reviews_datatable_lists_reviews(): void
    {
        $admin = User::factory()->create();
        $customer = Customer::factory()->create(['name' => 'Alice Reviewer']);
        $product = $this->createProductWithTranslation('Super Gadget');
        $review = ProductReview::factory()->for($customer)->for($product)->create([
            'rating' => 4,
            'is_approved' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $review->id);
        $response->assertJsonPath('data.0.customer_name', 'Alice Reviewer');
        $response->assertJsonPath('data.0.product_name', 'Super Gadget');
        $response->assertJsonPath('data.0.status', 'approved');
    }

    public function test_reviews_datatable_can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $approved = ProductReview::factory()->create(['is_approved' => true]);
        $pending = ProductReview::factory()->create(['is_approved' => false]);

        $this->actingAs($admin);

        $response = $this->getJson(route('admin.reviews.data', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'status' => 'pending',
        ]));

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertContains($pending->id, $ids);
        $this->assertNotContains($approved->id, $ids);
    }

    public function test_admin_can_update_review(): void
    {
        $admin = User::factory()->create();
        $review = ProductReview::factory()->create([
            'is_approved' => false,
            'rating' => 3,
        ]);

        $this->actingAs($admin);

        $response = $this->put(route('admin.reviews.update', $review), [
            'rating' => 5,
            'review' => 'Updated review text',
            'is_approved' => '1',
        ]);

        $response->assertRedirect(route('admin.reviews.show', $review));

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'rating' => 5,
            'review' => 'Updated review text',
            'is_approved' => true,
        ]);
    }

    public function test_update_review_validation_errors_are_returned(): void
    {
        $admin = User::factory()->create();
        $review = ProductReview::factory()->create();

        $this->actingAs($admin);

        $response = $this
            ->from(route('admin.reviews.edit', $review))
            ->put(route('admin.reviews.update', $review), [
                'rating' => 10,
                'review' => null,
                'is_approved' => 'invalid',
            ]);

        $response->assertRedirect(route('admin.reviews.edit', $review));
        $response->assertSessionHasErrors(['rating', 'is_approved']);
    }

    public function test_admin_can_delete_review(): void
    {
        $admin = User::factory()->create();
        $review = ProductReview::factory()->create();

        $this->actingAs($admin);

        $response = $this->deleteJson(route('admin.reviews.destroy', $review));

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('product_reviews', ['id' => $review->id]);
    }
}
