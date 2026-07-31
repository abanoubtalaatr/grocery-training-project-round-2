<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Authorize Application</h1>
        <p class="mt-2 text-sm text-gray-600">
            <strong>{{ $client->name }}</strong> requests permission to access your SmartLearn account.
        </p>
    </div>

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <p class="font-semibold mb-2">Requested permissions:</p>
        <ul class="list-disc ms-5 space-y-1">
            @forelse($scopes as $scope)
                <li>
                    <strong>{{ $scope->id }}</strong>
                    @if($scope->description)
                        — {{ $scope->description }}
                    @endif
                </li>
            @empty
                <li>No special scopes requested.</li>
            @endforelse
        </ul>
    </div>

    <p class="mb-4 text-sm text-gray-600">
        Signed in as <strong>{{ $user->email }}</strong> ({{ $user->role?->value }})
    </p>

    <div class="flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <x-secondary-button type="submit">Cancel</x-secondary-button>
        </form>

        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <x-primary-button type="submit">Allow</x-primary-button>
        </form>
    </div>
</x-guest-layout>
