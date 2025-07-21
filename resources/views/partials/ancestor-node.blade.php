<ul>
    <li>
        <div class="person-block">
            <strong>&rarr; {{ $person->name }}</strong>
        </div>

        @if($person->father() || $person->mother())
            <ul>
            @if($person->father())
                @include('partials.ancestor-node', ['person' => $person->father()])
            @endif
            @if($person->mother())
                @include('partials.ancestor-node', ['person' => $person->mother()])
            @endif
            </ul>
        @endif
    </li>
</ul>