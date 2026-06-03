<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
   
    public static array $extensions =[
                    'jpg','jpeg','png','gif','webp','svg',
                    'mp3','wav','mp4',
                    "doc","docx","pdf","ppt","pptx","xls","xlsx",
                    "zip"
                    ];



    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'attachments' => 'array|max:50',
            'attachments.*' =>[
                'file',
                File::types(self::$extensions)->max(10 * 1024),
            ],
            'user_id' => [ 'integer'],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => Auth::id(),
            'body'=>$this->input('body') ?: '',
        ]);
    }

    public function messages()
    {
        return [
            'attachments.*' => 'Invalid file ',

        ];
    }


}
