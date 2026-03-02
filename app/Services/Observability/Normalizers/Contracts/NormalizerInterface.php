<?php

namespace App\Services\Observability\Normalizers\Contracts;

use App\Models\DataSource;

interface NormalizerInterface
{
    public function normalize($row, DataSource $source): array;
}
