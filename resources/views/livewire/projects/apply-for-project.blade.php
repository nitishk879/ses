<div class="d-flex flex-column">
    <a href="" class="btn btn-primary">
        {{ __("projects/show.apply_to_project") }}
    </a>
    <div class="d-block align-items-center">
        <div class="list-group">
            @foreach($talents as $talent)
                <li class="list-group-item">{{ $talent->user->name }} - {{ $talent->score }}</li>
            @endforeach
        </div>
    </div>
</div>
