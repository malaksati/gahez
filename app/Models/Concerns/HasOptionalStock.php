<?php

namespace App\Models\Concerns;

trait HasOptionalStock
{
    public function tracksStock(): bool
    {
        return $this->stock !== null;
    }

    public function isInStock(): bool
    {
        if ($this->tracksStock()) {
            return (int) $this->stock > 0;
        }

        return (bool) ($this->is_in_stock ?? true);
    }

    public function hasStock(): bool
    {
        return $this->isInStock();
    }
}
