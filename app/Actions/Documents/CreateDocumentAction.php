<?php

namespace App\Actions\Documents;

use App\DTOs\DocumentData;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\User;

final class CreateDocumentAction
{
    public function execute(User $user, DocumentData $data): Document
    {
        return $user->documents()->create([
            'title' => $data->title,
            'customer' => $data->customer,
            'issue_date' => $data->issueDate,
            'status' => DocumentStatus::Draft,
        ]);
    }
}
