<?php

namespace App\Actions\Documents;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListDocumentAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        return $user
            ->documents()
            ->latest('issue_date')
            ->paginate(20);
    }
}
