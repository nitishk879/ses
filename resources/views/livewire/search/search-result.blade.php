<div>
    <div class="d-flex w-100 justify-content-between my-3">
        @if($search)
            <h2 class="search-keyword">{{ __("talents/index.showing_results_for") }}: <span>"{{ $search ?? __("talents/index.search_keyword") }}"</span></h2>
        @endif
        <div class="search-sort">
            <div class="input-group mb-3">
                <label class="input-group-text" for="inputGroupSelect01">{{ __("talents/index.sort_by") }}</label>
                <select class="form-select" id="inputGroupSelect01">
                    <option selected>{{ __("talents/index.recent_listings") }}</option>
                    <option value="1" wire:click="sortBy('firstname')">First Name</option>
                    <option value="2" wire:click="sortBy('email')">Email</option>
                    <option value="3" wire:click="sortBy('country)">Country</option>
                </select>
            </div>
        </div>
    </div>
    <div>
        <table class="table table-hover">
            <thead>
            <tr>
                <th wire:click="sortBy('firstname')" style="cursor: pointer;">Name
                    @if($sortBy == 'firstname')
                        <i class="fa-solid fa-chevron-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </th>
                <th wire:click="sortBy('email')" style="cursor: pointer;">Email
                    @if($sortBy == 'email')
                        <i class="fa-solid fa-chevron-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </th>
                <th wire:click="sortBy('country')" style="cursor: pointer;">Role
                    @if($sortBy == 'country')
                        <i class="fa-solid fa-chevron-{{ $sortDirection == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->country }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>

</div>
