@extends('layouts.manager')
@section('title', 'Insurance Tiers - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->routePrefix(); @endphp

<x-page-header title="Insurance Tiers" subtitle="Coverage-based fee schedule">
    <a href="/{{ $prefix }}/settings/insurance-tiers/add" class="btn btn-sm btn-primary"><i data-lucide="plus"></i> Add Tier</a>
</x-page-header>

<x-table-card title="Insurance Tiers ({{ $tiers->count() }} tiers)">
    <table class="table table-modern">
        <thead>
            <tr>
                <th>Coverage From</th>
                <th>Coverage To</th>
                <th class="text-end">Fee</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tiers as $tier)
                <tr>
                    <td>${{ number_format($tier->amount_from, 2) }}</td>
                    <td>${{ number_format($tier->amount_to, 2) }}</td>
                    <td class="text-end">${{ number_format($tier->insurance_fee, 2) }}</td>
                    <td>
                        <a href="/{{ $prefix }}/settings/insurance-tiers/edit/{{ $tier->insurance_id }}">Edit</a> |
                        <a href="/{{ $prefix }}/settings/insurance-tiers/delete/{{ $tier->insurance_id }}" onclick="return confirm('Delete this tier?')">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-table-card>

<div class="mt-3">
    <a href="/{{ $prefix }}/settings" class="btn btn-secondary"><i data-lucide="arrow-left" class="icon--sm"></i> Back to Settings</a>
</div>
@endsection
