{{-- resources/views/partials/descendant-node.blade.php --}}

{{-- 1. Mengembalikan Props untuk level, dan definisikan Max Level --}}
@props(['person', 'spouse' => null, 'children' => null, 'level' => 0])

@php
    $maxLevel = 2; // Atur kedalaman generasi maksimal yang ingin ditampilkan di sini
@endphp

<ul>
    <li>
        {{-- TAMPILKAN KEPALA KELUARGA (PERSON UTAMA & PASANGANNYA) --}}
        <div class="person-block">
            <span>
                @if($person->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                <strong>
                    <a href="{{ route('people.show', $person->id) }}" class="text-blue-600 hover:underline">
                        {{ $person->name }}
                    </a>
                </strong>
            </span>
            @if($spouse)
                <span class="partner">& @if($spouse->gender == 'Laki-laki') ♂️ @else ♀️ @endif
                    <a href="{{ route('people.show', $spouse->id) }}" class="text-blue-600 hover:underline">
                        {{ $spouse->name }}
                    </a>
                </span>
            @endif
        </div>

        {{-- 2. Tambahkan Pengecekan Level SEBELUM melakukan rekursi --}}
        @if ($level < $maxLevel && $children && $children->isNotEmpty())
            <ul>
                @foreach ($children as $child)
                    {{-- 3. Logika baru untuk menangani SEMUA kasus anak --}}
                    @if ($child->spouses()->isNotEmpty())
                        {{-- Jika anak punya pasangan, loop untuk setiap unit keluarga baru --}}
                        @foreach ($child->spouses() as $childsSpouse)
                            @include('partials.descendant-node', [
                                'person'   => $child,
                                'spouse'   => $childsSpouse,
                                'children' => $child->childrenWith($childsSpouse),
                                'level'    => $level + 1, // Naikkan level
                            ])
                        @endforeach
                    @else
                        {{-- Jika anak tidak punya pasangan, tampilkan dia sebagai "kepala keluarga" tunggal --}}
                        @include('partials.descendant-node', [
                            'person'   => $child,
                            'spouse'   => null,
                            'children' => $child->allChildren(), // Ambil semua anaknya (jika ada)
                            'level'    => $level + 1, // Naikkan level
                        ])
                    @endif
                @endforeach
            </ul>
        @endif
    </li>
</ul>