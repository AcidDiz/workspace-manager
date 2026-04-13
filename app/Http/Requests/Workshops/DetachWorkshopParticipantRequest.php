<?php

namespace App\Http\Requests\Workshops;

use App\Models\Workshop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetachWorkshopParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workshop = $this->route('workshop');

        return $workshop instanceof Workshop
            && $this->user() !== null
            && $this->user()->can('update', $workshop);
    }

    /**
     * @return array<string, list<string|Rule>>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
