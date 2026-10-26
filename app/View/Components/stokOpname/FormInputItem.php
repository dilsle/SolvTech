<?php

namespace App\View\Components\stokOpname;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class FormInputItem extends Component
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function render(): View|Closure|string
    {
        return view('components.stok-opname.form-input-item');
    }
}
