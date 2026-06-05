@extends('admin.layout')

@section('content')

<div class="card">
    <h2>📅 Booking Calendar</h2>

    <div id="legend" style="margin-bottom:10px;">
        <span style="color:orange">🟧 Pending</span> |
        <span style="color:green">🟩 Confirmed</span> |
        <span style="color:blue">🟦 Checked In</span> |
        <span style="color:red">🟥 Cancelled</span>
    </div>

    <div id="calendar"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let calendarEl = document.getElementById('calendar');
    let events = @json($events);

    let calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        height: 700,
        events: events,

        eventClick: function(info) {

            let data = info.event.extendedProps;

            let message =
                "👤 Guest: " + data.guest + "\n" +
                "🏠 Room Number: " + data.room_number + "\n" +
                "🏨 Room Type: " + data.room_type + "\n" +
                "📅 Check In: " + data.check_in + "\n" +
                "📅 Check Out: " + data.check_out;

            alert(message);
        }

    });

    calendar.render();
});
</script>

@endsection