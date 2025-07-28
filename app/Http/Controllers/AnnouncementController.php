<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * Menampilkan daftar semua pengumuman.
     */
    public function index()
    {
        $announcements = Announcement::latest()->paginate(10);
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Menampilkan form untuk membuat pengumuman baru.
     */
    public function create()
    {
        return view('admin.announcements.create');
    }

    /**
     * Menyimpan pengumuman baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success',
        ]);

        DB::transaction(function () use ($request) {
            // Jika admin memilih untuk mengaktifkan pengumuman ini,
            // nonaktifkan semua pengumuman lainnya terlebih dahulu.
            if ($request->has('is_active')) {
                Announcement::query()->update(['is_active' => false]);
            }

            Announcement::create([
                'content' => $request->content,
                'type' => $request->type,
                'is_active' => $request->has('is_active'),
            ]);
        });

        return redirect()->route('announcements.index')
                         ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit pengumuman.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Memperbarui pengumuman di database.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success',
        ]);

        DB::transaction(function () use ($request, $announcement) {
            // Jika admin memilih untuk mengaktifkan pengumuman ini,
            // nonaktifkan semua pengumuman lainnya terlebih dahulu.
            if ($request->has('is_active')) {
                Announcement::where('id', '!=', $announcement->id)->update(['is_active' => false]);
            }

            $announcement->update([
                'content' => $request->content,
                'type' => $request->type,
                'is_active' => $request->has('is_active'),
            ]);
        });

        return redirect()->route('announcements.index')
                         ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Menghapus pengumuman dari database.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')
                         ->with('success', 'Pengumuman berhasil dihapus.');
    }
}