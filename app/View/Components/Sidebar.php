<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public $links;
    public function __construct()
    {
        $this->links = [
            [
                'label' => 'Dashboar Analitik',
                'route' => 'home',
                'is_active' => request()->routeIs('home'),
                'icon' => 'fas fa-chart-line',
                'is_dropdown' => false
            ],
            [
                'label' => 'Master Data',
                'route' => '#',
                'is_active' => request()->routeIs('master-data.*'),
                'icon' => 'fas fa-cloud',
                'is_dropdown' => true,
                'items' => [
                    [
                        'label' => 'Kategori Produk',
                        'route' => 'master-data.kategori-produk.index',
                    ],
                    [
                        'label' => 'Data Produk',
                        'route' => 'master-data.produk.index',
                    ],
                    [
                        'label' => 'Stok Barang',
                        'route' => 'master-data.stok-barang.index',
                    ]
                ]
            ],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
