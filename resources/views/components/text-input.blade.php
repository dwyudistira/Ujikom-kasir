@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'class' => '
            w-full
            bg-white
            text-gray-900
            border border-gray-300
            rounded-xl
            px-4 py-3
            shadow-sm
            placeholder-gray-400
            focus:border-indigo-500
            focus:ring-indigo-500
            transition
        '
    ]) !!}
>
