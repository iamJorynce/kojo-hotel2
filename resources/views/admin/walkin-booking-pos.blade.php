@extends('admin.layout')

@section('content')

<style>
.pos-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    min-height: calc(100vh - 80px);
    overflow: hidden;
}
.pos-left {
    overflow-y: auto;
    padding-right: 10px;
}
.pos-right {
    overflow-y: auto;
    padding-left: 10px;
    border-left: 2px solid #ddd;
}
.section-box {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}
.item-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 0.5fr;
    gap: 8px;
    align-items: center;
    background: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 8px;
    border: 1px solid #eee;
}
.summary-total {
    display: flex;
    justify-content: space-between;
    padding: 16px;
    background: #0a4a6e;
    color: white;
    border-radius: 8px;
    font-weight: 600;
    font-size: 18px;
}
</style>

<div class="topbar">
    <h2>🛏️ Walk-In Booking - POS</h2>
    <a href="/admin/walkin/bookings" class="btn">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            ❌ {{ $error }}<br>
        @endforeach
    </div>
@endif

<div class="pos-container">

    {{-- LEFT: FORM --}}
    <form method="POST" class="pos-left" id="posForm">
        @csrf

        {{-- GUEST INFO & DATES --}}
        <div class="section-box">
            <h3 style="margin-top:0;">👤 Guest Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <input type="text" name="guest_name" placeholder="Guest Name *" required style="padding:8px;border:1px solid #ddd;border-radius:6px;">
                <input type="tel" name="guest_phone" placeholder="Phone *" required style="padding:8px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <input type="email" name="guest_email" placeholder="Email" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px;">

            <h4 style="margin:16px 0 12px 0;font-size:13px;">Booking Dates</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11px;margin-bottom:4px;">Check-In *</label>
                    <input type="date" name="check_in" id="checkInDate" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                </div>
                <div>
                    <label style="display:block;font-size:11px;margin-bottom:4px;">Check-Out *</label>
                    <input type="date" name="check_out" id="checkOutDate" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;">
                </div>
            </div>
        </div>

        {{-- ROOMS SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">🛏️ Rooms</h3>
            <div id="roomsContainer">
            </div>
            <button type="button" onclick="addRoomRow()" class="btn" style="width:100%;padding:8px;margin-top:8px;background:#0a4a6e;color:white;">+ Add Room</button>
        </div>

        {{-- COTTAGES SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">🏡 Cottages</h3>
            <div id="cottagesContainer">
            </div>
            <button type="button" onclick="addCottageRow()" class="btn" style="width:100%;padding:8px;margin-top:8px;background:#0a4a6e;color:white;">+ Add Cottage</button>
        </div>

        {{-- EQUIPMENT SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">🧰 Equipment & Add-ons</h3>
            <div id="equipmentContainer">
            </div>
            <button type="button" onclick="addEquipmentRow()" class="btn" style="width:100%;padding:8px;margin-top:8px;background:#0a4a6e;color:white;">+ Add Equipment</button>
        </div>

        <textarea name="notes" placeholder="Notes..." rows="2" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;margin-bottom:16px;"></textarea>

    </form>

    {{-- RIGHT: SUMMARY & PAYMENT --}}
    <div class="pos-right">
        <div class="section-box">
            <h3 style="margin-top:0;">💰 Summary</h3>

            <div id="nightsInfo" style="background:#f0f0f0;padding:10px;border-radius:6px;margin-bottom:12px;font-size:12px;">
                <p style="margin:0;">Nights: <strong id="nightsDisplay">0</strong></p>
            </div>

            <div id="itemsSummary" style="margin-bottom:16px;max-height:300px;overflow-y:auto;">
                <p style="color:#999;text-align:center;font-size:12px;">Add items to see summary</p>
            </div>

            <div style="background:#f0f0f0;padding:12px;border-radius:8px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #ddd;font-size:12px;">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12px;">
                    <span>Tax:</span>
                    <span id="tax">₱0.00</span>
                </div>
            </div>

            <div class="summary-total">
                <span>TOTAL:</span>
                <span id="totalAmount">₱0.00</span>
            </div>

            <button type="button" onclick="createTransaction()" class="btn" style="width:100%;padding:14px;margin-top:16px;background:#1a6b3c;color:white;font-weight:600;">
                ✓ Create Booking
            </button>
        </div>
    </div>

</div>

<script>
let items = {
    rooms: [],
    cottages: [],
    equipment: []
};

let nights = 0;

document.getElementById('checkInDate').addEventListener('change', calculateNights);
document.getElementById('checkOutDate').addEventListener('change', calculateNights);

function calculateNights() {
    const checkIn = new Date(document.getElementById('checkInDate').value);
    const checkOut = new Date(document.getElementById('checkOutDate').value);
    
    if (checkIn && checkOut) {
        nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        if (nights < 1) nights = 1;
        document.getElementById('nightsDisplay').textContent = nights;
    }
}

function addRoomRow() {
    const item = {
        id: Date.now(),
        roomId: '',
        name: '',
        nights: 1,
        pricePerNight: 0,
        subtotal: 0
    };
    
    items.rooms.push(item);
    
    const container = document.getElementById('roomsContainer');
    const roomOptions = `
        @foreach($rooms as $room)
            <option value="{{ $room['id'] }}" data-price="{{ $room['price_per_day'] }}">
                {{ $room['name'] }} - {{ $room['room_type'] }} (₱{{ number_format($room['price_per_day'], 2) }})
            </option>
        @endforeach
    `;
    
    const html = `
        <div class="item-row" data-id="${item.id}">
            <select onchange="updateRoomRow(${item.id}, this)" style="padding:6px;border:1px solid #ddd;border-radius:4px;">
                <option value="">Select Room...</option>
                ${roomOptions}
            </select>
            <input type="number" value="1" min="1" onchange="updateRoomNights(${item.id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;">
            <span>₱0.00</span>
            <button type="button" onclick="removeItem('rooms', ${item.id})" style="padding:4px 8px;font-size:11px;background:#c0392b;color:white;border:none;border-radius:4px;cursor:pointer;">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function updateRoomRow(id, select) {
    const option = select.options[select.selectedIndex];
    const item = items.rooms.find(i => i.id == id);
    
    if (item) {
        item.roomId = select.value;
        item.name = option.text.split('(')[0].trim();
        item.pricePerNight = parseFloat(option.dataset.price || 0);
        item.nights = nights || 1;
        item.subtotal = item.pricePerNight * item.nights;
        
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function updateRoomNights(id, newNights) {
    const item = items.rooms.find(i => i.id == id);
    if (item) {
        item.nights = parseInt(newNights) || 1;
        item.subtotal = item.pricePerNight * item.nights;
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function addCottageRow() {
    const item = {
        id: Date.now(),
        cottageId: '',
        name: '',
        nights: 1,
        pricePerNight: 0,
        subtotal: 0
    };
    
    items.cottages.push(item);
    
    const container = document.getElementById('cottagesContainer');
    const cottageOptions = `
        @foreach($cottages as $cottage)
            <option value="{{ $cottage['id'] }}" data-price="{{ $cottage['price_per_day'] }}">
                {{ $cottage['name'] }} (₱{{ number_format($cottage['price_per_day'], 2) }})
            </option>
        @endforeach
    `;
    
    const html = `
        <div class="item-row" data-id="${item.id}">
            <select onchange="updateCottageRow(${item.id}, this)" style="padding:6px;border:1px solid #ddd;border-radius:4px;">
                <option value="">Select Cottage...</option>
                ${cottageOptions}
            </select>
            <input type="number" value="1" min="1" onchange="updateCottageNights(${item.id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;">
            <span>₱0.00</span>
            <button type="button" onclick="removeItem('cottages', ${item.id})" style="padding:4px 8px;font-size:11px;background:#c0392b;color:white;border:none;border-radius:4px;cursor:pointer;">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function updateCottageRow(id, select) {
    const option = select.options[select.selectedIndex];
    const item = items.cottages.find(i => i.id == id);
    
    if (item) {
        item.cottageId = select.value;
        item.name = option.text.split('(')[0].trim();
        item.pricePerNight = parseFloat(option.dataset.price || 0);
        item.nights = nights || 1;
        item.subtotal = item.pricePerNight * item.nights;
        
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function updateCottageNights(id, newNights) {
    const item = items.cottages.find(i => i.id == id);
    if (item) {
        item.nights = parseInt(newNights) || 1;
        item.subtotal = item.pricePerNight * item.nights;
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function addEquipmentRow() {
    const item = {
        id: Date.now(),
        equipmentId: '',
        name: '',
        quantity: 1,
        pricePerUnit: 0,
        subtotal: 0
    };
    
    items.equipment.push(item);
    
    const container = document.getElementById('equipmentContainer');
    const equipmentOptions = `
        @foreach($equipmentTypes as $eq)
            <option value="{{ $eq['id'] }}" data-price="{{ $eq['price_per_day'] }}">
                {{ $eq['name'] }} (₱{{ number_format($eq['price_per_day'], 2) }})
            </option>
        @endforeach
    `;
    
    const html = `
        <div class="item-row" data-id="${item.id}">
            <select onchange="updateEquipmentRow(${item.id}, this)" style="padding:6px;border:1px solid #ddd;border-radius:4px;">
                <option value="">Select Equipment...</option>
                ${equipmentOptions}
            </select>
            <input type="number" value="1" min="1" onchange="updateEquipmentQty(${item.id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;">
            <span>₱0.00</span>
            <button type="button" onclick="removeItem('equipment', ${item.id})" style="padding:4px 8px;font-size:11px;background:#c0392b;color:white;border:none;border-radius:4px;cursor:pointer;">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function updateEquipmentRow(id, select) {
    const option = select.options[select.selectedIndex];
    const item = items.equipment.find(i => i.id == id);
    
    if (item) {
        item.equipmentId = select.value;
        item.name = option.text.split('(')[0].trim();
        item.pricePerUnit = parseFloat(option.dataset.price || 0);
        item.subtotal = item.pricePerUnit * item.quantity;
        
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function updateEquipmentQty(id, qty) {
    const item = items.equipment.find(i => i.id == id);
    if (item) {
        item.quantity = parseInt(qty) || 1;
        item.subtotal = item.pricePerUnit * item.quantity;
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function removeItem(type, id) {
    items[type] = items[type].filter(i => i.id != id);
    document.querySelector(`[data-id="${id}"]`).remove();
    updateTotal();
}

function updateTotal() {
    const subtotal = [...items.rooms, ...items.cottages, ...items.equipment]
        .reduce((sum, item) => sum + (item.subtotal || 0), 0);
    
    const tax = 0;
    const total = subtotal + tax;
    
    document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('tax').textContent = '₱' + tax.toFixed(2);
    document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    
    updateSummaryDisplay(subtotal, tax, total);
}

function updateSummaryDisplay(subtotal, tax, total) {
    const summary = document.getElementById('itemsSummary');
    let html = '';
    
    items.rooms.forEach(item => {
        html += `<div style="font-size:11px;margin-bottom:8px;padding:8px;background:white;border-radius:4px;border-left:4px solid #0a4a6e;">
            <strong>🛏️ ${item.name}</strong><br>
            ${item.nights} night(s) × ₱${item.pricePerNight.toFixed(2)} = <span style="color:#0a4a6e;font-weight:600;">₱${item.subtotal.toFixed(2)}</span>
        </div>`;
    });
    
    items.cottages.forEach(item => {
        html += `<div style="font-size:11px;margin-bottom:8px;padding:8px;background:#fffbf0;border-radius:4px;border-left:4px solid #f39c12;">
            <strong>🏡 ${item.name}</strong><br>
            ${item.nights} night(s) × ₱${item.pricePerNight.toFixed(2)} = <span style="color:#f39c12;font-weight:600;">₱${item.subtotal.toFixed(2)}</span>
        </div>`;
    });
    
    items.equipment.forEach(item => {
        html += `<div style="font-size:11px;margin-bottom:8px;padding:8px;background:#f0f0f0;border-radius:4px;border-left:4px solid #666;">
            <strong>🧰 ${item.name}</strong><br>
            ${item.quantity} × ₱${item.pricePerUnit.toFixed(2)} = <span style="color:#666;font-weight:600;">₱${item.subtotal.toFixed(2)}</span>
        </div>`;
    });
    
    summary.innerHTML = html || '<p style="color:#999;text-align:center;font-size:12px;">Add items to see summary</p>';
}

function createTransaction() {
    const guestName = document.querySelector('input[name="guest_name"]').value;
    const guestPhone = document.querySelector('input[name="guest_phone"]').value;
    const checkIn = document.getElementById('checkInDate').value;
    const checkOut = document.getElementById('checkOutDate').value;
    
    if (!guestName || !guestPhone) {
        alert('Please enter guest name and phone');
        return;
    }
    
    if (!checkIn || !checkOut) {
        alert('Please select check-in and check-out dates');
        return;
    }
    
    if (items.rooms.length === 0 && items.cottages.length === 0 && items.equipment.length === 0) {
        alert('Please add at least one item');
        return;
    }
    
    const form = document.getElementById('posForm');
    
    const itemsData = {
        rooms: items.rooms,
        cottages: items.cottages,
        equipment: items.equipment
    };
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'items_json';
    input.value = JSON.stringify(itemsData);
    form.appendChild(input);
    
    form.action = '/admin/walkin/booking/store';
    form.method = 'POST';
    form.submit();
}

window.addEventListener('load', () => {
    calculateNights();
});
</script>

@endsection
