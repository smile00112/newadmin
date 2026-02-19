<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <x-slot:title>
        {{ __('alfabank-payment::app.payment-error.title') }}
    </x-slot>

    <div class="grid min-h-screen place-items-center px-4 max-md:px-6">
{{--        @if (session('error'))--}}
{{--            <p class="w-full max-w-full break-words text-center text-lg text-red-600 max-md:text-base">{{ session('error') }}</p>--}}
{{--        @endif--}}
    </div>
</x-shop::layouts>
