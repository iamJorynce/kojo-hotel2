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
            <input type="date" id="checkInDate" name="check_in" style="width:100%;margin-bottom:8px;">
            <input type="date" id="checkOutDate" name="check_out" style="width:100%;margin-bottom:8px;">
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
            <select id="roomSelect" style="width:100%;margin-bottom:8px;">
                <option value="">-- Select Room --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room['id'] }}" data-name="{{ $room['name'] }}" data-price="200">
                        {{ $room['name'] }} ({{ $room['room_number'] }}) - ₱200/night
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addRoomItem('room')">+ Add Room</button>
            <div id="roomsContainer"></div>
        </div>

        <!-- COTTAGES -->
        <div class="section-box" id="cottagesSection" style="display:none;">
            <h3>🏡 Cottages</h3>
            <select id="cottageSelect" style="width:100%;margin-bottom:8px;">
                <option value="">-- Select Cottage --</option>
                @foreach($cottages as $cottage)
                    <option value="{{ $cottage['id'] }}" data-name="{{ $cottage['name'] }}" data-price="{{ $cottage['price_per_day'] }}">
                        {{ $cottage['name'] }} - ₱{{ number_format($cottage['price_per_day'], 2) }}/night
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addRoomItem('cottage')">+ Add Cottage</button>
            <div id="cottagesContainer"></div>
        </div>

        <!-- EQUIPMENT -->
        <div class="section-box">
            <h3>🧰 Equipment</h3>
            <select id="equipmentSelect" style="width:100%;margin-bottom:8px;">
                <option value="">-- Select Equipment --</option>
                @foreach($equipmentTypes as $eq)
                    <option value="{{ $eq['id'] }}" data-name="{{ $eq['name'] }}" data-price="{{ $eq['unit_price'] }}" data-qty="{{ $eq['quantity_available'] }}">
                        {{ $eq['name'] }} - ₱{{ number_format($eq['unit_price'], 2) }} ({{ $eq['quantity_available'] }} avail)
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn-add" onclick="addEquipment()">+ Add Equipment</button>
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
let items = { packages: [], rooms: [], equipment: [] };
let nights = 0;

function selectType(type) {
    transactionType = type;
    document.getElementById('transactionType').value = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    
    document.getElementById('datesSection').style.display = (type === 'booking') ? 'block' : 'none';
    document.getElementById('packagesSection').style.display = (type === 'day_tour') ? 'block' : 'none';
    document.getElementById('roomsSection').style.display = (type === 'booking') ? 'block' : 'none';
    document.getElementById('cottagesSection').style.display = (type === 'booking') ? 'block' : 'none';
    
    items = { packages: [], rooms: [], equipment: [] };
    document.getElementById('packagesContainer').innerHTML = '';
    document.getElementById('roomsContainer').innerHTML = '';
    document.getElementById('cottagesContainer').innerHTML = '';
    document.getElementById('equipmentContainer').innerHTML = '';
    updateTotal();
}

document.getElementById('checkInDate')?.addEventListener('change', calcNights);
document.getElementById('checkOutDate')?.addEventListener('change', calcNights);

function calcNights() {
    const ci = new Date(document.getElementById('checkInDate').value);
    const co = new Date(document.getElementById('checkOutDate').value);
    if (ci && co) {
        nights = Math.max(1, Math.ceil((co - ci) / (1000*60*60*24)));
        document.getElementById('nightsDisplay').textContent = 'Nights: ' + nights;
    }
}

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

function addRoomItem(type) {
    const selectId = type === 'room' ? 'roomSelect' : 'cottageSelect';
    const s = document.getElementById(selectId);
    if (!s.value) return alert('Pick a ' + type + '!');
    const o = s.options[s.selectedIndex];
    const id = Date.now();
    const price = parseFloat(o.dataset.price) || 0;
    const n = nights || 1;
    items.rooms.push({id, roomId: s.value, name: o.dataset.name, nights: n, pricePerNight: price, subtotal: price*n, type});
    renderRoom({id, name: o.dataset.name, nights: n, pricePerNight: price, subtotal: price*n});
    s.value = '';
    updateTotal();
}

