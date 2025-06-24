<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string',
            'model' => 'nullable|string',
        ];
    }

    /**
     * Get the validated text from the request.
     */
    public function getText(): string
    {
        return $this->validated()['text'];
    }

    /**
     * Get the validated model from the request.
     */
    public function getModel(): ?string
    {
        return $this->validated()['model'] ?? null;
    }
}
