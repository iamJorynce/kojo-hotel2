<h2 style="
    text-align:center;
    margin:30px 0;
    font-family:Arial;
    font-size:26px;
    color:#0a4a6e;
">
    Book Room Category
</h2>

@if(!$room)

    <p style="text-align:center; color:red; font-family:Arial;">
        No available room for this category.
    </p>

@else

<!-- INFO BOX -->
<div style="
    max-width:900px;
    margin:0 auto 15px auto;
    background:#eaf4ff;
    padding:12px;
    border-radius:10px;
    font-size:13px;
    color:#0a4a6e;
    font-family:Arial;
">
    💡 Reminder: Check-in = Arrival date | Check-out = Departure date
</div>

<!-- CARD -->
<div style="
    max-width:900px;
    margin:20px auto 60px auto;
    background:#fff;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,0.10);
    overflow:hidden;
    font-family:Arial;
">

    <!-- IMAGE HEADER -->
    <div style="position:relative;">

        <img src="{{ $room['image_url'] }}"
             style="width:100%; height:320px; object-fit:cover;">

        <!-- PRICE BADGE -->
        <div style="
            position:absolute;
            bottom:15px;
            right:15px;
            background:rgba(10,74,110,0.95);
            color:white;
            padding:8px 14px;
            border-radius:8px;
            font-weight:bold;
            font-size:16px;
        ">
            ₱{{ number_format($room['price'], 2) }} per night
        </div>
<div style="margin-top:15px;padding:10px;background:#f0f8ff;border-radius:10px;">
    <p><b>No. of Nights:</b> <span id="nights">0</span></p>
    
    <p><b>Total:</b> ₱<span id="total">0</span></p>
</div>
    </div>

    <!-- CONTENT -->
    <div style="padding:28px;">

        <h3 style="margin:0; font-size:24px; color:#111;">
            {{ $room['name'] }}
        </h3>

        <p style="color:#666; margin-top:6px; line-height:1.5;">
            {{ $room['description'] ?? '' }}
        </p>

        <div style="height:1px;background:#eee;margin:22px 0;"></div>

        <!-- FORM -->
        <form method="POST" action="/book/{{ $room['uuid_id'] }}">
            @csrf

            <!-- GUEST INFO -->
            <div style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:14px;
                margin-bottom:15px;
            ">

                <input type="text" name="full_name" placeholder="Full Name" required
                       style="padding:12px;border:1px solid #ddd;border-radius:10px;">

                <input type="text" name="phone" placeholder="Phone Number" required
                       style="padding:12px;border:1px solid #ddd;border-radius:10px;">

                <input type="email" name="email" placeholder="Email" required
                       style="padding:12px;border:1px solid #ddd;border-radius:10px;">
            </div>

            <!-- DATES -->
            <div style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:14px;
            ">

                <!-- CHECK IN -->
                <div>
                    <label style="font-size:13px;color:#666;">
                        Check-in (Arrival Date)
                    </label>

                    <input type="date" name="check_in" required
                           style="width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;">

                    <small style="color:#999;font-size:12px;">
                        🏨 Guest arrival date
                    </small>
                </div>

                <!-- CHECK OUT -->
                <div>
                    <label style="font-size:13px;color:#666;">
                        Check-out (Departure Date)
                    </label>

                    <input type="date" name="check_out" required
                           style="width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;">

                    <small style="color:#999;font-size:12px;">
                        🚪 Guest departure date
                    </small>
                </div>

            </div>

            <!-- SUBMIT -->
            <button type="submit"
                    style="
                        margin-top:22px;
                        width:100%;
                        padding:14px;
                        background:linear-gradient(135deg,#0a4a6e,#0d6efd);
                        color:white;
                        border:none;
                        border-radius:12px;
                        font-size:16px;
                        font-weight:bold;
                        cursor:pointer;
                        box-shadow:0 6px 15px rgba(13,110,253,0.3);
                        transition:0.2s;
                    "
                    onmouseover="this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                Confirm Booking
            </button>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const checkIn = document.querySelector('input[name="check_in"]');
    const checkOut = document.querySelector('input[name="check_out"]');

    const nightsEl = document.getElementById("nights");
    const totalEl = document.getElementById("total");

    const pricePerNight = {{ $room['price'] }};

    function calculate() {

        if (checkIn.value && checkOut.value) {

            let inDate = new Date(checkIn.value);
            let outDate = new Date(checkOut.value);

            let diff = (outDate - inDate) / (1000 * 60 * 60 * 24);

            if (diff > 0) {
                nightsEl.innerText = diff;
                totalEl.innerText = pricePerNight * diff;
            } else {
                nightsEl.innerText = 0;
                totalEl.innerText = 0;
            }
        }
    }

    checkIn.addEventListener("change", calculate);
    checkOut.addEventListener("change", calculate);

});
</script>
        </form>

    </div>
</div>

@endif