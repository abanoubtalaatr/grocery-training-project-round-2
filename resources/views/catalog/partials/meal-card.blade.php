@props(['meal', 'isFavorited' => false])

<a href="{{ route('catalog.meal', $meal->slug ?? $meal->id) }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
    <div class="relative aspect-square overflow-hidden bg-gray-100">
        @if($meal->image_url)
            <img src="{{ $meal->image_url }}" alt="{{ $meal->localized_title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif

        @if($meal->hasOffer())
            <span class="absolute top-2 {{ app()->getLocale() === 'ar' ? 'right-2' : 'left-2' }} bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                {{ $meal->offer_title ?? trans('common.special_offer') }}
            </span>
        @endif

        @if($meal->is_featured)
            <span class="absolute top-2 {{ app()->getLocale() === 'ar' ? 'left-2' : 'right-2' }} bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded">
                {{ trans('common.featured') }}
            </span>
        @endif

        @auth
            <form method="POST" action="{{ route('favorites.toggle', $meal->id) }}" class="absolute bottom-2 {{ app()->getLocale() === 'ar' ? 'left-2' : 'right-2' }}" onclick="event.stopPropagation(); event.preventDefault(); this.submit();">
                @csrf
                <button type="submit" class="w-8 h-8 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-sm hover:bg-white transition {{ $isFavorited ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                    @if($isFavorited)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    @endif
                </button>
            </form>
        @endauth
    </div>

    <div class="p-3">
        <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-brand-600 transition">
            {{ $meal->localized_title }}
        </h3>

        @if($meal->category)
            <p class="mt-1 text-xs text-gray-500">{{ $meal->category->localized_name }}</p>
        @endif

        @if($meal->rating > 0)
            <div class="flex items-center mt-1 gap-1">
                <div class="flex text-amber-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($meal->rating))
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @else
                            <svg class="w-3 h-3 fill-current text-gray-300" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endif
                    @endfor
                </div>
                <span class="text-xs text-gray-500">({{ $meal->rating_count }})</span>
            </div>
        @endif

        <div class="mt-2 flex items-baseline gap-2">
            @if($meal->discount_price !== null)
                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->discount_price, 2) }}</span>
                <span class="text-gray-400 line-through text-xs">${{ number_format((float) $meal->price, 2) }}</span>
            @else
                <span class="text-brand-600 font-bold text-sm">${{ number_format((float) $meal->price, 2) }}</span>
            @endif
        </div>
    </div>
</a>
