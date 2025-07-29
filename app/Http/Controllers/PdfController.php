<?php

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
        // 1. Validasi input, tambahkan 'with_photos'
        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'generations' => 'required|integer|min:0',
            'with_photos' => 'nullable|boolean',
        ]);

        // 2. Ambil data yang dibutuhkan
        $rootPerson = Person::findOrFail($validated['person_id']);
        $maxGenerations = (int) $validated['generations'];
        $withPhotos = (bool) ($validated['with_photos'] ?? false);

        // 3. Panggil method di model untuk membangun data laporan
        $reportLines = $rootPerson->generateIndentedReport($maxGenerations, $withPhotos);

        // 4. Siapkan data untuk dikirim ke view
        $data = [
            'rootPerson' => $rootPerson,
            'reportLines' => $reportLines,
            'withPhotos' => $withPhotos,
        ];

        // 5. Gunakan library DomPDF untuk membuat PDF
        $pdf = Pdf::loadView('pdf.silsilah-template', $data);

        // 6. Kirim PDF ke browser untuk diunduh
        return $pdf->download('laporan-silsilah-' . \Illuminate\Support\Str::slug($rootPerson->name) . '.pdf');
    }
}