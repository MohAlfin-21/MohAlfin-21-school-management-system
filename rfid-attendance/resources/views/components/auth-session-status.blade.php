@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-emerald-400']) }}>
        {{ __($status) }}
    </div>
@endif
