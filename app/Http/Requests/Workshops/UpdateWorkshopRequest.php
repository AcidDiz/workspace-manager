<?php

namespace App\Http\Requests\Workshops;

use App\Models\Workshop;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Workshop $workshop */
        $workshop = $this->route('workshop');

        return $this->user()?->can('update', $workshop) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('workshop_category_id') && $this->input('workshop_category_id') === '') {
            $this->merge(['workshop_category_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'workshop_category_id' => ['nullable', 'integer', 'exists:workshop_categories,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
