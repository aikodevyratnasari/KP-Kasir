@extends('layouts.app')
@section('title', 'Tampilan Dapur')
@section('page-title', 'Tampilan Dapur')

@section('content')
@php
    $queued  = $orders->where('status', 'pending');
    $cooking = $orders->where('status', 'cooking');
@endphp

<style>
.kitchen-card {
    background: var(--color-background-primary, #fff);
    border-radius: 14px;
    padding: 0;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    display: flex;
    flex-direction: column;
}
.kitchen-card:hover {
    transform: translateY(-2px);
}
.kitchen-card-header {
    padding: 12px 14px 10px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}
.kitchen-card-body {
    padding: 0 14px;
    flex: 1;
}
.kitchen-card-footer {
    padding: 12px 14px 14px;
}
.order-num {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-primary, #111);
    letter-spacing: -0.2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.customer-name {
    font-size: 12px;
    font-weight: 500;
    color: #6366f1;
    margin-top: 2px;
}
.order-meta {
    font-size: 11.5px;
    color: var(--color-text-secondary, #777);
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.order-notes {
    font-size: 11.5px;
    color: #d97706;
    margin-top: 4px;
    font-style: italic;
    display: flex;
    align-items: flex-start;
    gap: 4px;
    line-height: 1.4;
}
.timer-badge {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.timer-green  { background: #dcfce7; color: #15803d; }
.timer-yellow { background: #fef9c3; color: #a16207; }
.timer-red    { background: #fee2e2; color: #b91c1c; }

.item-divider {
    height: 1px;
    background: var(--color-border-tertiary, rgba(0,0,0,0.06));
    margin: 0 14px;
}
.item-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    padding: 9px 0;
    border-bottom: 1px solid var(--color-border-tertiary, rgba(0,0,0,0.05));
}
.item-row:last-child { border-bottom: none; }
.item-name {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-text-primary, #111);
    flex: 1;
}
.item-variant {
    font-size: 11px;
    color: #6366f1;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 3px;
}
.item-special {
    font-size: 11px;
    color: #ea580c;
    margin-top: 2px;
    font-style: italic;
    display: flex;
    align-items: center;
    gap: 3px;
}
.item-qty {
    font-size: 13px;
    font-weight: 700;
    color: var(--color-text-primary, #111);
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.section-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1.5px solid;
}
.section-label-queue   { color: #b45309; border-color: #fde68a; }
.section-label-cooking { color: #c2410c; border-color: #fed7aa; }

.kitchen-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

.btn-start {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 10px;
    background: #fbbf24;
    color: #78350f;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.15s;
}
.btn-start:hover { background: #f59e0b; }

.btn-done {
    width: 100%;
    padding: 11px;
    border: none;
    border-radius: 10px;
    background: #22c55e;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.15s;
}
.btn-done:hover { background: #16a34a; }

.live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    animation: pulse-dot 1.5s infinite;
    display: inline-block;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(0.8); }
}

.countdown-bar {
    height: 3px;
    border-radius: 0 0 0 0;
    background: var(--color-border-tertiary, rgba(0,0,0,0.07));
    overflow: hidden;
}
.countdown-bar-fill-yellow { background: #fbbf24; height: 100%; border-radius: 2px; }
.countdown-bar-fill-orange { background: #f97316; height: 100%; border-radius: 2px; }
.countdown-bar-fill-red    { background: #ef4444; height: 100%; border-radius: 2px; }
.countdown-bar-fill-green  { background: #22c55e; height: 100%; border-radius: 2px; }
</style>

<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ── Top bar ── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            {{-- Antri --}}
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            {{-- Antri --}}
            <div style="display:inline-flex; align-items:center; padding:4px 4px 4px 16px; background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.4); border-radius:20px; gap:10px;">
                <span style="font-size:13px; font-weight:700; color:#b45309;">Antri</span>
                <span style="font-size:13px; font-weight:700; color:white; background:#fbbf24; border-radius:50%; width:30px; height:30px; min-width:30px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $queued->count() }}</span>
            </div>
            <div style="display:inline-flex; align-items:center; padding:4px 4px 4px 16px; background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.4); border-radius:20px; gap:10px;">
                <span style="font-size:13px; font-weight:700; color:#dc2626;">Dimasak</span>
                <span style="font-size:13px; font-weight:700; color:white; background:#ef4444; border-radius:50%; width:30px; height:30px; min-width:30px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ $cooking->count() }}</span>
            </div>
        </div>
        </div>
        <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--color-text-secondary,#888);">
            <span class="live-dot"></span>
            <span>Live &mdash; auto-refresh tiap 10 dtk</span>
        </div>
    </div>

    {{-- ── ANTRI ── --}}
    @if($queued->count() > 0)
    <div>
        <div class="section-label section-label-queue">
            Antri
        </div>

        <div class="kitchen-grid">
            @foreach($queued as $order)
            @php
                $ko       = $order->kitchenOrder;
                $waitMins = $ko?->queued_at ? (int) now()->diffInMinutes($ko->queued_at) : 0;
                $urgency  = $waitMins >= 20 ? 'red' : ($waitMins >= 10 ? 'yellow' : 'green');
                $barPct   = min(100, ($waitMins / 25) * 100);
                $borderColor = match($urgency) {
                    'red'    => '#fca5a5',
                    'yellow' => '#fde68a',
                    default  => '#bbf7d0',
                };
                $barClass = match($urgency) {
                    'red'    => 'countdown-bar-fill-red',
                    'yellow' => 'countdown-bar-fill-yellow',
                    default  => 'countdown-bar-fill-green',
                };
            @endphp
            <div class="kitchen-card" style="border:1.5px solid {{ $borderColor }};">
                {{-- Progress bar atas --}}
                <div class="countdown-bar">
                    <div class="{{ $barClass }}" style="width:{{ $barPct }}%;"></div>
                </div>

                <div class="kitchen-card-header">
                    <div style="flex:1; min-width:0;">
                        <div class="order-num">#{{ $order->order_number }}</div>
                        @if($order->customer_name)
                            <div class="customer-name">{{ $order->customer_name }}</div>
                        @endif
                        <div class="order-meta">
                            @if($order->order_type === 'dine_in')
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M3 10h18"/></svg>
                                Dine-In
                                @if($order->table) &middot; Meja <strong>{{ $order->table->number }}</strong> @endif
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                Takeaway
                            @endif
                        </div>
                        @if($order->notes)
                            <div class="order-notes">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;flex-shrink:0;margin-top:1px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                {{ $order->notes }}
                            </div>
                        @endif
                    </div>
                    <span class="timer-badge timer-{{ $urgency }}">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $waitMins }}
                    </span>
                </div>

                <div class="item-divider"></div>

                <div class="kitchen-card-body">
                    @foreach($order->items as $item)
                    <div class="item-row">
                        <div style="flex:1;min-width:0;">
                            <div class="item-name">{{ $item->product_name }}</div>
                            @if($item->variant_name)
                                <div class="item-variant">
    {{ $item->variant_name }}
</div>
                            @endif
                            @if($item->special_notes)
                                <div class="item-special">
                                    {{ $item->special_notes }}
                                </div>
                            @endif
                        </div>
                        <div class="item-qty">{{ $item->quantity }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="kitchen-card-footer">
                    <form method="POST" action="{{ route('kitchen.orders.start', $ko) }}">
                        @csrf
                        <button type="submit" class="btn-start">
                            Mulai Masak
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── DIMASAK ── --}}
    @if($cooking->count() > 0)
    <div>
        <div class="section-label section-label-cooking">
            Sedang dimasak
        </div>

        <div class="kitchen-grid">
            @foreach($cooking as $order)
            @php
                $ko       = $order->kitchenOrder;
                $waitMins = $ko?->cooking_started_at ? (int) now()->diffInMinutes($ko->cooking_started_at) : 0;
                $urgency  = $waitMins >= 20 ? 'red' : ($waitMins >= 10 ? 'yellow' : 'green');
                $barPct   = min(100, ($waitMins / 25) * 100);
                $barClass = match($urgency) {
                    'red'    => 'countdown-bar-fill-red',
                    'yellow' => 'countdown-bar-fill-orange',
                    default  => 'countdown-bar-fill-green',
                };
                $borderColor = match($urgency) {
                    'red'    => '#fdba74',
                    'yellow' => '#fdba74',
                    default  => '#6ee7b7',
                };
            @endphp
            <div class="kitchen-card" style="border:1.5px solid {{ $borderColor }};">
                <div class="countdown-bar">
                    <div class="{{ $barClass }}" style="width:{{ $barPct }}%;"></div>
                </div>

                <div class="kitchen-card-header">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="order-num">#{{ $order->order_number }}</div>
                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;padding:2px 7px;border-radius:20px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:9px;height:9px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                Masak
                            </span>
                        </div>
                        @if($order->customer_name)
                            <div class="customer-name">{{ $order->customer_name }}</div>
                        @endif
                        <div class="order-meta">
                            @if($order->order_type === 'dine_in')
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2z"/><path d="M3 10h18"/></svg>
                                Dine-In
                                @if($order->table) &middot; Meja <strong>{{ $order->table->number }}</strong> @endif
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" style="width:11px;height:11px;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                Takeaway
                            @endif
                        </div>
                        @if($order->notes)
                           <div class="order-notes">
    {{ $order->notes }}
</div>
                        @endif
                    </div>
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;padding:2px 7px;border-radius:20px;border:1px solid;flex-shrink:0;
    {{ $urgency === 'red' ? 'background:#fee2e2;color:#b91c1c;border-color:#fca5a5;' : ($urgency === 'yellow' ? 'background:#fef9c3;color:#a16207;border-color:#fde68a;' : 'background:#dcfce7;color:#15803d;border-color:#bbf7d0;') }}">
    <svg xmlns="http://www.w3.org/2000/svg" style="width:9px;height:9px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    {{ $waitMins }}m
</span>
                </div>

                <div class="item-divider"></div>

                <div class="kitchen-card-body">
                    @foreach($order->items as $item)
                    <div class="item-row">
                        <div style="flex:1;min-width:0;">
                            <div class="item-name">{{ $item->product_name }}</div>
                            @if($item->variant_name)
    <div class="item-variant">
        {{ $item->variant_name }}
    </div>
@endif
                            @if($item->special_notes)
                                <div class="item-special">
                                    {{ $item->special_notes }}
                                </div>
                            @endif
                        </div>
                        <div class="item-qty">{{ $item->quantity }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="kitchen-card-footer">
                    <form method="POST" action="{{ route('kitchen.orders.ready', $ko) }}">
                        @csrf
                        <button type="submit" class="btn-done">
                            Selesai
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Kosong ── --}}
    @if($orders->isEmpty())
    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:80px 0; color:var(--color-text-secondary,#999);">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:56px;height:56px;margin-bottom:16px;opacity:0.3;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
            <line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
        </svg>
        <p style="font-size:16px; font-weight:500; color:var(--color-text-primary,#444); margin-bottom:4px;">Semua pesanan selesai</p>
        <p style="font-size:13px;">Tidak ada pesanan yang perlu diproses saat ini</p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    setTimeout(() => window.location.reload(), 10000);
</script>
@endpush