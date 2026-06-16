@extends('admin.layout')

@section('content')

<div class="card">
    <h2>📅 Room Calendar — {{ $room['name'] }} (Room {{ $room['room_number'] ?? '' }})</h2>

    <div id="legend" style="margin-bottom:12px;font-size:13px;">
        <span style="background:orange;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Pending</span>
        <span style="background:green;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Confirmed</span>
        <span style="background:blue;color:white;padding:3px 8px;border-radius:4px;margin-right:5px;">Checked In</span>
        <span style="background:red;color:white;padding:3px 8px;border-radius:4px;">Cancelled</span>
    </div>

    <div id="calendar"></div>
</div>

{{-- FIX: removed duplicate FullCalendar includes — already in layout --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },

        // FIX: use $room['id'] consistently (integer id, not uuid)
        events: '/admin/rooms/{{ $room['id'] }}/calendar-data',

        eventClick: function (info) {
            alert(
                '📋 ' + info.event.title + '\n' +
                '📅 From: ' + info.event.startStr + '\n' +
                '📅 To: '   + info.event.endStr
            );
        }
    });

    calendar.render();
});
</script>

@endsection
