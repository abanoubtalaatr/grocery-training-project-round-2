@csrf

<div class="field">
    <label for="name">اسم التصنيف</label>
    <input id="name" name="name" value="{{ old('name', $category->name) }}" required>
    @error('name') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="slug">الرابط المختصر</label>
    <input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="يتم إنشاؤه تلقائيا عند تركه فارغا">
    @error('slug') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="description">الوصف</label>
    <textarea id="description" name="description">{{ old('description', $category->description) }}</textarea>
    @error('description') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="image">الصورة</label>
    <input id="image" name="image" type="file" accept="image/*">
    @error('image') <div class="error">{{ $message }}</div> @enderror
</div>

@if ($category->exists)
    <div class="field">
        <img class="thumb" src="{{ $category->image_url }}" alt="{{ $category->name }}">
    </div>
@endif

<div class="grid">
    <div class="field">
        <label for="sort_order">ترتيب الظهور</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
        @error('sort_order') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="is_active">الحالة</label>
        <select id="is_active" name="is_active">
            <option value="1" @selected(old('is_active', $category->is_active) == 1)>نشط</option>
            <option value="0" @selected(old('is_active', $category->is_active) == 0)>غير نشط</option>
        </select>
        @error('is_active') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="actions">
    <button class="btn primary" type="submit">{{ $submitLabel }}</button>
    <a class="btn" href="{{ route('dashboard.categories.index') }}">رجوع</a>
</div>
