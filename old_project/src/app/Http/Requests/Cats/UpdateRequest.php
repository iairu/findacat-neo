<?php

namespace App\Http\Requests\Cats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'full_name'    => 'sometimes|required|string|max:255',
            'gender_id'   => 'sometimes|required|numeric',
            'dob'         => 'nullable|string|max:255',
            'titles_before_name'     => 'nullable|string|max:255',
            'titles_after_name'     => 'nullable|string|max:255',
            'ems_color'     => 'nullable|string|max:255',
            'breed'     => 'nullable|string|max:255',
            'genetic_tests'     => 'nullable|string|max:255',
            'chip_number'     => 'nullable|string|max:255',
            'breeding_station'     => 'nullable|string|max:255',
            'country_code'     => 'nullable|string|max:255',
            'alternative_name'     => 'nullable|string|max:255',
            'print_name_r1'     => 'nullable|string|max:255',
            'print_name_r2'     => 'nullable|string|max:255',
            'dod'     => 'nullable|string|max:255',
            'original_reg_num'     => 'nullable|string|max:255',
            'last_reg_num'     => 'nullable|string|max:255',
            'reg_num_2'     => 'nullable|string|max:255',
            'reg_num_3'     => 'nullable|string|max:255',
            'notes'     => 'nullable|string|max:255',
            'breeder'     => 'nullable|string|max:255',
            'current_owner'     => 'nullable|string|max:255',
            'country_of_origin'     => 'nullable|string|max:255',
            'country'     => 'nullable|string|max:255',
            'ownership_notes'     => 'nullable|string|max:255',
            'personal_info'     => 'nullable|string|max:255',
            'photo'     => 'nullable|string|max:255',
            'vet_confirmation'     => 'nullable|string|max:255'
        ];
    }

    public function messages()
    {
        return [
        ];
    }

    public function validated()
    {
        $formData = parent::validated();

        $formData['yob'] = $this->getYob($formData);

        return $formData;
    }

    private function getYob($formData)
    {
        if (isset($formData['yob'])) {
            return $formData['yob'];
        }

        if (isset($formData['dob']) && $formData['dob']) {
            return substr($formData['dob'], 0, 4);
        }

        return;
    }
}
