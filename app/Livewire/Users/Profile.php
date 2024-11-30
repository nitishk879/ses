<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\ProfileForm;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Profile extends Component
{
    public ProfileForm $profile;
    public $language = '';

    /**
     * Let's get all authenticated user data.
     *
    */
    public function mount(): void
    {
        $user = User::find(auth()->id());
        $this->profile->setProfile($user);
    }

    // Alternatively, handle all profile updates
    public function updated($propertyName): void
    {
        if ($propertyName === 'profile.languages') {
            $this->profile->languages = json_encode($this->profile['languages']);
        }
    }

    public function updateLanguages(): void
    {
        $this->profile->languages = [$this->profile->languages];
    }

    /**
     *  Update function will update user data
    */
    public function update(): null
    {
        $this->validate();
        $this->profile->update();

        return $this->redirect('/profile');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.users.profile');
    }
}
