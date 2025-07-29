<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Silsilah - {{ $rootPerson->name }}</title>
    <style>
        @page { margin: 25mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; }
        h1, h2 { text-align: center; margin: 0; padding: 0; }
        h1 { font-size: 16pt; }
        h2 { font-size: 12pt; font-weight: normal; margin-bottom: 1.5em; }
        hr { border: 0; border-top: 1px solid #ccc; margin: 1em 0; }
        .report-line { margin-bottom: 0.5em; }
        .person-name { font-weight: bold; }
        .details { color: #333; }
        .spouse-line { font-style: italic; }
    </style>
</head>
<body>
    <h1>Laporan Silsilah Keturunan</h1>
    <h2>Dimulai dari: {{ $rootPerson->name }}</h2>
    <hr>

    @foreach($reportLines as $line)
        @php
            $indent = $line['level'] * 20; // 20px indentasi per level
        @endphp

        <div class="report-line" style="padding-left: {{ $indent }}px;">
            @if($line['type'] === 'person')
                <span class="person-name">{{ $line['counter'] }}. {{ $line['person']->name }}</span>
                <span class="details">
                    (Lahir: {{ $line['person']->birth_date_formatted }}
                    @if($line['person']->death_date)
                        , Wafat: {{ $line['person']->death_date_formatted }}
                    @endif
                    )
                </span>
            @elseif($line['type'] === 'spouse')
                <span class="spouse-line">
                    &nbsp; &nbsp; & menikah dengan <span class="person-name">{{ $line['spouse']->name }}</span>
                    <span class="details">
                        (Lahir: {{ $line['spouse']->birth_date_formatted }}
                        @if($line['spouse']->death_date)
                            , Wafat: {{ $line['spouse']->death_date_formatted }}
                        @endif
                        )
                    </span>
                </span>
            @endif
        </div>
    @endforeach

</body>
</html>