function addEquipment() {
    const s = document.getElementById('equipmentSelect');
    if (!s.value) return alert('Pick equipment!');
    const o = s.options[s.selectedIndex];
    const id = Date.now();
    const price = parseFloat(o.dataset.price) || 0;
    items.equipment.push({id, equipId: s.value, name: o.dataset.name, quantity: 1, pricePerUnit: price, maxQty: parseInt(o.dataset.qty), subtotal: price});
    renderEquipment({id, name: o.dataset.name, quantity: 1, pricePerUnit: price, subtotal: price});
    s.value = '';
    updateTotal();
}

function renderPackage(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <input type="number" value="${item.guestCount}" min="1" onchange="updateItem('packages',${item.id},'guestCount',this.value)">
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('packages',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('packagesContainer').insertAdjacentHTML('beforeend', html);
}

function renderRoom(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <input type="number" value="${item.nights}" min="1" onchange="updateItem('rooms',${item.id},'nights',this.value)">
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('rooms',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('roomsContainer').insertAdjacentHTML('beforeend', html);
    document.getElementById('cottagesContainer').insertAdjacentHTML('beforeend', html);
}

function renderEquipment(item) {
    const html = `<div class="item-row" data-id="${item.id}">
        <span>${item.name}</span>
        <input type="number" value="${item.quantity}" min="1" max="${item.maxQty}" onchange="updateItem('equipment',${item.id},'quantity',this.value)">
        <span>₱${item.subtotal.toFixed(2)}</span>
        <button type="button" onclick="removeItem('equipment',${item.id})" style="padding:4px 8px;background:#c0392b;color:white;border:none;cursor:pointer;">✕</button>
    </div>`;
    document.getElementById('equipmentContainer').insertAdjacentHTML('beforeend', html);
}

function updateItem(type, id, field, value) {
    const item = items[type].find(i => i.id == id);
    if (!item) return;
    item[field] = parseInt(value) || 1;
    if (type === 'packages') item.subtotal = item.pricePerUnit * item.guestCount;
    else if (type === 'rooms') item.subtotal = item.pricePerNight * item.nights;
    else item.subtotal = item.pricePerUnit * item.quantity;
    document.querySelector(`[data-id="${id}"] span:nth-child(3)`).textContent = '₱' + item.subtotal.toFixed(2);
    updateTotal();
}

function removeItem(type, id) {
    items[type] = items[type].filter(i => i.id != id);
    document.querySelector(`[data-id="${id}"]`)?.remove();
    updateTotal();
}

function updateTotal() {
    const total = [...items.packages, ...items.rooms, ...items.equipment].reduce((s, i) => s + (i.subtotal || 0), 0);
    document.getElementById('subtotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    let html = '';
    items.packages.forEach(i => html += `<div style="padding:8px;background:white;border-radius:4px;margin-bottom:8px;"><strong>${i.name}</strong><br>${i.guestCount} × ₱${i.pricePerUnit.toFixed(2)}</div>`);
    items.rooms.forEach(i => html += `<div style="padding:8px;background:#fffbf0;border-radius:4px;margin-bottom:8px;"><strong>${i.name}</strong><br>${i.nights} night(s)</div>`);
    items.equipment.forEach(i => html += `<div style="padding:8px;background:#f0f0f0;border-radius:4px;margin-bottom:8px;"><strong>${i.name}</strong><br>${i.quantity} × ₱${i.pricePerUnit.toFixed(2)}</div>`);
    document.getElementById('itemsSummary').innerHTML = html || '<p style="color:#999;">Add items...</p>';
}

function createTransaction() {
    const name = document.querySelector('input[name="guest_name"]').value;
    const phone = document.querySelector('input[name="guest_phone"]').value;
    if (!name || !phone) return alert('Enter name & phone!');
    if (items.packages.length + items.rooms.length + items.equipment.length === 0) return alert('Add items!');
    if (transactionType === 'booking' && !document.getElementById('checkInDate').value) return alert('Pick dates!');
    
    const form = document.getElementById('posForm');
    form.insertAdjacentHTML('beforeend', `<input type="hidden" name="items_json" value='${JSON.stringify(items)}'`);
    form.action = '/admin/walkin/create';
    form.submit();
}

selectType('day_tour');
</script>

@endsection
