<?php
// app/Http/Controllers/PdfController.php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /**
     * Menampilkan form untuk konfigurasi ekspor PDF.
     */
    public function showExportForm()
    {
        $people = Person::orderBy('name')->get();
        return view('pdf.export-form', compact('people'));
    }

    /**
     * Menghasilkan dan mengirimkan file PDF silsilah.
     */
    public function generatePdf(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'person_id' => 'required|exists:people,id',
            'generations' => 'required|integer|min:0',
        ]);

        // 2. Ambil data root tanpa eager loading 'spouses' yang bermasalah
        $rootPerson = Person::findOrFail($request->input('person_id'));
        $maxGenerations = (int) $request->input('generations');

        // 3. Panggil method di model untuk mendapatkan semua model keturunan
        $descendantsCollection = $rootPerson->getDescendantsWithSpouses($maxGenerations - 1);

        // 4. Ambil semua ID dari koleksi keturunan
        $descendantIds = $descendantsCollection->pluck('id');
        
        // 5. Lakukan SATU query untuk mengambil semua data, tapi tanpa ->with()
        $tree = Person::whereIn('id', $descendantIds)->get();
        
        // 6. Siapkan data untuk dikirim ke view
        $data = [
            'rootPerson' => $rootPerson,
            'tree'       => $tree,
            'all_people' => $tree->push($rootPerson)->keyBy('id') // Gabungkan semua orang dan buat lookup table
        ];

        // 7. Gunakan library DomPDF untuk membuat PDF
        $pdf = Pdf::loadView('pdf.silsilah-template', $data);

        // 8. Kirim PDF ke browser untuk diunduh
        return $pdf->download('silsilah-' . \Illuminate\Support\Str::slug($rootPerson->name) . '.pdf');
    }
}