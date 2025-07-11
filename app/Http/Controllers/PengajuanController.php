<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengajuan::where('user_id', Auth::id());

        if ($request->filled('filter')) {
            $query->where('jenis', $request->filter);
        }

        $pengajuans = $query->latest()->paginate(10);
        $jenisList = Pengajuan::distinct()->pluck('jenis');

        return view('user.pengajuan.index', compact('pengajuans', 'jenisList'));
    }

    public function create()
    {
        return view('user.pengajuan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Pengajuan::create([
            'user_id' => Auth::id(),
            'jenis' => $request->jenis,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil diajukan.');
    }

    public function adminIndex(Request $request)
    {
        $query = Pengajuan::with('user')
                    ->where('status', 'Menunggu');

        if ($request->filled('filter')) {
            $query->where('jenis', $request->filter);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $pengajuans = $query->latest()->paginate(10);
        $jenisList = Pengajuan::distinct()->pluck('jenis');

        return view('admin.pengajuan.index', compact('pengajuans', 'jenisList'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Menunggu,Disetujui,Ditolak',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->status = $request->status;
        $pengajuan->save();

        return redirect()->back()->with('success', 'Status pengajuan diperbarui.');
    }

    public function show($id)
    {
        $pengajuan = Pengajuan::with('user')->findOrFail($id);
        return view('admin.pengajuan.show', compact('pengajuan'));
    }

}
