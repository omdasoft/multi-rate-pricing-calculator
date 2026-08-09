<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';
}
