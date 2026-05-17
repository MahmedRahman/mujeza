@php
    $lineItems = old('items');

    if ($lineItems === null) {
        $lineItems = isset($order) && $order->items->isNotEmpty()
            ? $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
            ])->values()->all()
            : [['product_id' => '', 'quantity' => 1]];
    }

    if ($lineItems === []) {
        $lineItems = [['product_id' => '', 'quantity' => 1]];
    }
@endphp

<div style="grid-column: 1 / -1; border:1px solid #efe3b7; border-radius:12px; padding:14px; background:#fffcf2;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
        <label style="font-weight:800; color:#111827;">المنتجات <span style="color:#b91c1c;">*</span></label>
        <button type="button" id="add-order-item-row" style="border:1px solid #d4af37; background:#fffbeb; color:#92400e; padding:8px 12px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer;">
            + إضافة منتج
        </button>
    </div>

    <div id="order-items-rows" style="display:grid; gap:10px;">
        @foreach ($lineItems as $index => $line)
            <div class="order-item-row" style="display:grid; grid-template-columns: minmax(200px, 1fr) 120px 44px; gap:10px; align-items:end;">
                <div>
                    @if ($index === 0)
                        <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">المنتج (ID)</label>
                    @endif
                    <select name="items[{{ $index }}][product_id]" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                        <option value="">-- اختر منتج --</option>
                        @foreach ($products as $product)
                            @php $unitPrice = $product->discount_price ?: $product->price; @endphp
                            <option value="{{ $product->id }}" {{ (string) ($line['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                #{{ $product->id }} — {{ $product->title }} — {{ number_format((float) $unitPrice, 3) }} د.ك
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    @if ($index === 0)
                        <label style="display:block; margin-bottom:6px; font-weight:700; font-size:13px;">الكمية</label>
                    @endif
                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? 1 }}" min="1" max="9999" required
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <button type="button" class="remove-order-item-row" title="حذف"
                        style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer; height:42px;">
                    ×
                </button>
            </div>
        @endforeach
    </div>

    <p style="margin:12px 0 0; font-size:12px; color:#6b7280; font-weight:600;">
        يتم حفظ كل منتج برقم الـ ID الخاص به في قاعدة البيانات.
    </p>
</div>

<template id="order-item-row-template">
    <div class="order-item-row" style="display:grid; grid-template-columns: minmax(200px, 1fr) 120px 44px; gap:10px; align-items:end;">
        <div>
            <select data-name="product_id" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                <option value="">-- اختر منتج --</option>
                @foreach ($products as $product)
                    @php $unitPrice = $product->discount_price ?: $product->price; @endphp
                    <option value="{{ $product->id }}">
                        #{{ $product->id }} — {{ $product->title }} — {{ number_format((float) $unitPrice, 3) }} د.ك
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="number" data-name="quantity" value="1" min="1" max="9999" required
                   style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
        </div>
        <button type="button" class="remove-order-item-row" title="حذف"
                style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px; border-radius:8px; font-weight:800; font-family:inherit; cursor:pointer; height:42px;">
            ×
        </button>
    </div>
</template>

<script>
    (function () {
        const rowsContainer = document.getElementById('order-items-rows');
        const template = document.getElementById('order-item-row-template');
        const addBtn = document.getElementById('add-order-item-row');

        function reindexRows() {
            rowsContainer.querySelectorAll('.order-item-row').forEach((row, index) => {
                const productSelect = row.querySelector('[data-name="product_id"], select[name*="[product_id]"]');
                const quantityInput = row.querySelector('[data-name="quantity"], input[name*="[quantity]"]');

                if (productSelect) {
                    productSelect.name = `items[${index}][product_id]`;
                    productSelect.removeAttribute('data-name');
                }

                if (quantityInput) {
                    quantityInput.name = `items[${index}][quantity]`;
                    quantityInput.removeAttribute('data-name');
                }
            });
        }

        function bindRemoveButtons() {
            rowsContainer.querySelectorAll('.remove-order-item-row').forEach((btn) => {
                btn.onclick = function () {
                    const rows = rowsContainer.querySelectorAll('.order-item-row');
                    if (rows.length <= 1) {
                        return;
                    }
                    btn.closest('.order-item-row')?.remove();
                    reindexRows();
                };
            });
        }

        addBtn?.addEventListener('click', function () {
            const clone = template.content.cloneNode(true);
            rowsContainer.appendChild(clone);
            reindexRows();
            bindRemoveButtons();
        });

        bindRemoveButtons();
    })();
</script>
