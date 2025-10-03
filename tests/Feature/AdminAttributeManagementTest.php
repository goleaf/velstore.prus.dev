<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttributeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_attributes_index_with_stats_and_filters(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => 'en']);

        $attribute = Attribute::factory()->create(['name' => 'Material']);
        AttributeValue::factory()->count(3)->create([
            'attribute_id' => $attribute->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attributes.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.attributes.index')
            ->assertViewHas('stats', function ($stats) use ($attribute) {
                return $stats['total'] === 1
                    && $stats['values'] === 3
                    && $stats['top_attribute']['name'] === $attribute->name;
            })
            ->assertSee(__('cms.attributes.total_attributes'))
            ->assertSee($attribute->name);
    }

    public function test_admin_can_filter_attributes_by_minimum_values(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => 'en']);

        $fewValues = Attribute::factory()->create(['name' => 'Length']);
        AttributeValue::factory()->create(['attribute_id' => $fewValues->id]);

        $manyValues = Attribute::factory()->create(['name' => 'Color']);
        AttributeValue::factory()->count(3)->create(['attribute_id' => $manyValues->id]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attributes.index', ['min_values' => 2]));

        $response
            ->assertOk()
            ->assertSee($manyValues->name)
            ->assertDontSee($fewValues->name);
    }

    public function test_admin_can_store_attribute_with_translations(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => 'en']);
        Language::factory()->create(['code' => 'fr']);

        $payload = [
            'name' => 'Material',
            'values' => ['Cotton', 'Linen'],
            'translations' => [
                'en' => ['Cotton', 'Linen'],
                'fr' => ['Coton', 'Lin'],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->post(route('admin.attributes.store'), $payload);

        $response->assertRedirect(route('admin.attributes.index'));

        $attribute = Attribute::where('name', 'Material')->first();
        $this->assertNotNull($attribute);
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Cotton',
        ]);
        $cottonValue = AttributeValue::where('attribute_id', $attribute->id)
            ->where('value', 'Cotton')
            ->first();

        $this->assertNotNull($cottonValue);
        $this->assertDatabaseHas('attribute_value_translations', [
            'attribute_value_id' => $cottonValue->id,
            'language_code' => 'fr',
            'translated_value' => 'Coton',
        ]);
    }

    public function test_admin_can_update_attribute_values(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => 'en']);

        $attribute = Attribute::factory()->create(['name' => 'Size']);
        $value = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value' => 'Small',
        ]);
        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $value->id,
            'language_code' => 'en',
        ]);

        $payload = [
            'name' => 'Updated Size',
            'values' => ['Medium', 'Large'],
            'translations' => [
                'en' => ['Medium', 'Large'],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->put(route('admin.attributes.update', $attribute), $payload);

        $response->assertRedirect(route('admin.attributes.index'));

        $this->assertDatabaseMissing('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Small',
        ]);
        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => 'Medium',
        ]);
    }

    public function test_admin_can_delete_attribute(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => 'en']);

        $attribute = Attribute::factory()->create();

        $this->actingAs($admin);

        $response = $this->delete(route('admin.attributes.destroy', $attribute));

        $response->assertRedirect(route('admin.attributes.index'));
        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }
}
