<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kuisioner;
use App\Models\Event;
use App\Mail\QrCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class KuisionerController extends Controller
{

public function selectEvent()
{
    $events = Event::all();
    return view('admin.kuisioner.event', compact('events'));
}

public function byEvent($id)
{
    $event = Event::findOrFail($id);
    $kuisioners = Kuisioner::where('event_id', $id)->get();
    return view('admin.kuisioner.index', compact('kuisioners', 'event'));
}

public function create($eventId)
{
    $event = Event::findOrFail($eventId);
    return view('admin.kuisioner.create', compact('event'));
}

public function store(Request $request)
{
    $request->validate([
        'event_id' => 'required|exists:event,id',
        'pertanyaan' => 'required|string|max:255',
    ]);

    Kuisioner::create($request->only('event_id', 'pertanyaan'));
    return redirect()->route('kuisioner.by-event', $request->event_id)->with('success', 'Pertanyaan berhasil ditambahkan.');
}

public function edit($id)
{
    $kuisioner = Kuisioner::with('event')->findOrFail($id);
    return view('admin.kuisioner.edit', compact('kuisioner'));
}

public function update(Request $request, $id)
{
    $kuisioner = Kuisioner::findOrFail($id);

    $request->validate([
        'pertanyaan' => 'required|string|max:255',
    ]);

    $kuisioner->update([
        'pertanyaan' => $request->pertanyaan,
    ]);

    return redirect()->route('kuisioner.by-event', $kuisioner->event_id)->with('success', 'Pertanyaan diperbarui.');
}

public function destroy($id)
{
    $kuisioner = Kuisioner::findOrFail($id);
    $eventId = $kuisioner->event_id;
    $kuisioner->delete();

    return redirect()->route('kuisioner.by-event', $eventId)->with('success', 'Pertanyaan dihapus.');
}

}
