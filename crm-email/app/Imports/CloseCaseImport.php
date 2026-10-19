<?php

namespace App\Imports;

class CloseCaseImport extends BaseCaseImport
{
    protected function tableName(): string
    {
        return 'close_case';
    }
}
