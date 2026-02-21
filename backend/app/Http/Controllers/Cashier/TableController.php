<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\TransferTableRequest;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use App\Services\ActivityLogService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $tables  = Table::forStore($storeId)
            ->with('activeOrder', 'activeReservation')
            ->orderBy('section')
            ->orderBy('number')
            ->get();

        return view('cashier.tables.index', compact('tables'));
    }

    public function transfer(TransferTableRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('transfer', Table::class);
        $this->orderService->transferTable($order, $request->table_id);
        ActivityLogService::log('table_transferred', $order, description: "Order #{$order->order_number} moved to table #{$request->table_id}");
        return back()->with('success', 'Pesanan berhasil dipindahkan.');
    }

    // Reservations
    public function createReservation(Request $request): View
    {
        $storeId = $request->get('_store_id');
        $tables  = Table::forStore($storeId)->available()->orderBy('number')->get();
        return view('cashier.reservations.create', compact('tables'));
    }

    public function storeReservation(StoreReservationRequest $request): RedirectResponse
    {
        $data               = $request->validated();
        $data['created_by'] = auth()->id();
        $data['expires_at'] = \Carbon\Carbon::parse($data['reserved_at'])->addMinutes(30);

        $reservation = Reservation::create($data);
        Table::where('id', $reservation->table_id)->update(['status' => 'reserved']);
        ActivityLogService::logCreated($reservation);
        return redirect()->route('cashier.tables.index')->with('success', 'Reservasi berhasil dibuat.');
    }

    public function cancelReservation(Reservation $reservation): RedirectResponse
    {
        abort_if(! $reservation->isActive(), 422, 'Reservasi tidak aktif.');
        $reservation->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => 'Dibatalkan manual.']);
        Table::where('id', $reservation->table_id)->where('status', 'reserved')->update(['status' => 'available']);
        return back()->with('success', 'Reservasi dibatalkan.');
    }
}