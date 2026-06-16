@extends('admin.layout')

@section('content')

<div class="card">
    <h2>📅 Booking Calendar</h2>

    <div id="legend" style="margin-bottom:12px;font-size:13px;">
        <span style="background:orange;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Pending</span>
        <span style="background:green;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Confirmed</span>
        <span style="background:blue;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Checked In</span>
        <span style="background:red;color:white;padding:3px 8px;border-radius:4px;">Cancelled</span>
    </div>

    <div id="calendar"></div>
</div>

{{-- FIX: removed duplicate FullCalendar CSS/JS — already loaded in layout.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');
    const events     = @json($events);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 700,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,listWeek'
        },
        events: events,

        eventClick: function (info) {
            const d = info.event.extendedProps;
            alert(
                '👤 Guest: '       + (d.guest       || '-') + '\n' +
                '🏠 Room No: '     + (d.room_number || '-') + '\n' +
                '🏨 Room Type: '   + (d.room_type   || '-') + '\n' +
                '📅 Check-in: '    + (d.check_in    || '-') + '\n' +
                '📅 Check-out: '   + (d.check_out   || '-') + '\n' +
                '📌 Status: '      + (d.status      || '-')
            );
        }
    });

    calendar.render();
});
</script>

@endsection
