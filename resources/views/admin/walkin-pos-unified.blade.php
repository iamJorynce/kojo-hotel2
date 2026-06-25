@extends('admin.layout')

@section('content')

<style>
.pos-container {
    display: grid;
    grid-template-columns: 2.5fr 1fr;
    gap: 20px;
}
.section-box {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}
.type-btn {
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    font-weight: 600;
}
.type-btn.active { background: #0a4a6e; color: white; }
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
select, input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 12px;
}
select:disabled {
    background: #f0f0f0;
    cursor: not-allowed;
    opacity: 0.6;
}
button.btn-add {
    width: 100%;
    padding: 8px;
    background: #0a4a6e;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 8px;
}
button.btn-add:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>

<div class="topbar">
    <h2>🧾 Walk-In POS</h2>
    <a href="/admin/walkin/transactions" class="btn">← Back</a>
</div>

<div class="pos-container">
    <form method="POST" id="posForm">
        @csrf

        <!-- TYPE -->
        <div class="section-box">
            <h3>📋 Type</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                <button type="button" class="type-btn active" onclick="selectType('day_tour')">🧾 Day Tour</button>
                <button type="button" class="type-btn" onclick="selectType('booking')">🛏️ Booking</button>
                <button type="button" class="type-btn" onclick="selectType('equipment')">🧰 Equipment</button>
            </div>
            <input type="hidden" id="transactionType" name="transaction_type" value="day_tour">
        </div>

        <!-- GUEST -->
        <div class="section-box">
            <h3>👤 Guest Info</h3>
            <input type="text" name="guest_name" placeholder="Name *" required style="width:100%;margin-bottom:8px;">
            <input type="tel" name="guest_phone" placeholder="Phone *" required style="width:100%;margin-bottom:8px;">
            <input type="email" name="guest_email" placeholder="Email" style="width:100%;">
        </div>

        <!-- DATES FOR BOOKING -->
        <div class="section-box" id="datesSection" style="display:none;">
            <h3>📅 Dates</h3>
            <input type="date" id="checkInDate" name="check_in" min="{{ date('Y-m-d') }}" onchange="calcNights()" style="width:100%;margin-bottom:8px;">
            <input type="date" id="checkOutDate" name="check_out" min="{{ date('Y-m-d') }}" onchange="calcNights()" style="width:100%;margin-bottom:8px;">
            <p id="nightsDisplay" style="font-size:12px;color:#666;margin:0;">Nights: 0</p>
        </div>

        <!-- PACKAGES -->
        <div class="section-box" id="packagesSection">
            <h3>📦 Packages</h3>
            <select id="packageSelect" style="width:100%;margin-bottom:8px;">
                <option value="">-- Select Package --</option>
                @foreach($dayTourPackages as $pkg)
                    <option value="{{ $pkg['id'] }}" data-name="{{ $pkg['name'] }}" data-price="{{ $pkg['price_per_person'] ?? 0 }}">
                        {{ $pkg['name'] }} - ₱{{ number_format($pkg['price_per_person'] ?? 0, 2) }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addPackage()">+ Add Package</button>
            <div id="packagesContainer"></div>
        </div>

        <!-- ROOMS -->
        <div class="section-box" id="roomsSection" style="display:none;">
            <h3>🏨 Rooms</h3>
            <select id="roomSelect" style="width:100%;margin-bottom:8px;" disabled title="Select check-in and check-out dates first">
                <option value="">-- Select Room --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room['id'] }}" data-name="{{ $room['name'] }}" data-price="{{ $room['price_per_night'] ?? 200 }}">
                        {{ $room['name'] }} ({{ $room['room_number'] }}) - ₱{{ number_format($room['price_per_night'] ?? 200, 2) }}/night
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addRoomItem('room')" disabled title="Select check-in and check-out dates first">+ Add Room</button>
            <div id="roomsContainer"></div>
        </div>

        <!-- COTTAGES -->
        <div class="section-box" id="cottagesSection" style="display:none;">
            <h3>🏡 Cottages</h3>
            <select id="cottageSelect" style="width:100%;margin-bottom:8px;" disabled title="Select check-in and check-out dates first">
                <option value="">-- Select Cottage --</option>
                @foreach($cottages as $cottage)
                    <option value="{{ $cottage['id'] }}" data-name="{{ $cottage['name'] }}" data-price="{{ $cottage['price_per_day'] }}">
                        {{ $cottage['name'] }} - ₱{{ number_format($cottage['price_per_day'], 2) }}/night
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addRoomItem('cottage')" disabled title="Select check-in and check-out dates first">+ Add Cottage</button>
            <div id="cottagesContainer"></div>
        </div>

        <!-- EQUIPMENT -->
        <div class="section-box">
            <h3>🧰 Equipment</h3>
            <select id="equipmentSelect" style="width:100%;margin-bottom:8px;" disabled title="Select check-in and check-out dates first">
                <option value="">-- Select Equipment --</option>
                @foreach($equipmentTypes as $eq)
                    <option value="{{ $eq['id'] }}" data-name="{{ $eq['name'] }}" data-price="{{ $eq['unit_price'] }}" data-qty="{{ $eq['quantity_available'] }}">
                        {{ $eq['name'] }} - ₱{{ number_format($eq['unit_price'], 2) }} ({{ $eq['quantity_available'] }} avail)
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addEquipment()" disabled title="Select check-in and check-out dates first">+ Add Equipment</button>
            <div id="equipmentContainer"></div>
        </div>

        <textarea name="notes" placeholder="Notes..." rows="2" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;"></textarea>
    </form>

    <!-- SUMMARY -->
    <div class="pos-right">
        <div class="section-box">
            <h3>💰 Summary</h3>
            <div id="itemsSummary" style="margin-bottom:16px;max-height:400px;overflow-y:auto;font-size:11px;">
                <p style="color:#999;">Add items...</p>
            </div>
            <div style="background:#f0f0f0;padding:12px;border-radius:8px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
            </div>
            <div class="summary-total">
                <span>TOTAL:</span>
                <span id="totalAmount">₱0.00</span>
            </div>
            <button type="button" onclick="createTransaction()" class="btn-add" style="background:#1a6b3c;margin-top:16px;padding:14px;font-weight:600;">✓ Create</button>
        </div>
    </div>
