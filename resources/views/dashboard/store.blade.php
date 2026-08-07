@extends('layouts.app')

@section('content')
    <div class="card">
        <h2>Store Dashboard</h2>
        <p class="badge">{{ data_get($payload, 'success') ? 'Success' : 'Error' }}</p>

        @if(!data_get($payload, 'success'))
            <div class="card error">
                <p>{{ data_get($payload, 'message', 'Unable to process store action.') }}</p>
            </div>
        @endif

        <pre>{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
@endsection
