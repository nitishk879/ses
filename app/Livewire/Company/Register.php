<?php

namespace App\Livewire\Company;

use App\Http\Traits\HasCompanyLogoTrait;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads, HasCompanyLogoTrait;

    public ?Company $company;
    public User $user;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * The component's model.
     *
     * @var string
     */
    public $model = 'companies';

    /**
     * The new avatar for the user.
     *
     * @var mixed
     */
    #[Validate('image|max:1024')]
    public $company_logo = null;

    public function mount(): void
    {
        $this->state["company_logo"] = $this->company->company_logo ?? '';
        $this->state["company_name"] = $this->company->company_name ?? '';
        $this->state["company_email"] = $this->company->company_email ?? '';
        $this->state["industry"] = $this->company->industry ?? '';
        $this->state["company_url"] = $this->company->company_url ?? '';
        $this->state["company_location"] = $this->company->company_location ?? '';
        $this->state["telephone_number"] = $this->company->telephone_number ?? '';
        $this->state["established"] = $this->company->established ?? '';
        $this->state["number_of_employees"] = $this->company->number_of_employees ?? '';
        $this->state['dispatch_business'] = $this->company->dispatch_business ?? '';
        $this->state["capital"] = $this->company->capital ?? '';
        $this->state["dispatch_business_license"] = $this->company->dispatch_business_license ?? '';
        $this->state["area_of_expertise"] = $this->company->area_of_expertise ?? '';
        $this->state["specialized_industries"] = $this->company->specialized_industries ?? '';
        $this->state["introduction"] = $this->company->introduction ?? '';
        $this->state["company_information_disclose"] = $this->company->company_information_disclose ?? '';

        $this->state['expertise'] = $this->company->expertise ?? '';
        $this->state['specialised'] = $this->company->specialised ?? '';

        $this->state["firstname"] = Auth::user()->firstname ?? '';
        $this->state["lastname"] = Auth::user()->lastname ?? '';
        $this->state["email"] = Auth::user()->email ?? '';
        $this->state["phone_number"] = Auth::user()->phone_number ?? '';
        $this->state["gender"] = Auth::user()->gender ?? '';
    }

    public function updateOrCreateCompany(): null
    {
        $this->resetErrorBag();

        if($this->state['company_logo'] && is_file($this->state['company_logo'])) {
            $this->updateCompanyLogo($this->state['company_logo']);
            $this->state['company_logo'] = "{$this->model}/".Str::slug($this->state['company_name']).'.'.$this->state['company_logo']->getClientOriginalExtension();
        }

        $this->user->company()->updateOrCreate([
            'user_id' => $this->user->id,
            'company_email' => $this->state['company_email'],
            'company_url' => $this->state['company_url'],
        ],[
            'company_name' => $this->state['company_name'] ?? '',
            'company_logo' => $this->state['company_logo'] ?? '',
            'company_url' => $this->state['company_url'] ?? '',
            'company_email' => $this->state['company_email'] ?? '',
            'company_location' => $this->state['company_location'] ?? '',
            'industry' => $this->state['industry'] ?? '',
            'telephone_number' => $this->state['telephone_number'] ?? '',
            'established' => $this->state['established'] ?? '',
            'number_of_employees' => $this->state['number_of_employees'] ?? '',
            'capital' => $this->state['capital'] ?? '',
            'dispatch_business_license' => $this->state['dispatch_business_license'] ?? '',
            'area_of_expertise' => $this->state['area_of_expertise'] ?? '',
            'specialized_industries' => $this->state['specialized_industries'] ?? '',
            'introduction' => $this->state['introduction'] ?? '',
            'company_information_disclose' => $this->state['company_information_disclose']
        ]);

        return $this->redirect('/companies');
    }

    public function render()
    {
        return view('livewire.company.register');
    }
}
