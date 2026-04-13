<?php

namespace App\Http\Requests\Workshops;

use App\Models\User;
use App\Models\Workshop;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachWorkshopParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $workshop = $this->route('workshop');

        return $workshop instanceof Workshop
            && $this->user() !== null
            && $this->user()->can('update', $workshop);
    }

    /**
     * @return array<string, list<string|Closure|Rule>>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $user = User::query()->find((int) $value);
                    if ($user === null || ! $user->hasRole('employee')) {
                        $fail(__('The selected user must be an employee.'));
                    }
                },
            ],
        ];
    }
}
