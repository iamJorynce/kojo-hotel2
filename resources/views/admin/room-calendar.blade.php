@extends('admin.layout')

@section('content')

<div class="card">
    <h2>📅 Room Calendar - {{ $room['name'] }}</h2>

    <div id="calendar"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',
        height: 'auto',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },

        events: '/admin/rooms/{{ $room['id'] }}/calendar-data',

        eventClick: function(info) {
            alert(
                "Guest: " + info.event.title +
                "\nFrom: " + info.event.startStr +
                "\nTo: " + info.event.endStr
            );
        }

    });

    calendar.render();
});
</script>

@endsection