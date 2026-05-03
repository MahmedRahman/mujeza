@extends('layouts.app')

@section('title', 'إنشاء طلب')
@section('page_title', 'إنشاء طلب')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إنشاء طلب</h2>
        <p style="margin-bottom: 14px; color: #4b5563; font-weight: 500;">
            أدخل بيانات العميل وحط المنتجات + الكميات، وإجمالي الطلب هيتحسب تلقائي.
        </p>

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orders.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">اسم العميل</label>
                    <input name="customer_name" type="text" value="{{ old('customer_name') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">تليفون العميل</label>
                    <input name="phone" type="text" value="{{ old('phone') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان التوصيل</label>
                    <textarea name="delivery_address" rows="2" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; resize:vertical;">{{ old('delivery_address') }}</textarea>
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">حالة الطلب</label>
                    <select name="status" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                        <option value="">-- اختر --</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" {{ old('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap: 10px; margin-bottom: 10px;">
                    <div style="font-weight:800;">المنتجات</div>
                    <button type="button" id="add-line-btn" style="border:none; background:#d4af37; color:#111827; padding:8px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                        + إضافة منتج
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width: 820px;">
                        <thead>
                            <tr style="background:#f8f2de;">
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">المنتج</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:160px;">السعر (د.ك)</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:140px;">الكمية</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:180px;">الإجمالي للسطر</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right; width:110px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="order-lines">
                            @php
                                $firstProductId = $products->first()?->id;
                            @endphp
                            <tr class="order-line" data-index="0">
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <select name="product_ids[]" class="product-select" data-line-price-target style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}" data-unit-price="{{ $p->price }}">{{ $p->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <input type="text" class="unit-price" value="0" readonly style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <input name="quantities[]" type="number" min="1" step="1" class="quantity-input" value="1" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <input type="text" class="line-total" value="0" readonly style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <button type="button" class="remove-line-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:8px 10px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:flex-end; gap: 12px; align-items:center; margin-top: 12px;">
                    <div style="font-weight:800; color:#374151;">الإجمالي:</div>
                    <div>
                        <input type="text" id="order-total" name="total_amount" value="0" readonly style="width:180px; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit; font-weight:800;">
                    </div>
                </div>

                <div style="margin-top: 14px;">
                    <button type="submit" style="border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                        حفظ الطلب
                    </button>
                    <a href="{{ route('orders.index') }}" style="display:inline-block; margin-inline-start: 10px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:10px 14px; border-radius:8px; font-weight:700;">
                        رجوع
                    </a>
                </div>
            </div>
        </form>

        <script>
            const productsSelectOptionsHtml = (() => {
                const firstSelect = document.querySelector('.product-select');
                if (!firstSelect) return '';
                return firstSelect.innerHTML;
            })();

            function recalcLine(line) {
                const select = line.querySelector('.product-select');
                const unitPriceInput = line.querySelector('.unit-price');
                const qtyInput = line.querySelector('.quantity-input');
                const lineTotalInput = line.querySelector('.line-total');

                const selectedOption = select.options[select.selectedIndex];
                const unitPrice = parseFloat(selectedOption?.dataset?.unitPrice || '0');
                const qty = parseInt(qtyInput.value || '0', 10);

                const lineTotal = round2(unitPrice * qty);
                unitPriceInput.value = unitPrice;
                lineTotalInput.value = lineTotal;

                recalcOrderTotal();
            }

            function recalcOrderTotal() {
                const lines = document.querySelectorAll('.order-line');
                let sum = 0;
                lines.forEach(line => {
                    const total = parseFloat((line.querySelector('.line-total')?.value || '0').toString());
                    sum += (isNaN(total) ? 0 : total);
                });
                document.getElementById('order-total').value = round2(sum);
            }

            function round2(n) {
                return Math.round((n + Number.EPSILON) * 100) / 100;
            }

            function attachLineEvents(line) {
                const select = line.querySelector('.product-select');
                const qtyInput = line.querySelector('.quantity-input');
                const removeBtn = line.querySelector('.remove-line-btn');

                select.addEventListener('change', () => recalcLine(line));
                qtyInput.addEventListener('input', () => recalcLine(line));

                removeBtn.addEventListener('click', () => {
                    const allLines = document.querySelectorAll('.order-line');
                    if (allLines.length <= 1) return;
                    line.remove();
                    recalcOrderTotal();
                });
            }

            document.querySelectorAll('.order-line').forEach(attachLineEvents);
            // init totals for first line
            const firstLine = document.querySelector('.order-line');
            if (firstLine) recalcLine(firstLine);

            document.getElementById('add-line-btn').addEventListener('click', () => {
                const tbody = document.getElementById('order-lines');
                const index = tbody.querySelectorAll('.order-line').length;

                const tr = document.createElement('tr');
                tr.className = 'order-line';
                tr.setAttribute('data-index', index);
                tr.innerHTML = `
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <select name="product_ids[]" class="product-select" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                            ${productsSelectOptionsHtml}
                        </select>
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <input type="text" class="unit-price" value="0" readonly style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <input name="quantities[]" type="number" min="1" step="1" class="quantity-input" value="1" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;" required>
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <input type="text" class="line-total" value="0" readonly style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <button type="button" class="remove-line-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:8px 10px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                            حذف
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
                attachLineEvents(tr);
                recalcLine(tr);
            });
        </script>
    </section>
@endsection

