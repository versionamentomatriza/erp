<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BaseExport implements FromView
{
    protected $viewData;
    protected $view;

    public function __construct(array $viewData, string $view)
    {
        $this->viewData = $viewData;
        $this->view = $view;
    }

    public function view(): View
    {
        return view($this->view, $this->viewData);
    }
}

