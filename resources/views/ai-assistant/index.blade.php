@extends('layouts.app')

@section('title', trans('ai_assistant.title'))

@section('content')
<div class="max-w-3xl mx-auto h-[calc(100vh-8rem)] flex flex-col"
     x-data="chatAssistant()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ trans('ai_assistant.heading') }}</h1>
            <p class="text-xs text-gray-500">{{ trans('ai_assistant.powered_by') }} — Gemini</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showHandoffModal = true" class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                {{ trans('ai_assistant.human_support') }}
            </button>
            <button @click="loadHistory()" class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                {{ trans('ai_assistant.history_title') }}
            </button>
        </div>
    </div>

    {{-- Chat Container --}}
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col overflow-hidden">
        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-4" x-ref="messagesContainer" @scroll="handleScroll($event)">
            {{-- Welcome Message --}}
            <template x-if="messages.length === 0 && !loading">
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto rounded-full bg-brand-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <p class="text-sm text-gray-600 mb-6">{{ trans('ai_assistant.welcome') }}</p>

                    {{-- Suggestions --}}
                    <div class="max-w-md mx-auto">
                        <p class="text-xs text-gray-400 mb-3">{{ trans('ai_assistant.suggestions_title') }}</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <template x-for="s in suggestions" :key="s.id">
                                <button @click="sendMessage(s.question)" class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-brand-50 hover:text-brand-700 transition border border-gray-200" x-text="s.label"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Message Bubbles --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    {{-- User Message --}}
                    <div x-show="msg.role === 'user'" class="flex justify-end mb-3">
                        <div class="max-w-[80%] bg-brand-500 text-white rounded-2xl rounded-br-md px-4 py-2.5">
                            <p class="text-sm whitespace-pre-line" x-text="msg.content"></p>
                            <p class="text-[10px] mt-1 opacity-70" x-text="formatTime(msg.timestamp)"></p>
                        </div>
                    </div>

                    {{-- AI Message --}}
                    <div x-show="msg.role === 'assistant'" class="flex justify-start mb-3">
                        <div class="max-w-[80%] bg-gray-100 text-gray-900 rounded-2xl rounded-bl-md px-4 py-2.5">
                            <div class="text-sm whitespace-pre-line" x-html="formatResponse(msg.content)"></div>
                            <p class="text-[10px] mt-1 text-gray-400" x-text="formatTime(msg.timestamp)"></p>
                            {{-- Rate buttons --}}
                            <div x-show="!msg.rated" class="mt-2 flex items-center gap-2">
                                <span class="text-[10px] text-gray-400">{{ trans('ai_assistant.rate_response') }}</span>
                                <button @click="rateMessage(msg, 5)" class="text-[10px] text-green-600 hover:text-green-700">{{ trans('ai_assistant.rate_yes') }}</button>
                                <button @click="rateMessage(msg, 1)" class="text-[10px] text-red-600 hover:text-red-700">{{ trans('ai_assistant.rate_no') }}</button>
                            </div>
                            <div x-show="msg.rated" class="mt-1">
                                <span class="text-[10px] text-gray-400">Thanks!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Loading Indicator --}}
            <div x-show="loading" class="flex justify-start mb-3">
                <div class="bg-gray-100 rounded-2xl rounded-bl-md px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error Banner --}}
        <div x-show="error" x-transition class="px-4 py-2 bg-red-50 border-t border-red-200">
            <p class="text-xs text-red-600" x-text="error"></p>
        </div>

        {{-- Input Area --}}
        <div class="p-4 border-t border-gray-200">
            <form @submit.prevent="sendMessage(inputText)" class="flex items-center gap-2">
                <input type="text" x-model="inputText" placeholder="{{ trans('ai_assistant.input_placeholder') }}"
                    :disabled="loading"
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <button type="submit" :disabled="loading || !inputText.trim()"
                    class="px-4 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- History Panel --}}
    <div x-show="showHistory" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col" @click.away="showHistory = false">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">{{ trans('ai_assistant.history_title') }}</h2>
                <button @click="showHistory = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-if="history.length === 0">
                    <p class="text-sm text-gray-500 text-center py-4">{{ trans('ai_assistant.no_history') }}</p>
                </template>
                <template x-for="(h, i) in history" :key="i">
                    <div class="bg-gray-50 rounded-lg p-3 cursor-pointer hover:bg-gray-100 transition" @click="reuseHistory(h)">
                        <p class="text-xs text-gray-500" x-text="formatTime(h.created_at)"></p>
                        <p class="text-sm font-medium text-gray-900 mt-1" x-text="h.question"></p>
                        <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="h.answer"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Handoff Modal --}}
    <div x-show="showHandoffModal" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4" @click.away="showHandoffModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ trans('ai_assistant.human_support_title') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ trans('ai_assistant.human_support_desc') }}</p>

                <div x-show="!handoffSuccess">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ trans('ai_assistant.human_support_question') }}</label>
                            <textarea x-model="handoffQuestion" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" :placeholder="trans('ai_assistant.human_support_notes_placeholder')"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ trans('ai_assistant.human_support_order_id') }}</label>
                            <input type="text" x-model="handoffOrderId" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" :placeholder="trans('ai_assistant.human_support_order_id_placeholder')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ trans('ai_assistant.human_support_notes') }}</label>
                            <textarea x-model="handoffNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" :placeholder="trans('ai_assistant.human_support_notes_placeholder')"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center gap-3">
                        <button @click="submitHandoff()" :disabled="handoffLoading" class="px-4 py-2 bg-brand-500 text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition disabled:opacity-50">
                            <span x-show="!handoffLoading">{{ trans('ai_assistant.human_support_submit') }}</span>
                            <span x-show="handoffLoading">{{ trans('ai_assistant.loading') }}</span>
                        </button>
                        <button @click="showHandoffModal = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                            {{ trans('ai_assistant.human_support_cancel') }}
                        </button>
                    </div>
                </div>

                <div x-show="handoffSuccess" class="text-center py-4">
                    <div class="w-12 h-12 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm text-green-700">{{ trans('ai_assistant.human_support_success') }}</p>
                    <button @click="showHandoffModal = false; handoffSuccess = false;" class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        {{ trans('common.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chatAssistant() {
    return {
        messages: [],
        inputText: '',
        loading: false,
        error: null,
        suggestions: [],
        history: [],
        showHistory: false,
        showHandoffModal: false,
        handoffQuestion: '',
        handoffOrderId: '',
        handoffNotes: '',
        handoffLoading: false,
        handoffSuccess: false,
        csrfToken: '{{ csrf_token() }}',
        locale: '{{ app()->getLocale() }}',

        async init() {
            await this.loadSuggestions();
        },

        async loadSuggestions() {
            try {
                const res = await fetch('{{ route("chatbot.suggestions") }}?locale=' + this.locale, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const json = await res.json();
                    this.suggestions = json.data?.suggestions || [];
                }
            } catch (e) {}
        },

        async loadHistory() {
            this.showHistory = true;
            try {
                const res = await fetch('{{ route("chatbot.history") }}?per_page=20', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const json = await res.json();
                    const items = json.data?.items || [];
                    this.history = items.reverse();
                }
            } catch (e) {}
        },

        reuseHistory(h) {
            this.messages = [];
            this.messages.push({ role: 'user', content: h.question, timestamp: h.created_at });
            this.messages.push({ role: 'assistant', content: h.answer, timestamp: h.created_at, rated: true });
            this.showHistory = false;
            this.scrollToBottom();
        },

        async sendMessage(text) {
            if (!text || !text.trim() || this.loading) return;

            const question = text.trim();
            this.inputText = '';
            this.error = null;

            this.messages.push({ role: 'user', content: question, timestamp: new Date().toISOString() });
            this.scrollToBottom();

            this.loading = true;

            try {
                const res = await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ question: question, locale: this.locale })
                });

                const json = await res.json();

                if (res.ok && json.success) {
                    this.messages.push({
                        role: 'assistant',
                        content: json.data.answer,
                        timestamp: new Date().toISOString(),
                        id: json.data.id,
                        rated: false
                    });
                } else {
                    this.error = json.message || json.error || '{{ trans("ai_assistant.error") }}';
                }
            } catch (e) {
                this.error = '{{ trans("ai_assistant.error") }}';
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },

        async rateMessage(msg, rating) {
            msg.rated = true;
            try {
                await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ question: msg.content, rating: rating, locale: this.locale })
                });
            } catch (e) {}
        },

        async submitHandoff() {
            this.handoffLoading = true;
            try {
                const body = {};
                if (this.handoffQuestion.trim()) body.question = this.handoffQuestion.trim();
                if (this.handoffOrderId.trim()) body.order_id = this.handoffOrderId.trim();
                if (this.handoffNotes.trim()) body.notes = this.handoffNotes.trim();

                const res = await fetch('{{ route("chatbot.handoff") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(body)
                });

                if (res.ok || res.status === 201) {
                    this.handoffSuccess = true;
                } else {
                    this.error = 'Failed to submit support request.';
                }
            } catch (e) {
                this.error = 'Failed to submit support request.';
            } finally {
                this.handoffLoading = false;
            }
        },

        formatTime(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        formatResponse(text) {
            if (!text) return '';
            return text
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        handleScroll(e) {},

        getSanctumToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        },

        trans(key) {
            return key;
        }
    };
}
</script>
@endsection