</div>

<script>
let transactionType = 'day_tour';
let items = { packages: [], rooms: [], cottages: [], equipment: [] };
let nights = 0;

// SELECT TRANSACTION TYPE
function selectType(type) {
    transactionType = type;
    document.getElementById('transactionType').value = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    
    document.getElementById('datesSection').style.display = (type === 'booking') ? 'block' : 'none';
    document.getElementById('packagesSection').style.display = (type === 'day_tour') ? 'block' : 'none';
    document.getElementById('roomsSection').style.display = (type === 'booking') ? 'block' : 'none';
    document.getElementById('cottagesSection').style.display = (type === 'booking') ? 'block' : 'none';
    
    items = { packages: [], rooms: [], cottages: [], equipment: [] };
    document.getElementById('packagesContainer').innerHTML = '';
    document.getElementById('roomsContainer').innerHTML = '';
    document.getElementById('cottagesContainer').innerHTML = '';
    document.getElementById('equipmentContainer').innerHTML = '';
    
    validateBookingDates();
    updateTotal();
}

// CALCULATE NIGHTS & VALIDATE DATES
function calcNights() {
    const ci = new Date(document.getElementById('checkInDate').value);
    const co = new Date(document.getElementById('checkOutDate').value);
    
    if (ci && co) {
        if (co <= ci) {
            alert('Check-out must be after check-in');
            document.getElementById('checkOutDate').value = '';
            nights = 0;
            document.getElementById('nightsDisplay').textContent = 'Nights: 0';
            validateBookingDates();
            return;
        }
        nights = Math.max(1, Math.ceil((co - ci) / (1000*60*60*24)));
        document.getElementById('nightsDisplay').textContent = 'Nights: ' + nights;
    }
    
    validateBookingDates();
}

