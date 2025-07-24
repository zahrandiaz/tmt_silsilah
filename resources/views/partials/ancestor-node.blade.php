<ul>
    <li>
        <div class="person-block">
            <strong>&rarr;
                <a href="{{ route('people.show', $person->id) }}" class="text-blue-600 hover:underline">
                    {{ $person->name }}
                </a>
            </strong>
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