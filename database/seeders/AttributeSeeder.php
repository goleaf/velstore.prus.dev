<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Language;
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
            $languages = Language::query()
                ->where('active', true)
                ->pluck('code')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($languages)) {
                $languages = ['en'];
            }

            $attributeDefinitions = [
                'Size' => ['Extra Small', 'Small', 'Medium', 'Large', 'Extra Large'],
                'Color' => ['Red', 'Green', 'Blue', 'Black', 'White', 'Yellow'],
                'Material' => ['Cotton', 'Linen', 'Wool', 'Leather'],
                'Length' => ['Short', 'Regular', 'Long'],
            ];

            foreach ($attributeDefinitions as $attributeName => $values) {
                $attribute = Attribute::firstOrCreate(
                    ['name' => $attributeName],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                foreach ($values as $valueName) {
                    $value = AttributeValue::firstOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'value' => $valueName,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    foreach ($languages as $languageCode) {
                        AttributeValueTranslation::updateOrCreate(
                            [
                                'attribute_value_id' => $value->id,
                                'language_code' => $languageCode,
                            ],
                            [
                                'translated_value' => $valueName,
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        });
    }
}
