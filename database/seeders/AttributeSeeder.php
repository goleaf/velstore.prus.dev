<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $sizeAttribute = Attribute::firstOrCreate(
                ['name' => 'Size'],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $sizes = ['Small', 'Medium', 'Large'];

            foreach ($sizes as $size) {
                $value = AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $sizeAttribute->id,
                        'value' => $size,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                AttributeValueTranslation::updateOrCreate(
                    [
                        'attribute_value_id' => $value->id,
                        'language_code' => 'en',
                    ],
                    [
                        'translated_value' => $size,
                        'updated_at' => now(),
                    ]
                );
            }

            $colorAttribute = Attribute::firstOrCreate(
                ['name' => 'Color'],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $colors = ['Red', 'Green', 'Blue', 'Black', 'White', 'Yellow'];

            foreach ($colors as $color) {
                $value = AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $colorAttribute->id,
                        'value' => $color,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                AttributeValueTranslation::updateOrCreate(
                    [
                        'attribute_value_id' => $value->id,
                        'language_code' => 'en',
                    ],
                    [
                        'translated_value' => $color,
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}
