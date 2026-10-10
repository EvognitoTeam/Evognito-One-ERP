<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationSlugService
{
    public function generate(
        string $name,
        ?int $ignoreOrganizationId = null
    ): string {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'organization';
        }

        $slug = $base;
        $counter = 2;

        while (
            Organization::withTrashed()
            ->when(
                $ignoreOrganizationId,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreOrganizationId
                )
            )
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
