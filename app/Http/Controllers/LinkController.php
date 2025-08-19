<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;

class LinkController extends Controller
{
    public function index()
    {
        $links = Link::all();
        return view('admin.crud.links.index', compact('links'));
    }

    public function create()
    {
        return view('admin.crud.links.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'type' => 'required|in:brand,link,social',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'icon_url' => 'nullable|string|max:255',
        ]);

        $icon = null;

        // prioritas file upload
        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            $filename = time() . '_' . $file->getClientOriginalName();

            // simpan file langsung ke public/icons
            $file->move(public_path('icons'), $filename);

            // simpan path relatif ke folder public di DB
            $icon = 'icons/' . $filename;
        } elseif ($request->icon_url) {
            $icon = $request->icon_url;
        }

        Link::create([
            'label' => $request->label,
            'url' => $request->url,
            'type' => $request->type,
            'icon' => $icon,
        ]);

        return redirect()->route('links.index')->with('success', 'Link berhasil ditambahkan');
    }




    public function edit(Link $link)
    {
        return view('admin.crud.links.edit', compact('link'));
    }

    public function update(Request $request, Link $link)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'icon_url' => 'nullable|string|max:255',
        ]);

        // validasi hanya satu icon yang boleh diisi
        if ($request->hasFile('icon_file') && $request->icon_url) {
            return back()->withInput()->withErrors(['icon_file' => 'Hanya boleh pilih salah satu: file atau URL']);
        }

        $icon = $link->icon; // default icon lama

        // handle file upload baru
        if ($request->hasFile('icon_file')) {
            if ($link->icon && file_exists(public_path($link->icon)) && !filter_var($link->icon, FILTER_VALIDATE_URL)) {
                unlink(public_path($link->icon));
            }

            $file = $request->file('icon_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('icons'), $filename);
            $icon = 'icons/' . $filename;
        } elseif ($request->icon_url) {
            $icon = $request->icon_url;
            if ($link->icon && file_exists(public_path($link->icon)) && !filter_var($link->icon, FILTER_VALIDATE_URL)) {
                unlink(public_path($link->icon));
            }
        } elseif (!$request->icon_file && !$request->icon_url) {
            if ($link->icon && file_exists(public_path($link->icon)) && !filter_var($link->icon, FILTER_VALIDATE_URL)) {
                unlink(public_path($link->icon));
            }
            $icon = null;
        }

        $link->update([
            'label' => $request->label,
            'url' => $request->url,
            'icon' => $icon,
        ]);

        return redirect()->route('links.index')->with('success', 'Link berhasil diperbarui');
    }


    public function destroy(Link $link)
    {
        // hapus file icon kalau ada dan bukan URL
        if ($link->icon && file_exists(public_path($link->icon)) && !filter_var($link->icon, FILTER_VALIDATE_URL)) {
            unlink(public_path($link->icon));
        }

        $link->delete();

        return redirect()->route('links.index')->with('success', 'Link berhasil dihapus');
    }
}