// ENABLE/DISABLE ROOMS/COTTAGES/EQUIPMENT BASED ON DATES
function validateBookingDates() {
    const checkIn = document.getElementById('checkInDate').value;
    const checkOut = document.getElementById('checkOutDate').value;
    const hasDate = checkIn && checkOut;
    
    if (transactionType === 'booking') {
        document.getElementById('roomSelect').disabled = !hasDate;
        document.getElementById('cottageSelect').disabled = !hasDate;
        document.getElementById('equipmentSelect').disabled = !hasDate;
        document.querySelector('button[onclick="addRoomItem(\'room\')"]').disabled = !hasDate;
        document.querySelector('button[onclick="addRoomItem(\'cottage\')"]').disabled = !hasDate;
        document.querySelector('button[onclick="addEquipment()"]').disabled = !hasDate;
    }
}

// ADD PACKAGE
function addPackage() {
    const s = document.getElementById('packageSelect');
    if (!s.value) return alert('Pick a package!');
    const o = s.options[s.selectedIndex];
    const id = Date.now();
    const price = parseFloat(o.dataset.price) || 0;
    items.packages.push({id, pkgId: s.value, name: o.dataset.name, guestCount: 1, pricePerUnit: price, subtotal: price});
    renderPackage({id, name: o.dataset.name, guestCount: 1, pricePerUnit: price, subtotal: price});
    s.value = '';
    updateTotal();
}

// ADD ROOM OR COTTAGE
function addRoomItem(type) {
    const selectId = type === 'room' ? 'roomSelect' : 'cottageSelect';
    const s = document.getElementById(selectId);
    if (!s.value) return alert('Pick a ' + type + '!');
    const o = s.options[s.selectedIndex];
    const id = Date.now();
    const price = parseFloat(o.dataset.price) || 0;
    const n = nights || 1;
    const item = {id, roomId: s.value, name: o.dataset.name, nights: n, pricePerNight: price, subtotal: price*n};
    
    if (type === 'cottage') {
        items.cottages.push(item);
        renderCottage(item);
    } else {
        items.rooms.push(item);
        renderRoom(item);
    }
    s.value = '';
    updateTotal();
}

// ADD EQUIPMENT
function addEquipment() {
    const s = document.getElementById('equipmentSelect');
    if (!s.value) return alert('Pick equipment!');
    const o = s.options[s.selectedIndex];
    const id = Date.now();
    const price = parseFloat(o.dataset.price) || 0;
    const maxQty = parseInt(o.dataset.qty) || 999;
    items.equipment.push({id, equipId: s.value, name: o.dataset.name, quantity: 1, pricePerUnit: price, maxQty: maxQty, subtotal: price});
    renderEquipment({id, name: o.dataset.name, quantity: 1, pricePerUnit: price, maxQty: maxQty, subtotal: price});
    s.value = '';
    updateTotal();
}

