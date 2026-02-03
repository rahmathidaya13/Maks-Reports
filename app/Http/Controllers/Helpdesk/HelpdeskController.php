<?php

namespace App\Http\Controllers\Helpdesk;

use Inertia\Inertia;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use App\Models\Tickets\TicketModel;
use App\Http\Controllers\Controller;
use App\Models\ProductRequestUserModel;
use App\Models\Tickets\TicketMessageModel;

class HelpdeskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:developer|admin|user']);
    }

    public function index()
    {
        $user = auth()->user()->id;

        $tickets = TicketModel::where('created_by', $user)
            ->with(['creator', 'messages', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $baseProduct = ProductModel::with(['prices' => function ($q) {
            $q->select('base_price', 'product_id');
        }])
            ->select('product_id', 'name')
            ->get();

        $productRequest = ProductRequestUserModel::query()
            ->with(['user.profile.branch', 'product'])
            ->where('user_id', auth()->id())
            ->orderByRaw("FIELD('status', 'pending', 'approved', 'rejected')")
            ->latest()
            ->get();

        return Inertia::render('Helpdesk/Index', [
            'tickets' => $tickets,
            'baseProduct' => $baseProduct,
            'productRequest' => $productRequest
        ]);
    }

    public function store(Request $request)
    {
        $ticket = TicketModel::create([
            'created_by' => auth()->id(),
            'subject' => $request->subject,
            'category' => $request->category ?? 'general',
            'status' => 'open'
        ]);

        TicketMessageModel::create([
            'ticket_id' => $ticket->ticket_id,
            'created_by' => auth()->id(),
            'message' => $request->message
        ]);

        return redirect()->route('helpdesk.show', $ticket->ticket_id);
    }

    public function show($id)
    {
        // BUKA CHAT ROOM
        $ticket = TicketModel::with(['creator', 'messages', 'latestMessage'])
            ->where('ticket_id', $id)
            ->where('created_by', auth()->id()) // Security check
            ->with(['messages.creator']) // Eager load user di setiap chat
            ->firstOrFail();

        return Inertia::render('Helpdesk/ChatRoom', [
            'ticket' => $ticket
        ]);
    }

    public function reply(Request $request, $id)
    {
        // KIRIM BALASAN
        $request->validate(['message' => 'required']);

        TicketMessageModel::create([
            'ticket_id' => $id,
            'created_by' => auth()->id(),
            'message' => $request->message
        ]);

        // Update timestamp tiket agar naik ke atas di list
        $ticket = TicketModel::findOrFail($id);
        $ticket->touch();
        $ticket->update(['status' => 'open']); // Set open lagi karena user membalas

        return back(); // Stay di chat room
    }
}
