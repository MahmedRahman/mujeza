@extends('layouts.app')

@section('title', 'إضافة منتج')
@section('page_title', 'إضافة منتج جديد')

@section('content')
    <section class="card">
        <h2 style="margin-top: 0; font-weight: 700;">إضافة منتج جديد</h2>
        <p style="margin-bottom: 18px; color: #4b5563; font-weight: 500;">
            املأ البيانات التالية لإضافة منتج كامل بكل التفاصيل (صور، فيديوهات، فوائد، أمراض، استخدام، أحجام).
        </p>
        <a href="{{ route('products.index') }}" style="display:inline-block; margin-bottom: 14px; text-decoration:none; border:1px solid #d1d5db; background:#fff; color:#111827; padding:8px 14px; border-radius:8px; font-weight:700;">
            رجوع إلى قائمة المنتجات
        </a>

        @if (session('success'))
            <div style="background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 10px; padding: 10px 12px; margin-bottom: 12px;">
                <ul style="margin: 0; padding-right: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 12px;">
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">عنوان المنتج</label>
                    <input name="title" type="text" value="{{ old('title') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">السعر</label>
                    <input name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">السعر بعد الخصم</label>
                    <input name="discount_price" type="number" step="0.01" min="0" value="{{ old('discount_price') }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:6px; font-weight:700;">متاح</label>
                    <label style="display:flex; align-items:center; gap:8px; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:#fff;">
                        <input
                            name="is_available"
                            type="checkbox"
                            value="1"
                            {{ old('is_available', '1') === '1' ? 'checked' : '' }}
                        >
                    </label>
                </div>
            </div>

            <div style="margin-top:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:700;">وصف المنتج</label>
                <textarea name="description" rows="4" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">{{ old('description') }}</textarea>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                    <label style="display:block; font-weight:700; margin:0;">الأحجام</label>
                    <button id="add-size-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                        + إضافة حجم
                    </button>
                </div>
                <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">أضف كل حجم في سطر منفصل ويمكنك الحذف بسهولة.</p>
                <div id="sizes-container" style="display:grid; gap:8px;">
                    @php
                        $oldSizes = old('sizes');
                        $sizes = is_array($oldSizes) ? $oldSizes : [''];
                    @endphp
                    @foreach ($sizes as $size)
                        <div class="size-row" style="display:grid; grid-template-columns: 1fr auto; gap:8px;">
                            <input name="sizes[]" type="text" value="{{ $size }}" placeholder="مثال: 250 جم" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                            <button type="button" class="remove-size-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                حذف
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="margin-top:12px; display: grid; gap: 12px;">
                <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                        <label style="display:block; font-weight:700; margin:0;">الفوائد</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button id="ai-benefit-btn" type="button" style="border:none; background:#111827; color:#fff; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                اقتراح بالذكاء الاصطناعي
                            </button>
                            <button id="add-benefit-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                + إضافة فائدة
                            </button>
                        </div>
                    </div>
                    <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">كل فائدة في سطر منفصل.</p>
                    <p id="ai-benefit-status" style="margin:0 0 10px; color:#6b7280; font-size:13px;"></p>
                    <div id="benefits-container" style="display:grid; gap:8px;">
                        @php
                            $oldBenefits = old('benefits');
                            $benefits = is_array($oldBenefits) ? $oldBenefits : [''];
                        @endphp
                        @foreach ($benefits as $benefit)
                            <div class="benefit-row" style="display:grid; grid-template-columns: 1fr auto; gap:8px;">
                                <input name="benefits[]" type="text" value="{{ $benefit }}" placeholder="مثال: يقوي المناعة" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                                <button type="button" class="remove-benefit-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                        <label style="display:block; font-weight:700; margin:0;">الأمراض التي يعالجها</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button id="ai-disease-btn" type="button" style="border:none; background:#111827; color:#fff; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                اقتراح بالذكاء الاصطناعي
                            </button>
                            <button id="add-disease-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                + إضافة مرض
                            </button>
                        </div>
                    </div>
                    <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">كل مرض في سطر منفصل.</p>
                    <p id="ai-disease-status" style="margin:0 0 10px; color:#6b7280; font-size:13px;"></p>
                    <div id="diseases-container" style="display:grid; gap:8px;">
                        @php
                            $oldDiseases = old('diseases');
                            $diseases = is_array($oldDiseases) ? $oldDiseases : [''];
                        @endphp
                        @foreach ($diseases as $disease)
                            <div class="disease-row" style="display:grid; grid-template-columns: 1fr auto; gap:8px;">
                                <input name="diseases[]" type="text" value="{{ $disease }}" placeholder="مثال: التهاب الحلق" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                                <button type="button" class="remove-disease-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                        <label style="display:block; font-weight:700; margin:0;">طرق الاستخدام</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button id="ai-usage-btn" type="button" style="border:none; background:#111827; color:#fff; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                اقتراح بالذكاء الاصطناعي
                            </button>
                            <button id="add-usage-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                                + إضافة طريقة
                            </button>
                        </div>
                    </div>
                    <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">كل طريقة في سطر منفصل.</p>
                    <p id="ai-usage-status" style="margin:0 0 10px; color:#6b7280; font-size:13px;"></p>
                    <div id="usage-container" style="display:grid; gap:8px;">
                        @php
                            $oldUsageMethods = old('usage_methods');
                            $usageMethods = is_array($oldUsageMethods) ? $oldUsageMethods : [''];
                        @endphp
                        @foreach ($usageMethods as $usageMethod)
                            <div class="usage-row" style="display:grid; grid-template-columns: 1fr auto; gap:8px;">
                                <input name="usage_methods[]" type="text" value="{{ $usageMethod }}" placeholder="مثال: ملعقة صباحًا على الريق" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                                <button type="button" class="remove-usage-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                    <label style="display:block; font-weight:700; margin:0;">صور المنتج</label>
                    <button id="add-image-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                        + إضافة صورة
                    </button>
                </div>
                <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">ارفع صور المنتج ثم اختر صورة واحدة كرئيسية من الجدول.</p>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; min-width:560px;">
                        <thead>
                            <tr style="background:#f8f2de;">
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:right;">ملف الصورة</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:center; width:130px;">رئيسية</th>
                                <th style="padding:10px; border:1px solid #efe3b7; text-align:center; width:120px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="images-container">
                            <tr class="image-row" data-index="0">
                                <td style="padding:10px; border:1px solid #efe3b7;">
                                    <input name="product_images[]" type="file" accept="image/*" style="width:100%;">
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                                    <input type="radio" name="primary_image_index" value="0" checked>
                                </td>
                                <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                                    <button type="button" class="remove-image-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:8px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top:12px; border:1px solid #efe3b7; border-radius:10px; padding:12px; background:#fffcf2;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
                    <label style="display:block; font-weight:700; margin:0;">فيديوهات ترويجية</label>
                    <button id="add-video-btn" type="button" style="border:none; background:#d4af37; color:#111827; padding:6px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">
                        + إضافة فيديو
                    </button>
                </div>
                <p style="margin:0 0 10px; color:#6b7280; font-size:14px;">كل رابط فيديو في سطر منفصل.</p>
                <div id="videos-container" style="display:grid; gap:8px;">
                    @php
                        $oldVideos = old('promo_videos');
                        $videos = is_array($oldVideos) ? $oldVideos : [''];
                    @endphp
                    @foreach ($videos as $video)
                        <div class="video-row" style="display:grid; grid-template-columns: 1fr auto; gap:8px;">
                            <input name="promo_videos[]" type="text" value="{{ $video }}" placeholder="https://example.com/video-1" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                            <button type="button" class="remove-video-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" style="margin-top:16px; border:none; background:#d4af37; color:#111827; padding:10px 18px; border-radius:8px; font-weight:700; font-family:inherit;">
                إضافة المنتج
            </button>
        </form>
    </section>

    <script>
        (function () {
            function setupDynamicSection(config) {
                const container = document.getElementById(config.containerId);
                const addButton = document.getElementById(config.addButtonId);

                function createRow(value = '') {
                    const row = document.createElement('div');
                    row.className = config.rowClass;
                    row.style.display = 'grid';
                    row.style.gridTemplateColumns = '1fr auto';
                    row.style.gap = '8px';
                    row.innerHTML = `
                        <input name="${config.inputName}" type="text" value="${value}" placeholder="${config.placeholder}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:inherit;">
                        <button type="button" class="${config.removeClass}" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:10px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                    `;
                    return row;
                }

                function bindRemove(button) {
                    button.addEventListener('click', function () {
                        const rows = container.querySelectorAll('.' + config.rowClass);
                        if (rows.length === 1) {
                            const input = rows[0].querySelector('input');
                            input.value = '';
                            return;
                        }
                        this.closest('.' + config.rowClass).remove();
                    });
                }

                container.querySelectorAll('.' + config.removeClass).forEach(bindRemove);

                addButton.addEventListener('click', function () {
                    const newRow = createRow();
                    container.appendChild(newRow);
                    bindRemove(newRow.querySelector('.' + config.removeClass));
                });
            }

            setupDynamicSection({
                containerId: 'sizes-container',
                addButtonId: 'add-size-btn',
                inputName: 'sizes[]',
                placeholder: 'مثال: 250 جم',
                rowClass: 'size-row',
                removeClass: 'remove-size-btn'
            });

            setupDynamicSection({
                containerId: 'benefits-container',
                addButtonId: 'add-benefit-btn',
                inputName: 'benefits[]',
                placeholder: 'مثال: يقوي المناعة',
                rowClass: 'benefit-row',
                removeClass: 'remove-benefit-btn'
            });

            setupDynamicSection({
                containerId: 'diseases-container',
                addButtonId: 'add-disease-btn',
                inputName: 'diseases[]',
                placeholder: 'مثال: التهاب الحلق',
                rowClass: 'disease-row',
                removeClass: 'remove-disease-btn'
            });

            setupDynamicSection({
                containerId: 'usage-container',
                addButtonId: 'add-usage-btn',
                inputName: 'usage_methods[]',
                placeholder: 'مثال: ملعقة صباحًا على الريق',
                rowClass: 'usage-row',
                removeClass: 'remove-usage-btn'
            });

            setupDynamicSection({
                containerId: 'videos-container',
                addButtonId: 'add-video-btn',
                inputName: 'promo_videos[]',
                placeholder: 'https://example.com/video-1',
                rowClass: 'video-row',
                removeClass: 'remove-video-btn'
            });

            const imageContainer = document.getElementById('images-container');
            const addImageBtn = document.getElementById('add-image-btn');

            function refreshImageRows() {
                const rows = imageContainer.querySelectorAll('.image-row');
                rows.forEach((row, index) => {
                    row.dataset.index = index;
                    const radio = row.querySelector('input[type="radio"][name="primary_image_index"]');
                    radio.value = String(index);
                });

                const checked = imageContainer.querySelector('input[type="radio"][name="primary_image_index"]:checked');
                if (!checked && rows.length > 0) {
                    rows[0].querySelector('input[type="radio"][name="primary_image_index"]').checked = true;
                }
            }

            function bindRemoveImage(button) {
                button.addEventListener('click', function () {
                    const rows = imageContainer.querySelectorAll('.image-row');
                    if (rows.length === 1) {
                        const fileInput = rows[0].querySelector('input[type="file"]');
                        fileInput.value = '';
                        rows[0].querySelector('input[type="radio"]').checked = true;
                        return;
                    }
                    this.closest('.image-row').remove();
                    refreshImageRows();
                });
            }

            imageContainer.querySelectorAll('.remove-image-btn').forEach(bindRemoveImage);

            addImageBtn.addEventListener('click', function () {
                const row = document.createElement('tr');
                row.className = 'image-row';
                row.innerHTML = `
                    <td style="padding:10px; border:1px solid #efe3b7;">
                        <input name="product_images[]" type="file" accept="image/*" style="width:100%;">
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                        <input type="radio" name="primary_image_index">
                    </td>
                    <td style="padding:10px; border:1px solid #efe3b7; text-align:center;">
                        <button type="button" class="remove-image-btn" style="border:1px solid #fecaca; background:#fff1f2; color:#b91c1c; padding:8px 12px; border-radius:8px; font-weight:700; font-family:inherit; cursor:pointer;">حذف</button>
                    </td>
                `;
                imageContainer.appendChild(row);
                bindRemoveImage(row.querySelector('.remove-image-btn'));
                refreshImageRows();
            });

            const aiDiseaseBtn = document.getElementById('ai-disease-btn');
            const aiDiseaseStatus = document.getElementById('ai-disease-status');
            const diseaseContainer = document.getElementById('diseases-container');
            const addDiseaseBtn = document.getElementById('add-disease-btn');

            function insertDiseases(diseaseList) {
                diseaseList.forEach((disease) => {
                    if (!disease) return;

                    const existingInputs = Array.from(diseaseContainer.querySelectorAll('input[name="diseases[]"]'));
                    const duplicate = existingInputs.some((input) => input.value.trim() === disease.trim());
                    if (duplicate) return;

                    const emptyInput = existingInputs.find((input) => input.value.trim() === '');
                    if (emptyInput) {
                        emptyInput.value = disease;
                    } else {
                        addDiseaseBtn.click();
                        const newInputs = diseaseContainer.querySelectorAll('input[name="diseases[]"]');
                        const lastInput = newInputs[newInputs.length - 1];
                        if (lastInput) {
                            lastInput.value = disease;
                        }
                    }
                });
            }

            aiDiseaseBtn.addEventListener('click', async function () {
                const title = document.querySelector('input[name="title"]')?.value || '';
                const description = document.querySelector('textarea[name="description"]')?.value || '';
                const cleanTitle = title.trim();
                const cleanDescription = description.trim();

                if (!cleanTitle || !cleanDescription) {
                    aiDiseaseStatus.textContent = 'يرجى كتابة عنوان المنتج ووصف المنتج أولاً للحصول على الاقتراحات.';
                    return;
                }

                aiDiseaseBtn.disabled = true;
                aiDiseaseStatus.textContent = 'جاري توليد اقتراحات الأمراض...';

                try {
                    const response = await fetch('{{ route('products.ai.diseases', [], false) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: cleanTitle,
                            description: cleanDescription
                        })
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'تعذر جلب الاقتراحات.');
                    }

                    const diseases = Array.isArray(data.diseases) ? data.diseases : [];
                    if (diseases.length === 0) {
                        aiDiseaseStatus.textContent = 'لم يتم العثور على اقتراحات مناسبة.';
                    } else {
                        insertDiseases(diseases);
                        aiDiseaseStatus.textContent = 'تمت إضافة اقتراحات الأمراض بنجاح.';
                    }
                } catch (error) {
                    aiDiseaseStatus.textContent = error.message || 'حدث خطأ أثناء الاتصال بالذكاء الاصطناعي.';
                } finally {
                    aiDiseaseBtn.disabled = false;
                }
            });
        })();
    </script>
    <script>
        (function () {
            function insertIntoDynamicRows(container, addButton, inputName, suggestions) {
                suggestions.forEach((item) => {
                    if (!item) return;
                    const existingInputs = Array.from(container.querySelectorAll(`input[name="${inputName}"]`));
                    const duplicate = existingInputs.some((input) => input.value.trim() === item.trim());
                    if (duplicate) return;

                    const emptyInput = existingInputs.find((input) => input.value.trim() === '');
                    if (emptyInput) {
                        emptyInput.value = item;
                    } else {
                        addButton.click();
                        const newInputs = container.querySelectorAll(`input[name="${inputName}"]`);
                        const lastInput = newInputs[newInputs.length - 1];
                        if (lastInput) {
                            lastInput.value = item;
                        }
                    }
                });
            }

            async function requestAiSuggestions(endpoint, responseKey, statusElement, buttonElement) {
                const title = document.querySelector('input[name="title"]')?.value?.trim() || '';
                const description = document.querySelector('textarea[name="description"]')?.value?.trim() || '';

                if (!title || !description) {
                    statusElement.textContent = 'يرجى كتابة عنوان المنتج ووصف المنتج أولاً للحصول على الاقتراحات.';
                    return [];
                }

                buttonElement.disabled = true;
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ title, description })
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'تعذر جلب الاقتراحات.');
                    }

                    return Array.isArray(data[responseKey]) ? data[responseKey] : [];
                } finally {
                    buttonElement.disabled = false;
                }
            }

            const aiBenefitBtn = document.getElementById('ai-benefit-btn');
            const aiBenefitStatus = document.getElementById('ai-benefit-status');
            aiBenefitBtn.addEventListener('click', async function () {
                aiBenefitStatus.textContent = 'جاري توليد اقتراحات الفوائد...';
                try {
                    const suggestions = await requestAiSuggestions(
                        '{{ route('products.ai.benefits', [], false) }}',
                        'benefits',
                        aiBenefitStatus,
                        aiBenefitBtn
                    );
                    if (suggestions.length === 0) {
                        aiBenefitStatus.textContent = 'لم يتم العثور على اقتراحات مناسبة.';
                        return;
                    }
                    insertIntoDynamicRows(
                        document.getElementById('benefits-container'),
                        document.getElementById('add-benefit-btn'),
                        'benefits[]',
                        suggestions
                    );
                    aiBenefitStatus.textContent = 'تمت إضافة اقتراحات الفوائد بنجاح.';
                } catch (error) {
                    aiBenefitStatus.textContent = error.message || 'حدث خطأ أثناء الاتصال بالذكاء الاصطناعي.';
                }
            });

            const aiUsageBtn = document.getElementById('ai-usage-btn');
            const aiUsageStatus = document.getElementById('ai-usage-status');
            aiUsageBtn.addEventListener('click', async function () {
                aiUsageStatus.textContent = 'جاري توليد اقتراحات طرق الاستخدام...';
                try {
                    const suggestions = await requestAiSuggestions(
                        '{{ route('products.ai.usage', [], false) }}',
                        'usage_methods',
                        aiUsageStatus,
                        aiUsageBtn
                    );
                    if (suggestions.length === 0) {
                        aiUsageStatus.textContent = 'لم يتم العثور على اقتراحات مناسبة.';
                        return;
                    }
                    insertIntoDynamicRows(
                        document.getElementById('usage-container'),
                        document.getElementById('add-usage-btn'),
                        'usage_methods[]',
                        suggestions
                    );
                    aiUsageStatus.textContent = 'تمت إضافة اقتراحات طرق الاستخدام بنجاح.';
                } catch (error) {
                    aiUsageStatus.textContent = error.message || 'حدث خطأ أثناء الاتصال بالذكاء الاصطناعي.';
                }
            });
        })();
    </script>
@endsection
