@extends('layouts.app')

@section('title', trans('common.today_deals'))

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ trans('common.today_deals') }}</h1>
</div>

<div class="mt-6">
    @if($meals->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($meals as $meal)
                @include('catalog.partials.meal-card', ['meal' => $meal])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $meals->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">{{ trans('common.no_products') }}</p>
        </div>
    @endif
</div>
@endsection
