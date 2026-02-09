<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.nav.rfid_cards') }}
            </h2>
            <a href="{{ route('admin.rfid-cards.create') }}" class="btn-primary">
                {{ __('ui.rfid.card.new_card') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('ui.rfid.card.uid') }}</th>
                                    <th class="py-2">{{ __('ui.rfid.card.student') }}</th>
                                    <th class="py-2">{{ __('ui.rfid.card.status') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $statusStyles = [
                                        'active' => 'badge-success',
                                        'lost' => 'badge-danger',
                                        'inactive' => 'badge-neutral',
                                    ];
                                @endphp
                                @foreach ($cards as $card)
                                    <tr>
                                        <td class="py-2 font-mono">{{ $card->uid }}</td>
                                        <td class="py-2">{{ $card->user?->name }} ({{ $card->user?->username }})</td>
                                        <td class="py-2">
                                            <span class="badge {{ $statusStyles[$card->status] ?? 'badge-neutral' }}">
                                                {{ __('ui.status.' . $card->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-right">
                                            <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('admin.rfid-cards.edit', $card) }}">{{ __('ui.actions.edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $cards->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
