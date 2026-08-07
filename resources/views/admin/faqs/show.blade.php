@extends('layouts.admin')

@section('title', 'FAQ Details')
@section('breadcrumb', 'FAQs / View')

@section('content')
<div class="page-title-box">
    <h1 class="page-title">FAQ Details</h1>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="table-container" style="padding: 25px;">
    <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
        <span class="badge badge-secondary" style="margin-bottom: 10px; inline-block;">{{ $faq->category ?? 'General' }}</span>
        <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary);">{{ $faq->question }}</h2>
    </div>
    <div style="line-height: 1.8; color: var(--text-secondary); white-space: pre-wrap;">{{ $faq->answer }}</div>
</div>
@endsection