// RENDER PACKAGE
function renderPackage(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <input type="number" value="${item.guestCount}" min="1" onchange="updateItem('packages',${item.id},'guestCount',this.value)">
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('packages',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('packagesContainer').insertAdjacentHTML('beforeend', html);
}

// RENDER ROOM (READ-ONLY NIGHTS)
function renderRoom(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <span style="font-weight:600;">${item.nights} nights</span>
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('rooms',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('roomsContainer').insertAdjacentHTML('beforeend', html);
}

// RENDER COTTAGE (READ-ONLY NIGHTS)
function renderCottage(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <span style="font-weight:600;">${item.nights} nights</span>
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('cottages',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('cottagesContainer').insertAdjacentHTML('beforeend', html);
}

// RENDER EQUIPMENT (WITH QUANTITY VALIDATION)
function renderEquipment(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <input type="number" value="${item.quantity}" min="1" max="${item.maxQty}" onchange="validateEquipmentQty(${item.id}, this)">
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('equipment',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('equipmentContainer').insertAdjacentHTML('beforeend', html);
}

// UPDATE ITEM (PACKAGES & EQUIPMENT ONLY)
function updateItem(type, id, field, value) {
    const item = items[type].find(i => i.id == id);
    if (!item) return;
    item[field] = parseInt(value) || 1;
    if (type === 'packages') item.subtotal = item.pricePerUnit * item.guestCount;
    else item.subtotal = item.pricePerUnit * item.quantity;
    document.querySelector(`[data-id="${id}"] span:nth-child(3)`).textContent = '₱' + item.subtotal.toFixed(2);
    updateTotal();
}

// VALIDATE EQUIPMENT QUANTITY
function validateEquipmentQty(id, input) {
    const item = items.equipment.find(i => i.id == id);
    if (!item) return;
    
    const newQty = parseInt(input.value) || 1;
    if (newQty > item.maxQty) {
        alert(`Only ${item.maxQty} available!`);
        input.value = item.maxQty;
        item.quantity = item.maxQty;
    } else if (newQty < 1) {
        input.value = 1;
        item.quantity = 1;
    } else {
        item.quantity = newQty;
    }
    
    item.subtotal = item.pricePerUnit * item.quantity;
    document.querySelector(`[data-id="${id}"] span:nth-child(3)`).textContent = '₱' + item.subtotal.toFixed(2);
    updateTotal();
}

// REMOVE ITEM
function removeItem(type, id) {
    items[type] = items[type].filter(i => i.id != id);
    document.querySelector(`[data-id="${id}"]`)?.remove();
    updateTotal();
}

// UPDATE TOTAL & SUMMARY
function updateTotal() {
    const total = [...items.packages, ...items.rooms, ...items.cottages, ...items.equipment]
        .reduce((s, i) => s + (i.subtotal || 0), 0);
    document.getElementById('subtotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    
    let html = '';
    items.packages.forEach(i => html += `<div style="padding:8px;background:white;border-radius:4px;margin-bottom:8px;"><strong>${i.name}</strong><br>${i.guestCount} × ₱${i.pricePerUnit.toFixed(2)}</div>`);
    items.rooms.forEach(i => html += `<div style="padding:8px;background:#fffbf0;border-radius:4px;margin-bottom:8px;"><strong>🏨 ${i.name}</strong><br>${i.nights} night(s)</div>`);
    items.cottages.forEach(i => html += `<div style="padding:8px;background:#f0f0f0;border-radius:4px;margin-bottom:8px;"><strong>🏡 ${i.name}</strong><br>${i.nights} night(s)</div>`);
    items.equipment.forEach(i => html += `<div style="padding:8px;background:#e0e0e0;border-radius:4px;margin-bottom:8px;"><strong>${i.name}</strong><br>${i.quantity} × ₱${i.pricePerUnit.toFixed(2)}</div>`);
    
    document.getElementById('itemsSummary').innerHTML = html || '<p style="color:#999;">Add items...</p>';
}

// CREATE TRANSACTION
function createTransaction() {
    const name = document.querySelector('input[name="guest_name"]').value;
    const phone = document.querySelector('input[name="guest_phone"]').value;
    if (!name || !phone) return alert('Enter name & phone!');
    if (items.packages.length + items.rooms.length + items.cottages.length + items.equipment.length === 0) return alert('Add items!');
    if (transactionType === 'booking' && !document.getElementById('checkInDate').value) return alert('Pick dates!');
    
    const form = document.getElementById('posForm');
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="items_json" value='${JSON.stringify(items)}'`);
    form.action = '/admin/walkin/create';
    form.submit();
}

// INITIALIZE
selectType('day_tour');
</script>

@endsection