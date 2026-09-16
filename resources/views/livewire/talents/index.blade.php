<div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if($subcategories)
                @foreach(\App\Models\Category::all() as $cat)
                    @php
                        $isOpen = $cat->subCategories->contains(function ($subCategory){
                            return in_array($subCategory->id, $this->subcategories);
                        });
                    @endphp
                    <span class="badge text-bg-primary text-white">{{ $isOpen ? $cat->title : '' }}</span>
                    @foreach($cat->subCategories as $subCat)
                        <span class="badge text-bg-secondary">{{ in_array($subCat->id, $this->subcategories) ? $subCat->title : '' }}</span>
                    @endforeach
                @endforeach
            @endif

            @if($this->search)
                <span class="badge text-bg-primary text-white">{{ __("common/sidebar.search_keyword") }}</span>
                <span class="badge text-bg-secondary">{{ $this->search }}</span>
            @endif

            @if($workLocation)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.locations") }} </span>
                @foreach(\App\Models\Location::all() as $lok)
                    <span class="badge text-bg-secondary">{{ in_array($lok->id, $workLocation) ? $lok->title : '' }}</span>
                @endforeach
            @endif

            @if($work_mode)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.work_mode") }} </span>
                @foreach(\App\Enums\WorkLocationEnum::cases() as $wmode)
                    <span class="badge text-bg-secondary">{{ in_array($wmode->value, $this->work_mode) ? __("common/sidebar.{$wmode->name}") : '' }}</span>
                @endforeach
            @endif
            @if($min_salary || $max_salary)
                <span class="badge text-bg-primary text-white">{{ __("common/sidebar.monthly_salary_range") }}</span>
                <span class="badge text-bg-secondary">{{ __("common/sidebar.min_salary") }}: {{ $this->min_salary ?? '' }}</span>
                <span class="badge text-bg-secondary">{{ __("common/sidebar.max_salary") }}: {{ $this->max_salary ?? '' }}</span>
            @endif

            @if($availability)
                <span class="badge text-bg-primary text-white">{{ __("common/sidebar.possible_participation") }} </span>
                @foreach(\App\Enums\ParticipationEnum::cases() as $flow)
                    <span class="badge text-bg-secondary">{{ in_array($flow->value, $this->availability) ? __("common/sidebar.{$flow->value}") : '' }}</span>
                @endforeach
            @endif

            @if($age)
                <span class="badge text-bg-primary text-white">{{ __("talents/index.age") }} </span>
                @foreach($ages as $trade)
                    <span class="badge text-bg-secondary">{{ in_array($trade, $this->age) ? $trade : '' }}</span>
                @endforeach
            @endif

            @if($gender)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.contract_type") }} </span>
                @foreach(\App\Enums\GenderEnum::cases() as $contract)
                    <span class="badge text-bg-secondary">{{ $contract->value == $this->gender ? __("talents/registration.{$contract->value}") : '' }}</span>
                @endforeach
            @endif

            @if($nationality)
                <span class="badge text-bg-primary text-white">{{ __("talents/index.nationality") }} </span>
                @foreach($nationalities as $nation)
                    <span class="badge text-bg-secondary">{{ in_array($nation, $this->nationality) ? __("talents/registration.{$nation}") : '' }}</span>
                @endforeach
            @endif

            @if($affiliation)
                <span class="badge text-bg-primary text-white">{{ __("talents/registration.affiliation") }} </span>
                @foreach(\App\Enums\AffiliationEnum::cases() as $af)
                    <span class="badge text-bg-secondary">{{ in_array($af->value, $this->affiliation) ? __("projects/form.{$af->name}") : '' }}</span>
                @endforeach
            @endif

            @if($contract)
                <span class="badge text-bg-primary text-white">{{ __("projects/form.contract_type") }} </span>
                @foreach(\App\Enums\ContractClassificationEnum::cases() as $cont)
                    <span class="badge text-bg-secondary">{{ in_array($cont->value, $this->contract) ? __("projects/form.{$cont->name}") : '' }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <div class="d-flex w-100 justify-content-between my-3">
        <h2 class="search-keyword">
            @if($search)
                {{ __("talents/index.showing_results_for") }}: <span>"{{ $search ?? __("talents/index.search_keyword") }}"</span>
            @endif
        </h2>
        <div class="search-sort">
            <div class="input-group mb-3">
                <label class="input-group-text" for="sortResultBy">{{ __("talents/index.sort_by") }}</label>
                <select class="form-select" id="sortResultBy">
                    <option wire:click="sortBy('created_at')">{{ __("talents/index.recent_listings") }}</option>
                    <option wire:click="sortBy('updated_on')">{{ __("talents/index.updated_on") }}</option>
                    <option wire:click="sortBy('created_on')">{{ __("talents/index.registered_on") }}</option>
                    <option wire:click="sortBy('joining_date')">{{ __("talents/index.favourite") }}</option>
                    <option wire:click="sortBy('deadline')">{{ __("talents/index.application_deadline") }}</option>
                </select>
            </div>
        </div>
    </div>
    {{-- Rank candidates against one of this company's projects.
         A match score belongs to a (project, candidate) pair, so nothing can
         be scored until the recruiter says which project they are hiring for. --}}
    @if($matchableProjects->isNotEmpty())
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <label for="matchProject" class="form-label mb-1">
                    {{ __("talents/index.match_against_project") }}
                </label>
                <select id="matchProject" class="form-select" wire:model.live="matchProject">
                    <option value="">{{ __("talents/index.no_project_selected") }}</option>
                    @foreach($matchableProjects as $matchable)
                        <option value="{{ $matchable->id }}">{{ $matchable->title }}</option>
                    @endforeach
                </select>
            </div>
            @if($matchProject)
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">{{ __("talents/index.ranked_by_match") }}</small>
                </div>
            @endif
        </div>
    @endif

    @if($talents->count() >=1)
        @foreach($talents as $talent)
            @php($match = $matchProject ? $talent->aiMatches->first() : null)
            <div class="talent-card" wire:key="{{ $talent->id }}">
                <a href="" class="add-to-favourite">
                    <i class="fa-solid fa-star"></i>
                </a>

                {{-- One kebab, not a stack of three full-width buttons at the foot
                     of the card. Those made every card taller than its own content
                     and gave equal weight to three things a recruiter does
                     occasionally.

                     Pinned to the card corner rather than dropped into the match
                     band, because that band only renders when a project is
                     selected — inside it, the actions would vanish on the plain
                     Find Talent screen. Positioned absolutely, so it lines up with
                     the score row without depending on it existing.

                     `data-bs-strategy="fixed"` is load-bearing: `.talent-card` sets
                     `overflow: hidden` to clip its header to the rounded corners,
                     which would otherwise cut the open menu off at the card edge.
                     The fixed strategy positions against the viewport instead, and
                     nothing in this card's ancestry sets a transform, so it really
                     does escape the clip.

                     The old `id="openTalentModal"` is gone: it was repeated on every
                     card on the page and referenced by nothing. --}}
                <div class="dropdown talent-actions">
                    <button type="button"
                            class="btn btn-sm talent-actions-toggle"
                            id="talentActions{{ $talent->id }}"
                            data-bs-toggle="dropdown"
                            data-bs-strategy="fixed"
                            aria-expanded="false"
                            aria-label="{{ __('talents/index.actions') }}"
                            title="{{ __('talents/index.actions') }}">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end"
                        aria-labelledby="talentActions{{ $talent->id }}">
                        {{-- "View resume" used to sit above this and dispatched the
                             identical event to the identical modal — two labels for
                             one action, which only made a reader wonder what the
                             difference was. --}}
                        <li>
                            <button type="button" class="dropdown-item"
                                    wire:click="$dispatch('confirmingOenModal', { id:{{ $talent->id }} })">
                                {{ __("talents/index.view_profile") }}
                            </button>
                        </li>

                        {{-- Gated on the policy, so it is shown only to someone who
                             may actually save it — an item that 403s is worse than
                             no item. --}}
                        @can('update', $talent)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('talents.edit', $talent) }}">
                                    {{ __("talents/index.edit_profile") }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>

                {{-- The match block sits in the card's top-left corner, which is
                     already occupied: `.add-to-favourite` is absolutely positioned
                     at top:-1rem / left:-1rem with a 3.5rem circle and z-index 1,
                     so it covers everything up to 2.5rem in from the card edge.
                     The first version of this put the score there and the star ate
                     the leading digits — "64/100" rendered as "100", which is not a
                     cosmetic bug: it is a wrong number shown to a recruiter.

                     `.talent-match` clears it with padding rather than with a
                     z-index fight, because the star has to stay clickable. The
                     rule lives in the parent view's stylesheets stack: the
                     compiled CSS bundle on the server is from Dec 2024 and
                     rebuilding it would ship two years of unrelated style changes
                     at the same time. --}}
                @if($matchProject)
                    @php($parse = $talent->aiResumeParse)
                    <div class="talent-match">
                        @if($match)
                            {{-- Colour is a reading aid only — never the only signal.
                                 The number is always spelled out, and the reasons
                                 below are what a recruiter should actually act on. --}}
                            @php($tone = $match->score >= 75 ? 'strong' : ($match->score >= 50 ? 'fair' : 'weak'))
                            @php($width = max(0, min(100, (int) round($match->score))))
                            <div class="talent-match-head">
                                <span class="talent-match-caption">{{ __('talents/index.match_score') }}</span>
                                <div class="talent-match-figure">
                                    <span class="talent-match-score talent-match-score--{{ $tone }}">
                                        <span class="talent-match-number">{{ $match->score }}</span><span
                                              class="talent-match-outof">/100</span>
                                    </span>
                                    {{-- aria-hidden on purpose: the figure to its left already
                                         states the same number, and a screen reader announcing
                                         it twice is noise, not access. --}}
                                    <span class="talent-match-meter" aria-hidden="true">
                                        <span class="talent-match-meter-fill talent-match-meter-fill--{{ $tone }}"
                                              style="width: {{ $width }}%"></span>
                                    </span>

                                    {{-- A score read off a profile is a weaker claim than one
                                         read off a CV. Saying so is the difference between a
                                         number a recruiter can act on and one they have to
                                         take on faith. --}}
                                    @if($parse?->isFromProfile())
                                        <span class="talent-match-flag"
                                              title="{{ __('talents/index.scored_from_profile_help') }}">
                                            {{ __("talents/index.scored_from_profile") }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <ul class="talent-match-reasons">
                                @foreach($match->reasons() as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                                @foreach($match->blockers() as $blocker)
                                    <li class="is-blocker">&#33; {{ $blocker }}</li>
                                @endforeach
                                @foreach($match->unverified() as $unverified)
                                    <li class="is-unverified">? {{ $unverified }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{-- An absent score is not a zero, and the three reasons
                                 it can be absent need different actions from the
                                 recruiter. Collapsing them into one badge left the
                                 only visible explanation — "not scored yet" — as the
                                 one that was usually wrong. --}}
                            <div class="talent-match-head">
                                <span class="talent-match-score talent-match-score--none">
                                    {{ __("talents/index.not_scored_yet") }}
                                </span>
                            </div>
                            <div class="talent-match-note">
                                @if(! $parse)
                                    {{ __("talents/index.not_scored_no_parse") }}
                                @else
                                    {{ __("talents/index.not_scored_run_matching") }}
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="talent-card-header">
                    <div class="row justify-content-between">
                        <div class="col-md-6 col-lg-5 ps-md-3">
                            <span class="talent-name">{{ $talent->user->short_name }}</span>
                            <div class="d-flex gap-2">
                                <span class="talent-age">{{ __("talents/index.gender") }} : <b>{{ \App\Enums\GenderEnum::toName($talent->user->gender->value) }}</b></span>
                                <span class="talent-age">{{ __("talents/index.age") }} : <b>{{ $talent->user->age }}</b></span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-5">
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-calendar-days"></i>
                                {{ __("talents/index.registered_on") }}: {{ $talent->user->created_at->format('M d, Y') }}</div>
                            <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-rotate"></i>
                                {{ __("talents/index.updated_on") }}: {{ $talent->user->updated_at->format('M d, Y') }}</div>
                            @if($talent->user?->last_login)
                                <div class="d-flex gap-2 align-items-center talent-updated"><i class="fa-solid fa-lock"></i>
                                    {{ __("talents/index.last_logged_in") }}: {{ $talent->user?->last_login }}
                                </div>
                            @endif
                        </div>
{{--                        <div class="col-md-6 col-lg-2 text-end">--}}
{{--                            <a href="" class="talent-save"><i class="fas fa-heart"></i> {{ random_int(18, 50) }}</a>--}}
{{--                        </div>--}}
                    </div>
                </div>
                <div class="talent-card-body">
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <h3 class="talent-availability">{{ __('talents/index.availability') }}:
                                <span>{{ \App\Enums\ParticipationEnum::toName($talent->availability->value) ?? __("talents/index.{$talent->availability}") }}</span>
                            </h3>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{ __("talents/index.experience") }}</h5>
                            <p>{!! $talent->experience_pr ?? __("talents/index.talent_description") !!}</p>
                        </div>
                        <div class="col-md-12">
                            <h5 class="talent-body-heading">{{__("talents/index.qualification")}}</h5>
                            @if($talent->qualifications)
                                {!! $talent->qualifications !!}
                            @else
                                <ul>
                                    <li>{{ __("talents/index.talent_q1") }}</li>
                                    <li>{{ __("talents/index.talent_q2") }}</li>
                                    <li>{{ __("talents/index.talent_q3") }}</li>
                                </ul>
                            @endif
                        </div>
                        <div class="talent-featured-section">
                            <div class="row justify-content-evenly">
                                <div class="col-md-6 col-lg-5">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/index.monthly_income") }}: </div>
                                        <div class="col-lg-6 col-xl-7 feature-text">{{ $talent->salary_range ?? '' }} {{ __('talents/index.currency_text') }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/index.nationality") }}: </div>
                                        <div class="col-lg-6 col-xl-7 feature-text">{{ $talent->user->nationality ?? '' }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/index.nearest_station") }}: </div>
                                        {{-- The same fake station the resume modal used to
                                             print. `??` only catches null, and the column
                                             holds '' far more often, so most cards showed a
                                             blank — but any candidate whose column was null
                                             was given a real station in Chiba as if they had
                                             said so. A recruiter filtering by location would
                                             have acted on it. --}}
                                        <div class="col-lg-6 col-xl-7 feature-text">{{ filled($talent->user->nearest_station_prefecture) ? $talent->user->nearest_station_prefecture : '—' }}</div>
                                    </div>
                                    {{-- An "Operations" row used to sit here printing exactly the
                                         expression the Availability heading above already prints.
                                         The same value under two labels, one of them wrong —
                                         運用 is not availability — and there is no operations
                                         column on talents to put here instead. --}}
                                </div>
                                <div class="col-md-6 col-lg-5">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/registration.affiliation") }}: </div>
                                        <div class="col-lg-6 col-xl-7 feature-text">{{ $talent->affiliation }}</div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/index.type_of_contract") }}: </div>
                                        <div class="col-lg-6 col-xl-7 feature-text">
                                            {{ $talent->myContract!==null ? __("talents/index.{$talent->myContract}"): '' }}
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-lg-6 col-xl-5 feature-head">{{ __("talents/index.preferred_location") }}: </div>
                                        <div class="col-lg-6 col-xl-7 feature-text">
                                            {{-- An em dash rather than nothing, as the station
                                                 row does: a label with empty space beside it
                                                 reads as a rendering fault, not as "unstated". --}}
                                            @forelse($talent->locations as $location)
                                                {{ $location->title ?? '' }}@if(!$loop->last), @endif
                                            @empty
                                                —
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                {{-- The actions moved to the kebab in the card's top-right
                                     corner; nothing is left to put in this column. --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div>
            {{ $talents->links() }}
        </div>
    @else
        <div class="">
            <p>{!! __("talents/show.search_error") !!}</p>
        </div>
    @endif
    <livewire:talents.talent-modal />
</div>
@push('scripts')
    <script>
        Livewire.on('openModal', () => {
            const resumeModal = new bootstrap.Modal(document.getElementById('talentResumeModal'));
            resumeModal.show();
        });

        Livewire.on('closeModal', () => {
            const resumeModal = new bootstrap.Modal(document.getElementById('talentResumeModal'));
            resumeModal.hide();
        });

    </script>
@endpush
