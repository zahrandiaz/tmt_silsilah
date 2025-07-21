<ul>
    @foreach ($people as $person)
        <li>
            <div class="person-block">
                <span>
                    @if($person->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                    <strong>{{ $person->name }}</strong>
                </span>
                
                {{-- Tampilkan semua pasangan (istri/suami) --}}
                @foreach ($person->partners() as $partner)
                    <span class="partner">
                        &
                        @if($partner->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                        {{ $partner->name }}
                    </span>
                @endforeach
            </div>
            
            {{-- Jika punya anak, panggil view ini lagi untuk anak-anaknya --}}
            @if ($person->children()->isNotEmpty())
                @include('partials.person-node', ['people' => $person->children()])
            @endif
        </li>
    @endforeach
</ul>