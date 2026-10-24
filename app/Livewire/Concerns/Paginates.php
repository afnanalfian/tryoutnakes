<?php

namespace App\Livewire\Concerns;

use Livewire\WithPagination;

trait Paginates
{
    use WithPagination;

    public function paginationView(): string
    {
        return 'components.pagination-livewire';
    }
}
