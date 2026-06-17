@extends('admin.layout')

@section('content')

<style>
.pos-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    height: 100vh;
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
.btn-small {
    padding: 4px 8px;
    font-size: 11px;
    background: #c0392b;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
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
    <h2>🧾 Walk-In Day Tour - POS</h2>
    <a href="/admin/walkin/daytours" class="btn">← Back</a>
</div>

@if($errors->any())
    <div class="alert-error">
        @foreach($errors->all() as $error)
            ❌ {{ $error }}<br>
        @endforeach
    </div>
@endif

@if(session('warning'))
    <div class="alert-error">{{ session('warning') }}</div>
@endif

<div class="pos-container">

    {{-- LEFT: FORM --}}
    <form method="POST" class="pos-left" id="posForm">
        @csrf

        {{-- GUEST INFO --}}
        <div class="section-box">
            <h3 style="margin-top:0;">👤 Guest Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <input type="text" name="guest_name" placeholder="Guest Name *" required style="padding:8px;border:1px solid #ddd;border-radius:6px;">
                <input type="tel" name="guest_phone" placeholder="Phone *" required style="padding:8px;border:1px solid #ddd;border-radius:6px;">
            </div>
            <input type="email" name="guest_email" placeholder="Email" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;margin-bottom:12px;">
            <textarea name="notes" placeholder="Notes..." rows="2" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;"></textarea>
        </div>

        {{-- PACKAGES SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">📦 Day Tour Packages</h3>
            <div id="packagesContainer"></div>
            <select id="packageSelect" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;">
                <option value="">Select Package...</option>
                @foreach($dayTourPackages as $pkg)
                    <option value="{{ $pkg['id'] }}" data-name="{{ $pkg['name'] }}" data-price="{{ $pkg['price_per_person'] ?? 0 }}">
                        {{ $pkg['name'] }} - ₱{{ number_format($pkg['price_per_person'] ?? 0, 2) }}/person
                    </option>
                @endforeach
            </select>
            <button type="button" onclick="addPackageRow()" class="btn" style="width:100%;padding:8px;background:#0a4a6e;color:white;">+ Add Package</button>
        </div>

        {{-- COTTAGES SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">🏡 Cottages</h3>
            <div id="cottagesContainer"></div>
            <select id="cottageSelect" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;">
                <option value="">Select Cottage...</option>
                @foreach($cottages as $cottage)
                    <option value="{{ $cottage['id'] }}" data-name="{{ $cottage['name'] }}" data-price="{{ $cottage['price_per_day'] ?? 0 }}">
                        {{ $cottage['name'] }} - ₱{{ number_format($cottage['price_per_day'] ?? 0, 2) }}/night
                    </option>
                @endforeach
            </select>
            <button type="button" onclick="addCottageRow()" class="btn" style="width:100%;padding:8px;background:#0a4a6e;color:white;">+ Add Cottage</button>
        </div>

        {{-- EQUIPMENT SECTION --}}
        <div class="section-box">
            <h3 style="margin-top:0;">🧰 Equipment & Add-ons</h3>
            <div id="equipmentContainer"></div>
            <select id="equipmentSelect" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;margin-bottom:8px;">
                <option value="">Select Equipment...</option>
                @forelse($equipmentTypes as $eq)
                    <option value="{{ $eq['id'] }}" data-name="{{ $eq['name'] }}" data-price="{{ $eq['unit_price'] ?? 0 }}" data-qty="{{ $eq['quantity_available'] ?? 999 }}">
                        {{ $eq['name'] }} - ₱{{ number_format($eq['unit_price'] ?? 0, 2) }} (Qty: {{ $eq['quantity_available'] ?? 999 }})
                    </option>
                @empty
                    <option value="" disabled>No equipment available</option>
                @endforelse
            </select>
            <button type="button" onclick="addEquipmentRow()" class="btn" style="width:100%;padding:8px;background:#0a4a6e;color:white;">+ Add Equipment</button>
        </div>

    </form>

    {{-- RIGHT: SUMMARY & PAYMENT --}}
    <div class="pos-right">
        <div class="section-box">
            <h3 style="margin-top:0;">💰 Summary</h3>

            <div id="itemsSummary" style="margin-bottom:16px;max-height:400px;overflow-y:auto;">
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
                ✓ Create Transaction
            </button>
        </div>
    </div>

</div>

<script>
let items = {
    packages: [],
    cottages: [],
    equipment: []
};

function addPackageRow() {
    const select = document.getElementById('packageSelect');
    if (!select.value) {
        alert('Please select a package');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const id = Date.now();
    
    const item = {
        id: id,
        pkgId: select.value,
        name: option.dataset.name,
        guestCount: 1,
        pricePerUnit: parseFloat(option.dataset.price) || 0,
        subtotal: parseFloat(option.dataset.price) || 0
    };
    
    items.packages.push(item);
    
    const container = document.getElementById('packagesContainer');
    const html = `
        <div class="item-row" data-id="${id}">
            <span style="font-weight:600;font-size:12px;">${item.name}</span>
            <input type="number" value="1" min="1" onchange="updatePackageRow(${id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;font-size:12px;">
            <span style="font-size:12px;">₱${item.subtotal.toFixed(2)}</span>
            <button type="button" class="btn-small" onclick="removeItem('packages', ${id})">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    select.value = '';
    updateTotal();
}

function updatePackageRow(id, guestCount) {
    const item = items.packages.find(i => i.id == id);
    if (item) {
        item.guestCount = parseInt(guestCount) || 1;
        item.subtotal = item.pricePerUnit * item.guestCount;
        document.querySelector(`[data-id="${id}"] span:nth-child(3)`).textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function addCottageRow() {
    const select = document.getElementById('cottageSelect');
    if (!select.value) {
        alert('Please select a cottage');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const id = Date.now();
    
    const item = {
        id: id,
        cottageId: select.value,
        name: option.dataset.name,
        nights: 1,
        pricePerNight: parseFloat(option.dataset.price) || 0,
        subtotal: parseFloat(option.dataset.price) || 0
    };
    
    items.cottages.push(item);
    
    const container = document.getElementById('cottagesContainer');
    const html = `
        <div class="item-row" data-id="${id}">
            <span style="font-weight:600;font-size:12px;">${item.name}</span>
            <input type="number" value="1" min="1" onchange="updateCottageNights(${id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;font-size:12px;">
            <span style="font-size:12px;">₱${item.subtotal.toFixed(2)}</span>
            <button type="button" class="btn-small" onclick="removeItem('cottages', ${id})">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    select.value = '';
    updateTotal();
}

function updateCottageNights(id, nights) {
    const item = items.cottages.find(i => i.id == id);
    if (item) {
        item.nights = parseInt(nights) || 1;
        item.subtotal = item.pricePerNight * item.nights;
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('span:nth-child(3)').textContent = '₱' + item.subtotal.toFixed(2);
        updateTotal();
    }
}

function addEquipmentRow() {
    const select = document.getElementById('equipmentSelect');
    if (!select.value) {
        alert('Please select equipment');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const id = Date.now();
    const maxQty = parseInt(option.dataset.qty) || 999;
    
    const item = {
        id: id,
        equipmentId: select.value,
        name: option.dataset.name,
        quantity: 1,
        maxQuantity: maxQty,
        pricePerUnit: parseFloat(option.dataset.price) || 0,
        subtotal: parseFloat(option.dataset.price) || 0
    };
    
    items.equipment.push(item);
    
    const container = document.getElementById('equipmentContainer');
    const html = `
        <div class="item-row" data-id="${id}">
            <span style="font-weight:600;font-size:12px;">${item.name} <span style="color:#666;font-size:10px;">(Avail: ${maxQty})</span></span>
            <input type="number" value="1" min="1" max="${maxQty}" onchange="updateEquipmentQty(${id}, this.value)" style="padding:4px;border:1px solid #ddd;border-radius:4px;text-align:center;font-size:12px;">
            <span style="font-size:12px;">₱${item.subtotal.toFixed(2)}</span>
            <button type="button" class="btn-small" onclick="removeItem('equipment', ${id})">✕</button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    select.value = '';
    updateTotal();
}

function updateEquipmentQty(id, qty) {
    const item = items.equipment.find(i => i.id == id);
    if (item) {
        item.quantity = parseInt(qty) || 1;
        if (item.quantity > item.maxQuantity) item.quantity = item.maxQuantity;
        item.subtotal = item.pricePerUnit * item.quantity;
        const row = document.querySelector(`[data-id="${id}"]`);
        row.querySelector('input').value = item.quantity;
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
    const subtotal = [...items.packages, ...items.cottages, ...items.equipment]
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
    
    items.packages.forEach(item => {
        html += `<div style="font-size:11px;margin-bottom:8px;padding:8px;background:white;border-radius:4px;border-left:4px solid #0a4a6e;">
            <strong>${item.name}</strong><br>
            ${item.guestCount} guest(s) × ₱${item.pricePerUnit.toFixed(2)} = <span style="color:#0a4a6e;font-weight:600;">₱${item.subtotal.toFixed(2)}</span>
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
    
    if (!guestName || !guestPhone) {
        alert('Please enter guest name and phone');
        return;
    }
    
    if (items.packages.length === 0 && items.cottages.length === 0 && items.equipment.length === 0) {
        alert('Please add at least one item');
        return;
    }
    
    const form = document.getElementById('posForm');
    
    const itemsData = {
        packages: items.packages,
        cottages: items.cottages,
        equipment: items.equipment
    };
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'items_json';
    input.value = JSON.stringify(itemsData);
    form.appendChild(input);
    
    form.action = '/admin/walkin/daytour/store';
    form.method = 'POST';
    form.submit();
}

window.addEventListener('load', () => {
    updateTotal();
});
</script>

@endsection