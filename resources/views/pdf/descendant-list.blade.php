{{-- resources/views/pdf/descendant-list.blade.php --}}
<ul>
    @foreach($children as $child)
        <li>
            <div class="person-details">
                - <strong>{{ $child->name }}</strong>
                @foreach ($child->spouses() as $spouse)
                    & <strong>{{ $spouse->name }}</strong>
                @endforeach
            </div>
            <div class="date-info">
                (Lahir: {{ $child->birth_date_formatted }})
                @if($child->death_date)
                    (Wafat: {{ $child->death_date_formatted }})
                @endif
            </div>

            {{-- Rekursi untuk generasi berikutnya dengan cara yang efisien --}}
            @php
                $grandChildren = $all_people->filter(function ($person) use ($child) {
                    $father = $person->father();
                    $mother = $person->mother();
                    return ($father && $father->id == $child->id) || ($mother && $mother->id == $child->id);
                });
            @endphp
            
            @if($grandChildren->isNotEmpty())
                @include('pdf.descendant-list', ['children' => $grandChildren, 'all_people' => $all_people])
            @endif
        </li>
    @endforeach
</ul>