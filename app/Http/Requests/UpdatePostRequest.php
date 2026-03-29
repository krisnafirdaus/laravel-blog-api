<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|min:5|max:255',
            'slug' => 'nullable|string|unique:posts,slug,' . $this->route('post')->id,
            'content' => 'sometimes|string|min:50',
            'status' => 'sometimes|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Kategori tidak ditemukan',
            'title.min' => 'Judul minimal 5 karakter',
            'content.min' => 'Konten minimal 50 karakter',
        ];
    }

        protected function failedValidation(Validator $validator)
        {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422));
        }
}
