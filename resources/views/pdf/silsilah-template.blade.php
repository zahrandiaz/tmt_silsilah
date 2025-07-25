{{-- resources/views/pdf/silsilah-template.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Silsilah Keluarga {{ $rootPerson->name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; line-height: 1.6; }
        h1, h2 { text-align: center; font-size: 1.2em; }
        ul { list-style-type: none; padding-left: 20px; border-left: 1px solid #ccc; }
        li { margin-bottom: 8px; }
        .person-details { font-size: 14px; }
        .date-info { color: #555; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Silsilah Keluarga</h1>
    <h2>Dimulai dari: {{ $rootPerson->name }}</h2>
    <hr>
    
    <ul>
        <li>
            <div class="person-details">
                <strong>{{ $rootPerson->name }}</strong>
                @foreach ($rootPerson->spouses() as $spouse)
                    & <strong>{{ $spouse->name }}</strong>
                @endforeach
            </div>
            <div class="date-info">
                (Lahir: {{ $rootPerson->birth_date_formatted }})
                @if($rootPerson->death_date)
                    (Wafat: {{ $rootPerson->death_date_formatted }})
                @endif
            </div>

            {{-- Mengambil anak-anak dari koleksi yang sudah disiapkan --}}
            @php
                $children = $all_people->filter(function ($person) use ($rootPerson) {
                    $father = $person->father();
                    $mother = $person->mother();
                    return ($father && $father->id == $rootPerson->id) || ($mother && $mother->id == $rootPerson->id);
                });
            @endphp

            @if($children->isNotEmpty())
                @include('pdf.descendant-list', ['children' => $children, 'all_people' => $all_people])
            @endif
        </li>
    </ul>

</body>
</html>