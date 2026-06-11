<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LocalNumber extends Component
{
    public function __construct(
        public mixed $value,
        public int $decimals = 0,
    ) {}

    public function render(): string
    {
        return local_num($this->value, $this->decimals);
    }
}
