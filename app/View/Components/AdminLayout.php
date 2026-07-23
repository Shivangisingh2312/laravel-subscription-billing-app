<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public function __construct(
        public string $header = 'Admin',
        public ?string $subheader = null,
        public ?string $title = null,
    ) {}

    public function render(): View
    {
        return view('layouts.admin');
    }
}
