<div>
    <div class="row mb-4 mt-5">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search by name" wire:model.live="search">
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search by email" wire:model.live="email">
        </div>
        <div class="col-md-4">
            <select class="form-select" wire:model="role">
                <option value="">Select Role</option>
                <option value="japan">Japan</option>
                <option value="india">India</option>
            </select>
        </div>
    </div>
</div>
