<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Notification;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // GET /api/tickets — ambil semua tiket sesuai role
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            // Admin lihat semua tiket
            $tickets = Ticket::with(['pelapor', 'teknisi'])->latest()->get();

        } elseif ($user->isTeknisi()) {
            // Teknisi lihat tiket yang di-assign ke dia
            $tickets = Ticket::with(['pelapor'])
                ->where('teknisi_id', $user->id)
                ->latest()->get();

        } else {
            // Pelapor lihat tiket miliknya sendiri
            $tickets = Ticket::with(['teknisi'])
                ->where('user_id', $user->id)
                ->latest()->get();
        }

        return response()->json($tickets);
    }

    // POST /api/tickets — buat tiket baru
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'priority'    => 'in:low,medium,high',
            'category'    => 'in:hardware,software,network,other',
        ]);

        $ticket = Ticket::create([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority ?? 'medium',
            'category'    => $request->category ?? 'other',
            'status'      => 'open',
            'user_id'     => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Tiket berhasil dibuat',
            'ticket'  => $ticket
        ], 201);
    }

    // GET /api/tickets/{id} — detail tiket
    public function show(Request $request, $id)
    {
        $user   = $request->user();
        $ticket = Ticket::with(['pelapor', 'teknisi'])->findOrFail($id);

        // Pelapor hanya bisa lihat tiket miliknya
        if ($user->isPelapor() && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Teknisi hanya bisa lihat tiket yang di-assign ke dia
        if ($user->isTeknisi() && $ticket->teknisi_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return response()->json($ticket);
    }

    // PUT /api/tickets/{id} — update status tiket
    public function update(Request $request, $id)
    {
        $user   = $request->user();
        $ticket = Ticket::findOrFail($id);

        // Pelapor tidak boleh update
        if ($user->isPelapor()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'status'           => 'in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket->update([
            'status'           => $request->status ?? $ticket->status,
            'resolution_notes' => $request->resolution_notes ?? $ticket->resolution_notes,
            'resolved_at'      => $request->status === 'resolved' ? now() : $ticket->resolved_at,
        ]);

        // Kirim notifikasi ke pelapor
        Notification::create([
            'user_id'   => $ticket->user_id,
            'title'     => 'Status Tiket Diperbarui',
            'message'   => "Tiket #{$ticket->id} sekarang berstatus {$ticket->status}",
            'type'      => 'ticket_updated',
            'ticket_id' => $ticket->id,
        ]);

        return response()->json([
            'message' => 'Tiket berhasil diupdate',
            'ticket'  => $ticket
        ]);
    }

    // POST /api/tickets/{id}/assign — assign tiket ke teknisi (admin only)
    public function assign(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'teknisi_id' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update([
            'teknisi_id' => $request->teknisi_id,
            'status'     => 'in_progress',
        ]);

        // Notifikasi ke teknisi
        Notification::create([
            'user_id'   => $request->teknisi_id,
            'title'     => 'Tiket Baru Ditugaskan',
            'message'   => "Anda mendapat tiket baru: {$ticket->title}",
            'type'      => 'ticket_assigned',
            'ticket_id' => $ticket->id,
        ]);

        // Notifikasi ke pelapor
        Notification::create([
            'user_id'   => $ticket->user_id,
            'title'     => 'Tiket Sedang Diproses',
            'message'   => "Tiket #{$ticket->id} sedang ditangani teknisi",
            'type'      => 'ticket_assigned',
            'ticket_id' => $ticket->id,
        ]);

        return response()->json([
            'message' => 'Tiket berhasil di-assign',
            'ticket'  => $ticket
        ]);
    }

    // DELETE /api/tickets/{id} — hapus tiket (admin only)
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        Ticket::findOrFail($id)->delete();

        return response()->json(['message' => 'Tiket berhasil dihapus']);
    }
}