<div class="d-flex flex-column">
    <div class="d-block align-items-center">
        @if(count($talents) >=1)
            <form method="post" action="{{ route("talent.invite", $project) }}" class="list-group">
                @csrf
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __("talents/show.check") }}</th>
                        <th scope="col">Name</th>
                        <th scope="col">Skills </th>
                        <th scope="col">Locations </th>
                        <th scope="col">Experience</th>
                        <th scope="col">Language </th>
                        <th scope="col">Salary </th>
                        <th scope="col">Domain </th>
                        <th scope="col">Work Location</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="">
                        <th scope="row"> </th>
                        <th scope="row"><input type="checkbox" class="form-check-input" id="selectedCheckboxes"></th>
                        <th scope="row"> </th>
                        <td>
                            @foreach($project->subCategories as $sub)
                                <span class="badge bg-primary text-white">{{ $sub->title }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach($project->locations as $location)
                                <span class="badge bg-primary text-white">{{ $location->title }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach(json_decode($project->experience) as $exp)
                                {{ $exp }}
                            @endforeach
                        </td>
                        <td>
                            @foreach($project->languages as $lang)
                                {{ $lang }}
                            @endforeach
                        </td>
                        <td>{{ $project->salary_range }}</td>
                        <td>
                            @foreach($project->industries as $industry)
                                <span class="badge bg-primary">{{ $industry->title }}</span>
                            @endforeach
                        </td>
                        <td>
                            @foreach($project->work_location_prefer as $w_loc)
                                <span class="badge bg-primary">{{ \App\Enums\WorkLocationEnum::toName($w_loc) }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @foreach($talents as $talent)
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <th scope="row">
                                <input class="form-check-input" type="checkbox" name="talents[]" wire:model="selectedTalents" id="checkbox{{$talent->id}}" value="{{ $talent->id }}" aria-label="...">
                            </th>
                            <th colspan="">{{ $talent->user->name }} - {{ ($talent->score) }}%</th>
                            <td>
                                @foreach($talent->subCategories as $skill)
                                    <span class="badge bg-info text-white">{{ $skill->title }}</span>
                                @endforeach
                                ({{ $talent->score_detail['skills'] ?? '' }})
                            </td>
                            <td>
                                @foreach($talent->locations as $loc)
                                    <span class="badge bg-info text-white">{{ $loc->title }}</span>
                                @endforeach
                                ({{ $talent->score_detail['location'] ? round($talent->score_detail['location'], 2) : '' }})
                            </td>
                            <td>{{ $talent->experience }} ({{ $talent->score_detail['experience'] ?? '' }})</td>
                            <td>
                                @if($talent->user->languages)
                                    @foreach($talent->user->languages as $lang)
                                        {{ $lang }}
                                    @endforeach
                                    ({{ $talent->score_detail['languages'] ?? '' }})
                                @endif
                            </td>
                            <td>{{ $talent->salary_range }} ({{ round($talent->score_detail['salary'], 2) ?? '' }})</td>
                            <td>
                                @foreach($talent->industries as $industry)
                                    <span class="badge bg-info">{{ $industry->title }}</span>
                                @endforeach
                                ({{ $talent->score_detail['domain'] ?? '' }})
                            </td>
                            <td>
                                {{--                                {{ ($talent->work_location_prefer) }}--}}
                                @foreach($talent->work_location_prefer as $work_location)
                                    <span class="badge bg-info text-white"> {{ \App\Enums\WorkLocationEnum::toName($work_location) }}</span>
                                @endforeach
                                ({{ $talent->score_detail['work_location'] ?? '' }})
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="">
                    <div class="mb-3">
                        <label for="invitationCover" class="form-label">Invitation Letter</label>
                        <textarea class="form-control my-3" name="invitation_letter" id="invitationCover">{!! old('invitation_letter') ?? '' !!}</textarea>
                    </div>

                    <button type="submit"
{{--                            wire:click="inviteTalent"--}}
                            class="btn btn-primary"
{{--                        {{ count($selectedTalents) == 0 ? 'disabled' : '' }}--}}
                    >{{ __("common/index.apply_now") }}</button>
                </div>
            </form>
        @else
            <p class="text-danger">Not even a single talent seem eligible for the project.</p>
        @endif
    </div>
    @push('scripts')
        <script>
            // Select All Functionality
            document.getElementById('selectedCheckboxes').addEventListener('change', function(e) {
                const checkboxes = document.querySelectorAll('.form-check-input');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
            // Uncheck "Select All" if any item is unchecked
            const itemCheckboxes = document.querySelectorAll('.form-check-input');
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        document.getElementById('selectedCheckboxes').checked = false;
                    } else if (Array.from(itemCheckboxes).every(cb => cb.checked)) {
                        document.getElementById('selectedCheckboxes').checked = true;
                    }
                });
            });
        </script>
    @endpush
</div>
