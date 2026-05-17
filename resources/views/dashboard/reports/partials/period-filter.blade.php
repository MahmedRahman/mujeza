<div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px; align-items:center;">
    <span style="font-size:13px; font-weight:800; color:#6b7280;">الفترة:</span>
    @foreach ($periodOptions as $option)
        <a
            href="{{ request()->url() }}?period={{ $option['key'] }}"
            style="display:inline-block; text-decoration:none; padding:8px 16px; border-radius:8px; font-weight:800; font-size:13px; border:1px solid {{ $period === $option['key'] ? '#d4af37' : '#d1d5db' }}; background:{{ $period === $option['key'] ? '#fffbeb' : '#fff' }}; color:{{ $period === $option['key'] ? '#92400e' : '#374151' }};"
        >
            {{ $option['label'] }}
        </a>
    @endforeach
</div>
