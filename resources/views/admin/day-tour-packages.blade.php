@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🏷 Day Tour Packages</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:24px;">

    {{-- ADD FORM --}}
    <div class="card">
        <h3 style="margin-bottom:16px;">➕ Add Package</h3>

        <form method="POST" action="/admin/day-tour-packages/create">
            @csrf

            <label>Package Name</label>
            <input type="text" name="name" placeholder="e.g. Beach + Pool Access" required>

            <label>Description</label>
            <textarea name="description" rows="2" placeholder="Brief description..."></textarea>

            <label>Price per Person (₱)</label>
            <input type="number" name="price_per_person" min="1" step="0.01" placeholder="e.g. 250" required>

            <label>Inclusions <small style="color:#999;">(comma separated)</small></label>
            <textarea name="inclusions" rows="3"
                      placeholder="Beach access, Pool access, Shower facilities, Changing rooms"></textarea>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;margin-top:6px;">
                Save Package
            </button>
        </form>
    </div>

    {{-- EXISTING PACKAGES --}}
    <div>
        <h3 style="margin-bottom:14px;">Current Packages</h3>

        @forelse($packages as $pkg)
        <div class="card" style="margin-bottom:14px;border-left:5px solid #0a4a6e;">

            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                <div>
                    <h3 style="margin-bottom:4px;">{{ $pkg['name'] }}</h3>
                    <p style="color:#666;font-size:13px;margin-bottom:8px;">{{ $pkg['description'] ?? '' }}</p>
                    <p style="font-size:22px;font-weight:700;color:#0a4a6e;">
                        ₱{{ number_format($pkg['price_per_person'], 2) }}
                        <span style="font-size:13px;font-weight:400;color:#888;">/ person</span>
                    </p>
                    @if(!empty($pkg['inclusions']))
                    <p style="font-size:12px;color:#888;margin-top:6px;">
                        ✓ {{ $pkg['inclusions'] }}
                    </p>
                    @endif
                </div>
                <a href="/admin/day-tour-packages/delete/{{ $pkg['id'] }}"
                   class="btn btn-danger"
                   style="white-space:nowrap;font-size:12px;padding:6px 10px;"
                   onclick="return confirm('Delete this package?')">
                    🗑 Delete
                </a>
            </div>

            {{-- INLINE EDIT FORM --}}
            <details style="margin-top:14px;">
                <summary style="cursor:pointer;font-size:13px;color:#0a4a6e;font-weight:500;">✏️ Edit this package</summary>
                <form method="POST" action="/admin/day-tour-packages/update/{{ $pkg['id'] }}"
                      style="margin-top:12px;">
                    @csrf

                    <label>Name</label>
                    <input type="text" name="name" value="{{ $pkg['name'] }}" required>

                    <label>Description</label>
                    <textarea name="description" rows="2">{{ $pkg['description'] ?? '' }}</textarea>

                    <label>Price per Person (₱)</label>
                    <input type="number" name="price_per_person"
                           value="{{ $pkg['price_per_person'] }}" min="1" step="0.01" required>

                    <label>Inclusions</label>
                    <textarea name="inclusions" rows="2">{{ $pkg['inclusions'] ?? '' }}</textarea>

                    <button type="submit" class="btn btn-success" style="margin-top:8px;">
                        ✅ Update Package
                    </button>
                </form>
            </details>

        </div>
        @empty
            <div class="card" style="text-align:center;color:#999;padding:30px;">
                No packages yet. Add one on the left.
            </div>
        @endforelse
    </div>

</div>

@endsection
