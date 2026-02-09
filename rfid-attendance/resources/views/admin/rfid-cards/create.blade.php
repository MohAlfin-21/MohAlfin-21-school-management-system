<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.rfid.card.new') }}
        </h2>
    </x-slot>

    @php
        $liveCaptureConfig = [
            'routes' => [
                'lastScan' => route('admin.rfid.last-scan'),
                'clear' => route('admin.rfid.last-scan.clear'),
                'captureStart' => route('admin.rfid.live-capture.start'),
                'captureStop' => route('admin.rfid.live-capture.stop'),
            ],
            'csrf' => csrf_token(),
            'locale' => app()->getLocale(),
            'pollInterval' => 1000,
            'devicesCount' => $devices->count(),
            'defaultDeviceId' => $devices->first() ? $devices->first()->id : null,
            'labels' => [
                'registered' => __('ui.status.registered'),
                'not_registered' => __('ui.status.not_registered'),
                'no_scan' => __('ui.rfid.live_capture.no_scan'),
                'select_device' => __('ui.rfid.live_capture.no_device'),
                'autofill_on' => __('ui.rfid.live_capture.autofill_on'),
                'autofill_off' => __('ui.rfid.live_capture.autofill_off'),
            ],
        ];
    @endphp

    <div class="py-8" x-data="rfidLiveCapture(@js($liveCaptureConfig))" x-init="init()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="glass-card p-6 space-y-6">
                    <form method="POST" action="{{ route('admin.rfid-cards.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="uid" :value="__('ui.rfid.card.uid')" />
                            <x-text-input id="uid" name="uid" class="block mt-1 w-full" :value="old('uid')" required x-ref="uidInput" />
                            <x-input-error :messages="$errors->get('uid')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="user_id" :value="__('ui.rfid.card.student')" />
                            <select id="user_id" name="user_id" class="input-base" required>
                                <option value="">{{ __('ui.actions.select') }}</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected((string) old('user_id') === (string) $student->id)>
                                        {{ $student->name }} ({{ $student->username }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('ui.rfid.card.status')" />
                            <select id="status" name="status" class="input-base">
                                <option value="active" @selected(old('status', 'active') === 'active')>{{ __('ui.status.active') }}</option>
                                <option value="lost" @selected(old('status') === 'lost')>{{ __('ui.status.lost') }}</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>{{ __('ui.status.inactive') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.rfid-cards.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.cancel') }}</a>
                            <x-primary-button>{{ __('ui.actions.create') }}</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="glass-card p-6 space-y-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-lg font-semibold">{{ __('ui.rfid.live_capture.title') }}</div>
                            <div class="text-sm text-slate-400">
                                <span x-show="listening" x-cloak>{{ __('ui.rfid.live_capture.listening') }}</span>
                                <span x-show="!listening">{{ __('ui.rfid.live_capture.idle') }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="live_device" :value="__('ui.rfid.live_capture.device')" />
                        <select id="live_device" class="input-base" x-model="selectedDeviceId" @change="handleDeviceChange">
                            <option value="">{{ __('ui.actions.select') }}</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}">
                                    {{ $device->name }}@if($device->location) ({{ $device->location }})@endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-2" x-show="devicesCount === 0" x-cloak>{{ __('ui.rfid.live_capture.no_devices') }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn-primary" x-show="!listening" @click="start">{{ __('ui.actions.start_listening') }}</button>
                        <button type="button" class="btn-secondary" x-show="listening" x-cloak @click="stop">{{ __('ui.actions.stop_listening') }}</button>
                        <button type="button" class="btn-secondary" @click="clear" :disabled="!selectedDeviceId">{{ __('ui.actions.clear') }}</button>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60" x-model="autoFillEnabled">
                            <span class="text-sm text-slate-200">{{ __('ui.rfid.live_capture.autofill') }}</span>
                        </label>
                        <div class="text-xs text-slate-400 mt-1" x-text="autoFillEnabled ? labels.autofill_on : labels.autofill_off"></div>
                    </div>

                    <div class="border-t border-slate-700/60 pt-4 space-y-3">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.rfid.live_capture.last_uid') }}</div>
                            <div class="text-lg font-semibold" x-text="lastUid || labels.no_scan"></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('ui.rfid.live_capture.scanned_at') }}</div>
                            <div class="text-sm text-slate-200" x-text="scannedAtDisplay || '-'" ></div>
                        </div>
                        <div class="flex items-center gap-2" x-show="lastUid" x-cloak>
                            <span class="badge" :class="registered ? 'badge-success' : 'badge-warning'" x-text="registered ? labels.registered : labels.not_registered"></span>
                            <span class="text-sm text-slate-200" x-show="registered && student" x-text="studentLabel"></span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500" x-show="statusMessage" x-text="statusMessage" x-cloak></div>
                </div>
            </div>
        </div>

        <script>
            function rfidLiveCapture(options) {
                return {
                    routes: options.routes,
                    csrf: options.csrf,
                    locale: options.locale || 'id',
                    pollInterval: options.pollInterval || 1000,
                    devicesCount: options.devicesCount || 0,
                    defaultDeviceId: options.defaultDeviceId || '',
                    labels: options.labels,
                    selectedDeviceId: '',
                    listening: false,
                    autoFillEnabled: true,
                    lastUid: null,
                    scannedAtDisplay: null,
                    registered: null,
                    student: null,
                    statusMessage: null,
                    intervalId: null,
                    activeDeviceId: null,
                    lastSignature: null,
                    init() {
                        if (this.devicesCount === 1 && this.defaultDeviceId) {
                            this.selectedDeviceId = String(this.defaultDeviceId);
                        }
                    },
                    async handleDeviceChange() {
                        this.resetState();
                        if (this.listening) {
                            await this.stop();
                            await this.start();
                        }
                    },
                    async start() {
                        if (!this.selectedDeviceId) {
                            this.statusMessage = this.labels.select_device;
                            return;
                        }

                        this.statusMessage = 'Requesting device capture mode...';
                        const captureOk = await this.setCaptureMode(true, this.selectedDeviceId);
                        if (!captureOk) {
                            return;
                        }
                        this.activeDeviceId = String(this.selectedDeviceId);

                        this.statusMessage = null;
                        this.listening = true;
                        this.poll();
                        this.intervalId = setInterval(() => this.poll(), this.pollInterval);
                    },
                    async stop() {
                        this.listening = false;
                        if (this.intervalId) {
                            clearInterval(this.intervalId);
                            this.intervalId = null;
                        }

                        if (this.activeDeviceId) {
                            await this.setCaptureMode(false, this.activeDeviceId);
                            this.activeDeviceId = null;
                        }
                    },
                    resetState() {
                        this.lastUid = null;
                        this.scannedAtDisplay = null;
                        this.registered = null;
                        this.student = null;
                        this.lastSignature = null;
                    },
                    async setCaptureMode(enabled, deviceId) {
                        const url = enabled ? this.routes.captureStart : this.routes.captureStop;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ device_id: Number(deviceId) }),
                            });

                            let data = null;
                            try {
                                data = await response.json();
                            } catch (e) {
                                // ignore
                            }

                            if (!response.ok || !data || !data.ok) {
                                this.statusMessage = data?.message || 'Failed to toggle capture mode.';
                                return false;
                            }

                            return true;
                        } catch (error) {
                            console.warn(error);
                            this.statusMessage = 'Capture mode request failed (network).';
                            return false;
                        }
                    },
                    applyUid(uid) {
                        const input = this.$refs.uidInput;
                        if (!input) {
                            return;
                        }
                        const current = input.value.trim();
                        const shouldAutofill = this.autoFillEnabled || current === '';
                        if (!shouldAutofill) {
                            return;
                        }
                        input.value = uid;
                        input.dispatchEvent(new Event('input'));
                    },
                    async poll() {
                        if (!this.listening || !this.selectedDeviceId) {
                            return;
                        }

                        try {
                            const url = `${this.routes.lastScan}?device_id=${this.selectedDeviceId}`;
                            const response = await fetch(url, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                                cache: 'no-store',
                                credentials: 'same-origin',
                            });

                            if (!response.ok) {
                                return;
                            }

                            const data = await response.json();
                            if (!data.ok) {
                                return;
                            }

                            if (!data.uid) {
                                this.resetState();
                                return;
                            }

                            const signature = `${data.uid}|${data.scanned_at ?? ''}`;
                            if (this.lastSignature === signature) {
                                return;
                            }
                            this.lastSignature = signature;

                            this.lastUid = data.uid;
                            this.registered = data.registered;
                            this.student = data.student;
                            this.scannedAtDisplay = data.scanned_at
                                ? new Date(data.scanned_at).toLocaleString(this.locale, { hour12: false })
                                : null;

                            this.applyUid(data.uid);
                        } catch (error) {
                            console.warn(error);
                        }
                    },
                    async clear() {
                        if (!this.selectedDeviceId) {
                            return;
                        }

                        try {
                            await fetch(this.routes.clear, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ device_id: this.selectedDeviceId }),
                            });
                        } catch (error) {
                            console.warn(error);
                        }

                        this.resetState();
                    },
                    get studentLabel() {
                        if (!this.student) {
                            return '';
                        }
                        return `${this.student.name} (${this.student.username})`;
                    },
                };
            }
        </script>
    </div>
</x-app-layout>
