<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeknisiController extends Controller
{
    // GET /api/teknisi — list semua teknisi
    public function index()
    {
        $teknisi = User::where('role', 'teknisi')
            ->withCount([
                'assignedTickets as total_tickets',
                'assignedTickets as open_tickets' => function ($q) {
                    $q->where('status', 'open');
                },
                'assignedTickets as resolved_tickets' => function ($q) {
                    $q->where('status', 'resolved');
                },
            ])
            ->get();

        return response()->json($teknisi);
    }

    // POST /api/teknisi — tambah teknisi baru
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone'    => 'nullable|string',
        ]);

        $teknisi = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'teknisi',
            'phone'    => $request->phone,
        ]);

        return response()->json([
            'message' => 'Teknisi berhasil ditambahkan',
            'teknisi' => $teknisi
        ], 201);
    }

    // DELETE /api/teknisi/{id} — hapus teknisi
    public function destroy($id)
    {
        $teknisi = User::where('role', 'teknisi')->findOrFail($id);
        $teknisi->delete();

        return response()->json(['message' => 'Teknisi berhasil dihapus']);
    }
}