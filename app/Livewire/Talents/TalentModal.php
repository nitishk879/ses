<?php

namespace App\Livewire\Talents;

use App\Models\Talent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TalentModal extends Component
{
    protected $listeners = ['confirmingOenModal' => 'open'];
    public ?int $id = null;
    public Talent $talent;

    public bool $confirmingOenModal = false;

    public function open($id): void
    {
        $this->talent = Talent::findOrFail($id);
        $this->confirmingOenModal = true;
    }

    public function close(): void
    {
        $this->confirmingOenModal = false;
    }

    /**
     * Let's download resume for the user.
     *
     * @return StreamedResponse
     */
    public function download(): StreamedResponse
    {
        return Storage::disk('public')->download("talents/{$this->talent->resume}");
    }

    public function render()
    {
        return view('livewire.talents.talent-modal');
    }
}
