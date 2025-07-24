@props(['person', 'level' => 0])
@php
    $maxLevel = 2; 
@endphp

<ul>
    <li>
        <div class="person-block">
            <span>
                @if($person->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                <strong>
                    <a href="{{ route('people.show', $person->id) }}" class="text-blue-600 hover:underline">
                        {{ $person->name }}
                    </a>
                </strong>
            </span>
            @foreach ($person->spouses() as $spouse)
                <span class="partner">& @if($spouse->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                    <a href="{{ route('people.show', $spouse->id) }}" class="text-blue-600 hover:underline">
                        {{ $spouse->name }}
                    </a>
                </span>
            @endforeach
        </div>
        
        @if ($level < $maxLevel && $person->allChildren()->isNotEmpty())
            <ul>
                @foreach ($person->allChildren() as $child)
                    @include('partials.descendant-node', ['person' => $child, 'level' => $level + 1])
                @endforeach
            </ul>
        @endif
    </li>
</ul>