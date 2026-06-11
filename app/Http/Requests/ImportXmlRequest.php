<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportXmlRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->input('type') === 'users') {
            return $this->user()?->isAdmin() === true;
        }

        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['posts', 'users'])],
            'xml' => ['required', 'file', 'mimetypes:text/xml,application/xml,text/plain', 'max:2048'],
        ];
    }
}
