<?php

namespace App\Exceptions;

use DomainException;

/**
 * Thrown when any write is attempted against a finalized document's
 * lines or metadata. Mapped to a 422 JSON response (see bootstrap/app.php).
 */
final class DocumentIsFinalizedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('This document is finalized and can no longer be edited.');
    }
}
