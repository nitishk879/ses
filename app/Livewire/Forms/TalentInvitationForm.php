<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class TalentInvitationForm extends Form
{
    #[Validate('required|min:5')]
    public $invitation_ids = array();

    #[Validate('required|min:5')]
    public $content = '';
}
