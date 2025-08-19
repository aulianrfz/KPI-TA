<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{

    public function manage()
    {
        return view('superadmin.manage_admin');
    }

    public function listAll()
    {
        $admins = Admin::with('user')->where('status', 1)->get();
        return view('superadmin.list_admin', compact('admins'));
    }

    public function listAdmin()
    {
        $admins = Admin::with('user')->where('status', 0)->get();
        return view('superadmin.approval', compact('admins'));
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return back()->with('success', 'Tidak ada admin yang dipilih.');
        }

        foreach ($ids as $id) {
            $admin = Admin::find($id);
            if (!$admin)
                continue;

            if ($action === 'approve') {
                $admin->status = 1;
            } elseif ($action === 'reject') {
                $admin->status = 2;
            }

            $admin->save();
        }

        return back()->with('success', 'Aksi berhasil dilakukan.');
    }


    public function approveAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->is_active = true;
        $admin->save();

        return back()->with('success', 'Admin berhasil diapprove.');
    }

    public function rejectAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;
        $admin->delete();
        $user->delete();

        return back()->with('success', 'Admin ditolak dan dihapus.');
    }

    // Menampilkan form tambah admin
    public function create()
    {
        return view('superadmin.create_admin');
    }

    // Menyimpan data admin baru
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'jabatan' => 'required|string|max:100',
        ]);

        // Simpan user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // pastikan ini sesuai sistem
        ]);

        // Simpan admin
        Admin::create([
            'user_id' => $user->id,
            'jabatan' => $request->jabatan,
            'status' => 1,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.admin.list')->with('success', 'Admin berhasil ditambahkan.');
    }

    // Menampilkan form edit admin
    public function edit($id)
    {
        $admin = Admin::with('user')->findOrFail($id);
        return view('superadmin.edit_admin', compact('admin'));
    }

    // Menyimpan perubahan admin
    public function update(Request $request, $id)
    {
        $admin = Admin::with('user')->findOrFail($id);
        $user = $admin->user;

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'jabatan' => 'required|string|max:100',
            'is_active' => 'required|in:0,1',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $admin->update([
            'jabatan' => $request->jabatan,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('superadmin.admin.list')->with('success', 'Admin berhasil diperbarui.');
    }


    // Menghapus admin dan user terkait
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $user = $admin->user;

        $admin->delete();
        $user->delete();

        return redirect()->route('superadmin.admin.list')->with('success', 'Admin berhasil dihapus.');
    }

}