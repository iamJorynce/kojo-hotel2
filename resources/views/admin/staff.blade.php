@extends('admin.layout')

@section('content')

@if($errors->any())
    <div style="background:#fadbd8;border:2px solid #c0392b;padding:16px;border-radius:8px;margin-bottom:20px;">
        <p style="margin:0;color:#c0392b;font-weight:600;">❌ Error:</p>
        @foreach($errors->all() as $error)
            <p style="margin:4px 0 0 0;color:#c0392b;font-size:13px;">{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="topbar">
    <h2>👥 Staff Management</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:24px;">

    {{-- ADD STAFF FORM --}}
    <div class="card">
        <h3 style="margin-bottom:16px;">➕ Add New Staff</h3>

        <form method="POST" action="/admin/staff/create">
            @csrf

            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="e.g. Juan dela Cruz" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="staff@seaeagle.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Min. 6 characters" required>

            <label>Role</label>
            <select name="role" required>
                <option value="cashier">💰 Cashier</option>
                <option value="front_desk">🏨 Front Desk</option>
                <option value="housekeeping">🧹 Housekeeping</option>
                <option value="security">🔒 Security</option>
                <option value="admin">⚙️ Admin</option>
            </select>

            <button type="submit" class="btn btn-primary"
                    style="width:100%;padding:11px;margin-top:6px;">
                Create Staff Account
            </button>
        </form>
    </div>

    {{-- STAFF LIST --}}
    <div>
        <h3 style="margin-bottom:14px;">Current Staff ({{ $staff->count() }})</h3>

        @forelse($staff as $s)
        @php
            $roleColors = [
                'admin'       => '#0a4a6e',
                'cashier'     => '#1a6b3c',
                'front_desk'  => '#854d0e',
                'housekeeping'=> '#6b21a8',
                'security'    => '#c0392b',
            ];
            $roleLabels = [
                'admin'       => '⚙️ Admin',
                'cashier'     => '💰 Cashier',
                'front_desk'  => '🏨 Front Desk',
                'housekeeping'=> '🧹 Housekeeping',
                'security'    => '🔒 Security',
            ];
            $color = $roleColors[$s['role']] ?? '#555';
            $label = $roleLabels[$s['role']] ?? ucfirst($s['role']);
        @endphp

        <div class="card" style="margin-bottom:12px;border-left:4px solid {{ $color }};">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">

                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                        <h3 style="margin:0;font-size:16px;">{{ $s['full_name'] }}</h3>
                        <span style="background:{{ $color }};color:white;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:500;">
                            {{ $label }}
                        </span>
                        @if(!$s['is_active'])
                            <span style="background:#fee2e2;color:#c0392b;padding:2px 8px;border-radius:20px;font-size:11px;">
                                Inactive
                            </span>
                        @endif
                    </div>
                    <p style="color:#888;font-size:13px;margin-bottom:4px;">{{ $s['email'] }}</p>
                    <p style="color:#aaa;font-size:12px;">
                        Added: {{ \Carbon\Carbon::parse($s['created_at'])->format('M d, Y') }}
                        @if($s['last_login_at'])
                            · Last login: {{ \Carbon\Carbon::parse($s['last_login_at'])->diffForHumans() }}
                        @else
                            · Never logged in
                        @endif
                    </p>
                </div>

                {{-- ACTIONS --}}
                @if($s['id'] != session('admin_id'))
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <a href="/admin/staff/toggle/{{ $s['id'] }}"
                       class="btn {{ $s['is_active'] ? 'btn-warning' : 'btn-success' }}"
                       style="font-size:11px;padding:5px 10px;"
                       onclick="return confirm('{{ $s['is_active'] ? 'Deactivate' : 'Activate' }} this staff?')">
                        {{ $s['is_active'] ? '🔒 Deactivate' : '✅ Activate' }}
                    </a>
                    <a href="/admin/staff/delete/{{ $s['id'] }}"
                       class="btn btn-danger"
                       style="font-size:11px;padding:5px 10px;"
                       onclick="return confirm('Delete {{ $s['full_name'] }}? This cannot be undone.')">
                        🗑
                    </a>
                </div>
                @else
                    <span style="font-size:12px;color:#888;padding:5px;">(You)</span>
                @endif

            </div>

            {{-- INLINE EDIT --}}
            <details style="margin-top:12px;">
                <summary style="cursor:pointer;font-size:13px;color:#0a4a6e;font-weight:500;">✏️ Edit</summary>
                <form method="POST" action="/admin/staff/update/{{ $s['id'] }}" style="margin-top:10px;">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="{{ $s['full_name'] }}" required>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="{{ $s['email'] }}" required>
                        </div>
                        <div>
                            <label>New Password <small style="color:#999;">(leave blank to keep)</small></label>
                            <input type="password" name="password" placeholder="New password...">
                        </div>
                        <div>
                            <label>Role</label>
                            <select name="role">
                                @foreach(['cashier'=>'💰 Cashier','front_desk'=>'🏨 Front Desk','housekeeping'=>'🧹 Housekeeping','security'=>'🔒 Security','admin'=>'⚙️ Admin'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ $s['role'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success" style="margin-top:8px;">✅ Update</button>
                </form>
            </details>

        </div>
        @empty
            <div class="card" style="text-align:center;color:#999;padding:30px;">No staff found.</div>
        @endforelse
    </div>

</div>

@endsection
