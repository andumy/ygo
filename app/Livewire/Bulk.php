<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function storage_path;

class Bulk extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx')]
    public $file;
    public string $cellValue = '';

    public function save()
    {
        $this->file->storeAs(path:'bulk', name: 'bulk.xlsx');
        $spreadsheet = IOFactory::load(storage_path('app/bulk/bulk.xlsx'));
        $this->cellValue = $spreadsheet->getActiveSheet()->getCell('A1')->getValue();

        Storage::deleteDirectory('livewire-tmp');
        Storage::deleteDirectory('bulk');
    }

    public function render()
    {
        return view('livewire.bulk');
    }
}
