<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;

class UserModal extends Component
{
    public $user;
    public $userId;
    public $showModal = false;

    protected $listeners = ['openModal' => 'open'];

    public function open($userId)
    {
        $this->userId = $userId;
        $this->user = User::find($userId);
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.user-modal');
    }
}
?>
<------- resources/views/livewire/user-modal.blade.php -------->
<div>
    @if($showModal)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" wire:click="close" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($user)
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    @else
                        <p>No user found.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="close">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

<!-------- Button ---->
<button class="btn btn-primary" wire:click="$emit('openModal', {{ $user->id }})">View User</button>

<!----- index ------->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User List</h1>
        @foreach($users as $user)
            <p>{{ $user->name }} <button class="btn btn-primary" wire:click="$emit('openModal', {{ $user->id }})">View</button></p>
        @endforeach

        <!-- Include the Livewire modal component -->
        <livewire:user-modal />
    </div>
@endsection

<!-------- Scritp ----------->
<script>
    Livewire.on('openModal', () => {
        var userModal = new bootstrap.Modal(document.getElementById('userModal'));
        userModal.show();
    });

    Livewire.on('closeModal', () => {
        var userModal = new bootstrap.Modal(document.getElementById('userModal'));
        userModal.hide();
    });
</script>
