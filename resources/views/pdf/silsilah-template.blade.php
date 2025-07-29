<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Silsilah - {{ $rootPerson->name }}</title>
    <style>
        @page { margin: 20mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.4; color: #333; }
        h1 { font-size: 18pt; text-align: center; margin-bottom: 5px; }
        h2 { font-size: 11pt; font-weight: normal; text-align: center; margin-top: 0; margin-bottom: 1.5em; }
        hr { border: 0; border-top: 1px solid #ccc; margin-bottom: 1.5em; }
        .report-line { margin-bottom: 0.8em; page-break-inside: avoid; }
        .person-block { display: inline-block; vertical-align: top; }
        .details { font-size: 8pt; color: #555; }
        .person-name { font-weight: bold; }
        .spouse-line { font-style: italic; }
        .no-spouse-separator { font-size: 8pt; font-style: italic; color: #777; }

        /* == AWAL PERUBAHAN GAYA FOTO == */

        /* Di sinilah Anda bisa mengatur ukuran foto di masa depan */
        .photo {
            width: 55px;  /* <-- Ubah nilai ini untuk lebar */
            height: 73px; /* <-- Ubah nilai ini untuk tinggi */
            object-fit: cover;
            border-radius: 4px; /* <-- Hapus baris ini untuk sudut tajam, atau kecilkan nilainya */
            border: 1px solid #ddd;
            margin-right: 12px;
            vertical-align: middle;
        }
        .no-photo {
            width: 55px;  /* <-- Samakan dengan lebar .photo */
            height: 73px; /* <-- Samakan dengan tinggi .photo */
            background-color: #eee;
            border-radius: 4px; /* <-- Samakan dengan .photo */
            border: 1px solid #ddd;
            margin-right: 12px;
            display: inline-block; 
            text-align: center;
            font-weight: bold; 
            color: #aaa; 
            vertical-align: middle;
        }
        /* Mengatur line-height untuk placeholder '?' agar di tengah */
        .no-photo::before {
            content: '?';
            line-height: 73px; /* <-- Samakan dengan tinggi .no-photo */
        }

        /* == AKHIR PERUBAHAN GAYA FOTO == */
    </style>
</head>
<body>
    <h1>Laporan Silsilah Keturunan</h1>
    <h2>Dimulai dari: {{ $rootPerson->name }}</h2>
    <hr>

    @foreach($reportLines as $line)
        @php
            $indent = $line['level'] * 25; // 25px indentasi per level
        @endphp

        <div class="report-line" style="padding-left: {{ $indent }}px;">
            <table>
                <tr>
                    @if($withPhotos)
                        <td style="width: 60px;">
                            @if($line['type'] === 'person' && isset($line['photo_path']))
                                <img src="{{ storage_path('app/public/' . $line['photo_path']) }}" class="photo">
                            @elseif($line['type'] === 'spouse' && isset($line['photo_path']))
                                <img src="{{ storage_path('app/public/' . $line['photo_path']) }}" class="photo">
                            @elseif($line['type'] !== 'no_spouse_separator')
                                <span class="no-photo">?</span>
                            @endif
                        </td>
                    @endif
                    <td>
                        @if($line['type'] === 'person')
                            <div class="person-block">
                                <span class="person-name">{{ $line['number'] }}. {{ $line['person']->name }}</span>
                                <div class="details">
                                    Lahir: {{ $line['person']->birth_date_formatted }}
                                    @if($line['person']->death_date)
                                        | Wafat: {{ $line['person']->death_date_formatted }}
                                    @endif
                                </div>
                            </div>
                        @elseif($line['type'] === 'spouse')
                             <div class="person-block spouse-line">
                                & menikah dengan <span class="person-name">{{ $line['spouse']->name }}</span>
                                <div class="details">
                                    Lahir: {{ $line['spouse']->birth_date_formatted }}
                                    @if($line['spouse']->death_date)
                                        | Wafat: {{ $line['spouse']->death_date_formatted }}
                                    @endif
                                </div>
                            </div>
                        @elseif($line['type'] === 'no_spouse_separator')
                            <div class="no-spouse-separator">Keturunan (tanpa pasangan tercatat):</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

</body>
</html>