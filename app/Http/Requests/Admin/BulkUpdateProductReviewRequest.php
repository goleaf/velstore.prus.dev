<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,unapprove,delete'],
            'review_ids' => ['required', 'array', 'min:1'],
            'review_ids.*' => ['integer', 'exists:product_reviews,id'],
        ];
    }

    public function action(): string
    {
        return (string) $this->input('action');
    }

    /**
     * @return array<int>
     */
    public function reviewIds(): array
    {
        return array_map('intval', $this->input('review_ids', []));
    }
}

