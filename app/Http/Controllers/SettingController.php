<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $users = User::orderBy('name')->get();

        return view('settings.index', compact('settings', 'users'));
    }

    public function updateStore(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string',
            'store_phone' => 'required|string|max:20',
            'store_whatsapp' => 'nullable|string|max:20',
            'store_npwp' => 'nullable|string|max:30',
            'store_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fields = ['store_name', 'store_address', 'store_phone', 'store_whatsapp', 'store_npwp'];

        foreach ($fields as $field) {
            Setting::setValue($field, $request->input($field));
        }

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::setValue('store_logo', $path);
        }

        return redirect()->route('settings.index')->with('success', 'Profil toko berhasil diperbarui!');
    }

    public function updateTax(Request $request)
    {
        $request->validate([
            'tax_enabled' => 'nullable|boolean',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'max_discount' => 'nullable|numeric|min:0|max:100',
            'price_rounding' => 'nullable|in:0,100,500,1000',
            'transaction_format' => 'nullable|string',
            'reset_number' => 'nullable|in:daily,monthly,yearly,never',
            'printer_size' => 'nullable|in:58mm,80mm',
            'printer_margin' => 'nullable|numeric|min:0',
            'auto_print' => 'nullable|boolean',
        ]);

        $fields = [
            'tax_enabled', 'tax_percent', 'max_discount', 'price_rounding',
            'transaction_format', 'reset_number', 'printer_size', 'printer_margin', 'auto_print'
        ];

        foreach ($fields as $field) {
            $value = $request->input($field, '0');
            Setting::setValue($field, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan pajak & struk berhasil diperbarui!');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,kasir',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect()->route('settings.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,kasir',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('settings.index')->with('success', 'Status pengguna berhasil diubah!');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('settings.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}
