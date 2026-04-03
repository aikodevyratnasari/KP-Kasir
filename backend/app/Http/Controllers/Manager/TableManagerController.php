<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Models\Table;
use App\Services\ActivityLogService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TableManagerController extends Controller
{
    public function index(Request $request): View
    {
        $storeId  = $request->get('_store_id');
        $tables   = Table::forStore($storeId)->orderBy('section')->orderBy('number')->get();
        $sections = $tables->pluck('section')->filter()->unique()->values()->toArray();

        $defaultSections = ['Indoor', 'Outdoor', 'VIP'];
        $sections = array_unique(array_merge($defaultSections, $sections));

        return view('manager.tables.index', compact('tables', 'sections'));
    }

    /**
     * Simpan satu meja (form biasa)
     */
    public function store(StoreTableRequest $request): RedirectResponse
    {
        $data             = $request->validated();
        $data['store_id'] = (int) $request->get('_store_id');
        $data['status']   = 'available';

        try {
            $table = Table::create($data);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()
                ->withErrors(['number' => 'Nomor meja sudah digunakan.']);
        }

        ActivityLogService::logCreated($table);

        return redirect()->route('manager.tables.index')
            ->with('success', "Meja {$table->number} berhasil ditambahkan.");
    }

    /**
     * Simpan banyak meja sekaligus (bulk)
     * Payload: section, prefix, start, end, capacity
     * Contoh: prefix=A, start=1, end=6, capacity=4
     * → A01, A02, A03, A04, A05, A06 masing-masing 4 kursi
     */
    public function storeBulk(Request $request): RedirectResponse
    {
        $storeId = (int) $request->get('_store_id');

        $v = Validator::make($request->all(), [
            'section'  => ['nullable', 'string', 'max:50'],
            'prefix'   => ['nullable', 'string', 'max:10'],
            'start'    => ['required', 'integer', 'min:1', 'max:999'],
            'end'      => ['required', 'integer', 'min:1', 'max:999', 'gte:start'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'end.gte' => 'Nomor akhir harus lebih besar atau sama dengan nomor awal.',
        ]);

        if ($v->fails()) {
            return back()->withInput()->withErrors($v, 'bulk');
        }

        $data     = $v->validated();
        $prefix   = strtoupper(trim($data['prefix'] ?? ''));
        $section  = $data['section'] ?? null;
        $capacity = (int) $data['capacity'];
        $start    = (int) $data['start'];
        $end      = (int) $data['end'];

        // Batas wajar: max 50 meja per bulk
        if (($end - $start + 1) > 50) {
            return back()->withInput()
                ->withErrors(['end' => 'Maksimal 50 meja per penambahan massal.'], 'bulk');
        }

        $created = 0;
        $skipped = [];

        DB::transaction(function () use (
            $start, $end, $prefix, $section, $capacity, $storeId, &$created, &$skipped
        ) {
            for ($i = $start; $i <= $end; $i++) {
                // Format nomor: jika prefix ada → A01, B03, dst
                // Jika tidak → 01, 02, dst (zero-pad 2 digit)
                $number = $prefix . str_pad($i, 2, '0', STR_PAD_LEFT);

                $exists = Table::where('store_id', $storeId)
                    ->where('number', $number)
                    ->exists();

                if ($exists) {
                    $skipped[] = $number;
                    continue;
                }

                Table::create([
                    'store_id' => $storeId,
                    'number'   => $number,
                    'capacity' => $capacity,
                    'section'  => $section,
                    'status'   => 'available',
                ]);
                $created++;
            }
        });

        $msg = "{$created} meja berhasil ditambahkan.";
        if (count($skipped) > 0) {
            $msg .= ' Dilewati (sudah ada): ' . implode(', ', $skipped) . '.';
        }

        return redirect()->route('manager.tables.index')->with('success', $msg);
    }

    public function update(UpdateTableRequest $request, Table $table): RedirectResponse
    {
        $old = $table->toArray();

        try {
            $table->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()
                ->withErrors(['number' => 'Nomor meja sudah digunakan.']);
        }

        ActivityLogService::logUpdated($table, $old, $table->toArray());

        return redirect()->route('manager.tables.index')
            ->with('success', "Meja {$table->number} berhasil diperbarui.");
    }

    public function destroy(Table $table): RedirectResponse
    {
        abort_if(
            in_array($table->status, ['occupied', 'reserved']),
            422,
            'Tidak dapat menghapus meja yang sedang digunakan.'
        );

        ActivityLogService::logDeleted($table);
        $table->delete();

        return redirect()->route('manager.tables.index')
            ->with('success', "Meja {$table->number} berhasil dihapus.");
    }
}