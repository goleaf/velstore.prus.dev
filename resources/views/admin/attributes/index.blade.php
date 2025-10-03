@extends('admin.layouts.admin')

@section('title', __('cms.attributes.title_manage'))

@section('content')
<x-admin.page-header :title="__('cms.attributes.title_manage')" :description="__('cms.attributes.index_description')">
    <x-admin.button-link href="{{ route('admin.attributes.create') }}" class="btn-primary">
        {{ __('cms.attributes.title_create') }}
    </x-admin.button-link>
</x-admin.page-header>

<x-admin.card class="mt-6">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th scope="col" class="w-16">{{ __('cms.attributes.id') }}</th>
                    <th scope="col">{{ __('cms.attributes.name') }}</th>
                    <th scope="col" class="w-1/2">{{ __('cms.attributes.values') }}</th>
                    <th scope="col" class="text-end">{{ __('cms.attributes.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attributes as $attribute)
                    <tr>
                        <td>{{ $attribute->id }}</td>
                        <td class="fw-semibold">{{ $attribute->name }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($attribute->values as $value)
                                    <span class="badge bg-primary">{{ $value->value }}</span>
                                @empty
                                    <span class="text-muted">{{ __('cms.attributes.no_values') }}</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-outline-primary btn-sm">
                                    {{ __('cms.attributes.title_edit') }}
                                </a>
                                <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('{{ __('cms.attributes.confirm_delete') }}');">
                                        {{ __('cms.attributes.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            {{ __('cms.attributes.empty_state') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $attributes->links() }}
    </div>
</x-admin.card>
@endsection
