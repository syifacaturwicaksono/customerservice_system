<?php

namespace App\Imports;

class PendingCaseImport extends BaseCaseImport
{
    protected function tableName(): string
    {
        return 'pending_case';
    }
}
