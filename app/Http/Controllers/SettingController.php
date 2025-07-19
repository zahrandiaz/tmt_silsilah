<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $isPublic = Setting::where('key', 'is_public')->first()->value ?? '0';
        return view('settings.index', compact('isPublic'));
    }

    public function update(Request $request)
    {
        $isPublic = $request->has('is_public') ? '1' : '0';

        Setting::updateOrCreate(
            ['key' => 'is_public'],
            ['value' => $isPublic]
        );

        return redirect()->route('settings.index')->with('success', 'Pengaturan privasi berhasil diperbarui.');
    }
}