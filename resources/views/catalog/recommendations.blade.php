@extends('layouts.app')

@section('title', trans('common.recommendations'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.recommendations') }}</h1>
</div>

<div class="mt-6">
    @if($meals->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($meals as $meal)
                @include('catalog.partials.meal-card', ['meal' => $meal])
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">{{ trans('common.no_products') }}</p>
        </div>
    @endif
</div>
@endsection
