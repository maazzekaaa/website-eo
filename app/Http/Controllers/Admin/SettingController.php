<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['setting' => Setting::current()]);
    }

    public function update(Request $request, ImageService $images, WhatsAppService $whatsApp): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:150'],
            'wa_number' => ['required', 'string', 'max:20', 'regex:/^[\d\s+\-().Xx]+$/'],
            'npwp' => ['nullable', 'string', 'max:40'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'bank_account' => ['nullable', 'string', 'max:40'],
            'bank_holder' => ['nullable', 'string', 'max:150'],
            'tax_rate' => ['required', 'integer', 'min:0', 'max:100'],
            'hero_title' => ['nullable', 'string', 'max:200'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'about' => ['nullable', 'string', 'max:5000'],
            'opening_hours' => ['nullable', 'string', 'max:150'],
            'map_embed' => ['nullable', 'url:https', 'starts_with:https://www.google.com/maps/embed', 'max:1000'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255'],
            'tiktok_url' => ['nullable', 'url:http,https', 'max:255'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:255'],
            'hero' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($data['hero'], $data['signature']);

        $setting = Setting::query()->firstOrNew();

        // Placeholder 62XXXXXXXXXX boleh tetap ada sampai nomor asli diketahui.
        if (! preg_match('/x/i', $data['wa_number'])) {
            $data['wa_number'] = $whatsApp->normalize($data['wa_number']);
        }
        $data['tax_enabled'] = $request->boolean('tax_enabled');

        foreach (['hero' => ['hero_image', 'site'], 'signature' => ['signature_path', 'site']] as $field => [$column, $dir]) {
            if ($request->file($field)) {
                // Simpan yang baru dulu: bila gagal diolah, gambar lama tetap utuh.
                $data[$column] = $images->store($request->file($field), $dir);
                $images->delete($setting->{$column});
            }
        }

        $setting->fill($data)->save();

        return back()->with('toast', 'Pengaturan disimpan.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth('admin')->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();

        return back()->with('toast', 'Kata sandi diganti.');
    }
}
