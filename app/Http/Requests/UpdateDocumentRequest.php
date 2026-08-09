<?php

namespace App\Http\Requests;

class UpdateDocumentRequest extends StoreDocumentRequest
{  
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'customer' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
        ];
    }
}
