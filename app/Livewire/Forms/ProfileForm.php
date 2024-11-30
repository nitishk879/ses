<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProfileForm extends Form
{
    public ?User $user;

    #[Validate('required|min:2')]
    public $firstname = '';
    #[Validate('required|min:2')]
    public $lastname = '';
    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|min:10')]
    public $phone = '';

    #[Validate('required|date')]
    public $date_of_birth = '';

    #[Validate('required')]
    public $nationality = '';
    #[Validate('required')]
    public $languages = '';
    #[Validate('required')]
    public $country = '';

    #[Validate('required')]
    public $gender = '';


    public function setProfile(User $user): void
    {
        $this->user = $user;
        $this->firstname = $user->firstname;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->date_of_birth = $user->date_of_birth;
        $this->nationality = $user->nationality;
        $this->languages = $user->languages ?? [];
        $this->country = $user->country;
        $this->gender = $user->gender;

    }

    public function update(): void
    {
        $this->validate();

        $this->user->update(
            $this->all()
        );
    }
}
