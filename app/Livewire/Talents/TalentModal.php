<?php

namespace App\Livewire\Talents;

use App\Models\Talent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TalentModal extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['confirmingOenModal' => 'open'];
    public ?int $id = null;
    public Talent $talent;

    public bool $confirmingOenModal = false;

    /**
     * Open one candidate's profile.
     *
     * The authorization check is not incidental. A Livewire listener is a
     * public entry point — anything that can dispatch `confirmingOenModal`
     * with an id reaches this method — and the modal now shows a named
     * person's email address and phone number. `findOrFail($id)` on its own
     * would hand those to any authenticated session that asked, including one
     * whose role is not permitted to browse candidates at all.
     *
     * The same gate as the listing the modal is opened from, so nothing is
     * reachable here that was not already reachable there.
     */
    public function open($id): void
    {
        $this->authorize('viewAny', Talent::class);

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
        return Storage::disk('public')->download("talents/{$this?->talent?->resume}");
    }

    public function render()
    {
        return view('livewire.talents.talent-modal');
    }
}
