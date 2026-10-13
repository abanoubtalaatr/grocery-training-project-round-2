@extends('layouts.app')

@section('title', trans('auth.verify_email.title'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ trans('auth.verify_email.heading') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ trans('auth.verify_email.subtitle') }}</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('verify-email') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700">{{ trans('auth.verify_email.otp') }}</label>
                    <input type="text" name="otp" id="otp" required autofocus maxlength="8"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-center text-2xl tracking-widest">
                </div>

                <button type="submit"
                    class="w-full bg-brand-500 text-white py-2 px-4 rounded-lg hover:bg-brand-600 transition font-medium">
                    {{ trans('auth.verify_email.submit') }}
                </button>
            </div>
        </form>

        <div class="mt-6 text-center" x-data="{ countdown: 0, active: true }" x-init="
            countdown = 60;
            let timer = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(timer);
                    active = true;
                }
            }, 1000);
        ">
            <template x-if="!active">
                <p class="text-sm text-gray-500">{{ trans('auth.verify_email.resend_in', ['seconds' => '']) }} <span x-text="countdown"></span></p>
            </template>
            <template x-if="active">
                <a href="{{ route('verify-email.resend') }}" class="text-sm text-brand-500 hover:text-brand-600 font-medium"
                    @click.prevent="
                        active = false;
                        countdown = 60;
                        fetch('{{ route('verify-email.resend') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        let timer = setInterval(() => {
                            countdown--;
                            if (countdown <= 0) { clearInterval(timer); active = true; }
                        }, 1000);
                    ">
                    {{ trans('auth.verify_email.resend') }}
                </a>
            </template>
        </div>
    </div>
</div>
@endsection
