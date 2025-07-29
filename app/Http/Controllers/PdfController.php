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
        // 1. Validasi input
        $request->validate([
            'person_id' => 'required|exists:people,id',
            'generations' => 'required|integer|min:0',
        ]);

        // 2. Ambil data yang dibutuhkan
        $rootPerson = Person::findOrFail($request->input('person_id'));
        $maxGenerations = (int) $request->input('generations');

        // 3. Panggil method baru di model untuk membangun data laporan
        // (Method ini akan kita buat di langkah selanjutnya)
        $reportLines = $rootPerson->generateIndentedReport($maxGenerations);

        // 4. Siapkan data untuk dikirim ke view
        $data = [
            'rootPerson' => $rootPerson,
            'reportLines' => $reportLines,
        ];

        // 5. Gunakan library DomPDF untuk membuat PDF
        $pdf = Pdf::loadView('pdf.silsilah-template', $data);

        // 6. Kirim PDF ke browser untuk diunduh
        return $pdf->download('laporan-silsilah-' . \Illuminate\Support\Str::slug($rootPerson->name) . '.pdf');
    }
}