<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BackupUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user() && Auth::user()->is_admin) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'backup_file' => 'required|sql_gz',
        ];
    }

    public function messages()
    {
        return [
            'backup_file.sql_gz' => 'Invalid file type, must be <strong>.gz</strong> file',
        ];
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->addImplicitExtension('sql_gz', function ($attribute, $value, $parameters) {
            if ($value) {
                return $value->getClientOriginalExtension() == 'gz';
            }

            return false;
        });

        return $validator;
    }
}
