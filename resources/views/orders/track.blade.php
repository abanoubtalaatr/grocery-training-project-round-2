@extends('layouts.app')

@section('title', trans('orders.tracking.title') . ' - ' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto"
     x-data="tracking({{ json_encode([
         'trackUrl' => route('orders.track-json', $order->id),
         'chatMessagesUrl' => route('orders.messages.index', $order->id),
         'chatSendUrl' => route('orders.messages.store', $order->id),
         'chatUnreadUrl' => route('orders.messages.unread', $order->id),
         'status' => $order->status,
         'position' => $order->status_position,
         'hasGps' => $hasGps,
         'driverName' => $order->driver?->full_name ?? $order->driver?->username,
         'driverPhone' => $order->driver?->phone,
         'driverAvatar' => $order->driver?->profile_image_url,
         'estimatedDelivery' => $order->estimated_delivery_time?->toIso8601String(),
         'isSlaBreached' => $isSlaBreached,
         'deliveredAt' => $order->delivered_at?->toIso8601String(),
         'cancelledAt' => $order->cancelled_at?->toIso8601String(),
         'isActive' => !in_array($order->status, ['delivered', 'cancelled']),
         'hasDriver' => $order->driver_id !== null,
         'currentUserId' => auth()->id(),
     ]) }})">
    <div class="mb-6">
        <a href="{{ route('orders.index') }}" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; {{ trans('orders.tracking.back_to_orders') }}
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ trans('orders.tracking.heading') }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ trans('orders.tracking.order_number', ['number' => $order->order_number]) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if(!in_array($order->status, ['delivered', 'cancelled']))
                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            {{ trans('orders.tracking.auto_refresh') }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          :class="statusColor">
                        <span x-text="statusDesc"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Status Tracker --}}
        @if($order->status !== 'cancelled')
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.order_progress') }}</h3>
                <div class="flex items-center justify-between">
                    @foreach($positions as $index => $step)
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-300"
                                 :class="currentPosition >= {{ $step['position'] }} ? 'bg-brand-500 text-white' : 'bg-gray-200 text-gray-500'">
                                <template x-if="currentPosition > {{ $step['position'] }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </template>
                                <template x-if="currentPosition <= {{ $step['position'] }}">
                                    <span>{{ $step['position'] }}</span>
                                </template>
                            </div>
                            <p class="mt-2 text-xs text-center font-medium transition-colors duration-300"
                               :class="currentPosition >= {{ $step['position'] }} ? 'text-brand-600' : 'text-gray-400'">
                                {{ $step['label'] }}
                            </p>
                            @if($order->{$step['timestamp_field']})
                                <p class="mt-1 text-xs text-gray-400">{{ $order->{$step['timestamp_field']}->format('g:i A') }}</p>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-1 transition-colors duration-300"
                                 :class="currentPosition > {{ $step['position'] }} ? 'bg-brand-500' : 'bg-gray-200'"></div>
                        @endif
                    @endforeach
                </div>

                {{-- SLA Status --}}
                @if($order->estimated_delivery_time && !in_array($order->status, ['delivered', 'cancelled']))
                    <div class="mt-4 p-3 rounded-lg border transition-colors duration-300"
                         :class="isSlaBreached ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200'">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">{{ trans('orders.tracking.estimated_delivery') }}:</span>
                                {{ $order->estimated_delivery_time->format('M d, Y \a\t g:i A') }}
                            </p>
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full"
                                  :class="isSlaBreached ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                                <template x-if="!isSlaBreached">
                                    <span>{{ trans('orders.tracking.on_time') }}</span>
                                </template>
                                <template x-if="isSlaBreached">
                                    <span>{{ trans('orders.tracking.delayed') }}</span>
                                </template>
                            </span>
                        </div>
                        @if($order->delivery_speed)
                            <p class="mt-1 text-xs text-gray-500">
                                {{ trans('common.delivery_speed') }}: {{ ucfirst($order->delivery_speed) }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Delivered/Cancelled Banner --}}
        <template x-if="status === 'delivered'">
            <div class="p-6 border-b border-gray-200 bg-green-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-green-800">{{ trans('orders.tracking.completed') }}</p>
                        <p class="text-xs text-green-600" x-show="deliveredAt" x-text="'{{ trans("orders.tracking.delivered_at", ["time" => ""]) }}' + formatTime(deliveredAt)"></p>
                    </div>
                </div>
            </div>
        </template>
        <template x-if="status === 'cancelled'">
            <div class="p-6 border-b border-gray-200 bg-red-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-red-800">{{ trans('orders.cancel.success') }}</p>
                </div>
            </div>
        </template>

        {{-- Driver Info --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.tracking.driver_info') }}</h3>
            <template x-if="driverName">
                <div class="flex items-center gap-4">
                    <template x-if="driverAvatar">
                        <img :src="driverAvatar" :alt="driverName" class="w-12 h-12 rounded-full object-cover">
                    </template>
                    <template x-if="!driverAvatar">
                        <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center">
                            <span class="text-brand-600 font-bold text-lg" x-text="driverName?.charAt(0)?.toUpperCase()"></span>
                        </div>
                    </template>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900" x-text="driverName"></p>
                        <p class="text-xs text-gray-500" x-show="driverPhone" x-text="driverPhone"></p>
                    </div>
                    <template x-if="driverPhone">
                        <a :href="'tel:' + driverPhone" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ trans('orders.tracking.call_driver') }}
                        </a>
                    </template>
                </div>
            </template>
            <template x-if="!driverName">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">{{ trans('orders.tracking.no_driver') }}</p>
                </div>
            </template>
        </div>

        {{-- Chat with Driver --}}
        <template x-if="hasDriver && isActive">
            <div class="border-b border-gray-200">
                <div class="p-6 pb-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">{{ trans('orders.tracking.chat_with_driver') }}</h3>
                        <button @click="chatOpen = !chatOpen" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                            <span x-text="chatOpen ? '{{ trans("orders.tracking.hide_chat") }}' : '{{ trans("orders.tracking.show_chat") }}'"></span>
                            <template x-if="chatUnreadCount > 0">
                                <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full" x-text="chatUnreadCount"></span>
                            </template>
                        </button>
                    </div>
                </div>
                <template x-if="chatOpen">
                    <div class="px-6 pb-6">
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            {{-- Messages area --}}
                            <div class="h-64 overflow-y-auto p-4 space-y-3 bg-gray-50" x-ref="chatContainer" x-init="$watch('chatMessages', () => { $nextTick(() => $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight) })">
                                <template x-if="chatMessages.length === 0">
                                    <p class="text-sm text-gray-400 text-center py-8">{{ trans('orders.tracking.no_messages') }}</p>
                                </template>
                                <template x-for="msg in chatMessages" :key="msg.id">
                                    <div class="flex" :class="msg.sender_id === currentUserId ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[75%] rounded-lg px-3 py-2 text-sm"
                                             :class="msg.sender_id === currentUserId ? 'bg-brand-500 text-white' : 'bg-white border border-gray-200 text-gray-900'">
                                            <p x-text="msg.body" class="whitespace-pre-wrap break-words"></p>
                                            <p class="text-[10px] mt-1 opacity-60" :class="msg.sender_id === currentUserId ? 'text-white' : 'text-gray-400'" x-text="formatChatTime(msg.created_at)"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            {{-- Input area --}}
                            <div class="border-t border-gray-200 p-3 flex gap-2">
                                <input type="text" x-model="chatMessageBody" @keydown.enter.prevent="sendChatMessage()"
                                       :placeholder="'{{ trans('orders.tracking.type_message') }}'"
                                       :disabled="chatSending"
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 disabled:opacity-50">
                                <button @click="sendChatMessage()" :disabled="!chatMessageBody.trim() || chatSending"
                                        class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="!chatSending" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <svg x-show="chatSending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- GPS Status --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.tracking.live_tracking') }}</h3>
            <template x-if="hasGps">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-sm text-green-700 font-medium">{{ trans('orders.tracking.driver_assigned') }}</span>
                    </div>
                    <p class="text-xs text-gray-500" x-show="gpsUpdatedAt" x-text="'{{ trans("orders.tracking.gps_last_updated", ["time" => ""]) }}' + formatTime(gpsUpdatedAt)"></p>
                </div>
            </template>
            <template x-if="!hasGps">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">{{ trans('orders.tracking.gps_unavailable') }}</p>
                </div>
            </template>
        </div>

        {{-- Delivery Destination --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">
                {{ $order->delivery_type === 'delivery' ? trans('orders.tracking.delivery_destination') : trans('orders.tracking.pickup_destination') }}
            </h3>
            @if($order->delivery_type === 'delivery' && $order->address)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $order->address->full_address }}</p>
                        @if($order->address->phone)
                            <p class="mt-1 text-xs text-gray-500">{{ $order->address->phone }}</p>
                        @endif
                    </div>
                </div>
            @elseif($order->delivery_type === 'pickup' && $order->pickup_location)
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $order->pickup_location }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ trans('common.pickup_hours') }}</p>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">{{ trans('common.no_addresses') }}</p>
            @endif
        </div>

        {{-- Order Items Summary --}}
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ trans('orders.detail.items') }}</h3>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-4">
                        @if($item->meal && $item->meal->image_path)
                            <img src="{{ asset('storage/' . $item->meal->image_path) }}" alt="{{ $item->meal->localized_name }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $item->meal->localized_name ?? $item->meal->title ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ trans('common.qty') }}: {{ $item->quantity }}</p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">${{ number_format((float) $item->subtotal, 2) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between text-base font-bold">
                    <span class="text-gray-900">{{ trans('common.total') }}</span>
                    <span class="text-gray-900">${{ number_format((float) $order->total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="p-6 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition">
                {{ trans('orders.tracking.view_order') }}
            </a>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                {{ trans('orders.tracking.back_to_orders') }}
            </a>
        </div>
    </div>
</div>

<script>
function tracking(initial) {
    return {
        ...initial,
        statusDesc: '',
        statusColor: '',
        currentPosition: initial.position,
        gpsUpdatedAt: null,
        deliveredAt: initial.deliveredAt,
        cancelledAt: initial.cancelledAt,
        chatOpen: false,
        chatMessages: [],
        chatMessageBody: '',
        chatSending: false,
        chatUnreadCount: 0,
        chatLastMessageId: null,

        init() {
            this.updateStatusColors();
            if (initial.isActive) {
                this.startPolling();
            }
            if (initial.hasDriver) {
                this.loadChatMessages();
            }
        },

        updateStatusColors() {
            const colors = {
                awaiting_payment: 'bg-yellow-100 text-yellow-800',
                placed: 'bg-blue-100 text-blue-800',
                processing: 'bg-indigo-100 text-indigo-800',
                shipping: 'bg-purple-100 text-purple-800',
                out_for_delivery: 'bg-orange-100 text-orange-800',
                delivered: 'bg-green-100 text-green-800',
                cancelled: 'bg-red-100 text-red-800',
            };
            const descs = {
                awaiting_payment: '{{ trans("orders.detail.status_placed") }}',
                placed: '{{ trans("orders.detail.status_placed") }}',
                processing: '{{ trans("orders.detail.status_processing") }}',
                shipping: '{{ trans("orders.detail.status_shipping") }}',
                out_for_delivery: '{{ trans("orders.detail.status_out_for_delivery") }}',
                delivered: '{{ trans("orders.detail.status_delivered") }}',
                cancelled: '{{ trans("orders.cancel.success") }}',
            };
            this.statusColor = colors[this.status] || 'bg-gray-100 text-gray-800';
            this.statusDesc = descs[this.status] || this.status;
        },

        startPolling() {
            setInterval(() => {
                fetch(this.trackUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(json => {
                    if (!json.success) return;
                    const d = json.data;
                    this.status = d.status;
                    this.statusDesc = d.status_description;
                    this.currentPosition = d.position;
                    this.hasGps = d.has_gps;
                    this.gpsUpdatedAt = d.driver_location_updated_at;
                    this.driverName = d.driver?.name || null;
                    this.driverPhone = d.driver?.phone || null;
                    this.driverAvatar = d.driver?.avatar || null;
                    this.estimatedDelivery = d.estimated_delivery_time;
                    this.isSlaBreached = d.is_sla_breached;
                    this.deliveredAt = d.delivered_at;
                    this.cancelledAt = d.cancelled_at;
                    this.updateStatusColors();
                })
                .catch(() => {});
            }, 30000);

            // Poll for new chat messages every 30 seconds
            setInterval(() => {
                if (this.hasDriver) {
                    this.pollChatMessages();
                }
            }, 30000);
        },

        loadChatMessages() {
            const url = this.chatMessagesUrl + '?per_page=100';
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(json => {
                if (!json.success) return;
                this.chatMessages = json.data;
                if (json.data.length > 0) {
                    this.chatLastMessageId = json.data[json.data.length - 1].id;
                }
            })
            .catch(() => {});
        },

        pollChatMessages() {
            let url = this.chatMessagesUrl + '?per_page=50';
            if (this.chatLastMessageId) {
                url += '&after_id=' + this.chatLastMessageId;
            }
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(json => {
                if (!json.success || !json.data.length) return;
                this.chatMessages.push(...json.data);
                this.chatLastMessageId = json.data[json.data.length - 1].id;
                if (!this.chatOpen) {
                    this.chatUnreadCount += json.data.filter(m => m.sender_id !== this.currentUserId).length;
                }
            })
            .catch(() => {});
        },

        sendChatMessage() {
            const body = this.chatMessageBody.trim();
            if (!body || this.chatSending) return;

            this.chatSending = true;
            fetch(this.chatSendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ body })
            })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    this.chatMessages.push(json.data);
                    this.chatLastMessageId = json.data.id;
                    this.chatMessageBody = '';
                    this.chatUnreadCount = 0;
                }
            })
            .catch(() => {})
            .finally(() => { this.chatSending = false; });
        },

        formatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        formatChatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            const now = new Date();
            const isToday = d.toDateString() === now.toDateString();
            if (isToday) {
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
    };
}
</script>
@endsection
