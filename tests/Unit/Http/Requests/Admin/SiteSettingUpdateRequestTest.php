<?php

namespace Tests\Unit\Http\Requests\Admin;

use App\Http\Requests\Admin\SiteSettingUpdateRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteSettingUpdateRequest::class)]
class SiteSettingUpdateRequestTest extends TestCase
{
    #[Test]
    public function authorize_returns_true_when_user_is_present(): void
    {
        $request = new SiteSettingUpdateRequest();
        $request->setUserResolver(fn () => (object) ['id' => 1]);

        $this->assertTrue($request->authorize());
    }

    #[Test]
    public function authorize_returns_false_when_user_is_missing(): void
    {
        $request = new SiteSettingUpdateRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    #[Test]
    public function rules_define_expected_validation_structure(): void
    {
        $request = new SiteSettingUpdateRequest();

        $expectedHexColorRule = ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'];

        $rules = $request->rules();

        $this->assertSame(['required', 'string', 'max:255'], $rules['site_name']);
        $this->assertSame($expectedHexColorRule, $rules['primary_color']);
        $this->assertSame($expectedHexColorRule, $rules['secondary_color']);
        $this->assertContains('required_if:maintenance_mode,1', $rules['maintenance_message']);
    }

    #[Test]
    public function rules_cover_all_expected_fields(): void
    {
        $request = new SiteSettingUpdateRequest();

        $rules = $request->rules();

        $expectedKeys = [
            'site_name',
            'tagline',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'contact_email',
            'support_email',
            'contact_phone',
            'support_hours',
            'address',
            'logo',
            'favicon',
            'primary_color',
            'secondary_color',
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'linkedin_url',
            'maintenance_mode',
            'maintenance_message',
            'footer_text',
        ];

        $this->assertSameCanonicalizing($expectedKeys, array_keys($rules));
    }

    #[Test]
    public function messages_override_color_validation_feedback(): void
    {
        $request = new SiteSettingUpdateRequest();

        $messages = $request->messages();

        $this->assertArrayHasKey('primary_color.regex', $messages);
        $this->assertArrayHasKey('secondary_color.regex', $messages);
        $this->assertNotEmpty($messages['primary_color.regex']);
        $this->assertNotEmpty($messages['secondary_color.regex']);
    }
}
