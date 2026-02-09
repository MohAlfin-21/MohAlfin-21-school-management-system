<x-guest-layout>
    <div class="mb-4 text-sm text-slate-400">
        {{ __('ui.auth.verify_desc') }}
    </div>

    @if (session('status') === 'ui.messages.verification_link_sent')
        <div class="mb-4 font-medium text-sm text-emerald-300">
            {{ __('ui.auth.verification_sent') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('ui.auth.resend_verification') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-slate-900">
                {{ __('ui.actions.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
