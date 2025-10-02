@extends('admin.layouts.admin')

@section('title', 'Edit Attribute')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header card-header-bg text-white">
            <h6>{{ __('cms.attributes.title_edit') }}</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div id="errorBar" class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Attribute Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('cms.attributes.attribute_name') }}</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $attribute->name) }}" 
                           class="form-control @error('name') is-invalid @enderror" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Attribute Values -->
                <div class="mb-3">
                    <label class="form-label">{{ __('cms.attributes.attribute_values') }}</label>
                    <div id="attribute-values-container">
                        @foreach ($attribute->values as $value)
                            <div class="input-group mb-2">
                                <input type="text" 
                                       name="values[]" 
                                       value="{{ old('values.' . $loop->index, $value->value) }}" 
                                       class="form-control">
                                <button type="button" class="btn btn-danger remove-value">{{ __('cms.attributes.remove_value') }}</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-value" class="btn btn-primary mt-2">{{ __('cms.attributes.add_value') }}</button>
                </div>

                <!-- Translations -->
                <div class="mb-3">
                    <label class="form-label">{{ __('cms.attributes.translations') }}</label>
                    <ul class="nav nav-tabs" id="languageTabs" role="tablist">
                        @foreach ($languages as $language)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                        id="{{ $language->code }}-tab"
                                        data-bs-toggle="tab" 
                                        data-bs-target="#{{ $language->code }}" 
                                        type="button">
                                    {{ ucwords($language->name) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="languageTabContent">
                        @foreach ($languages as $language)
                            <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="{{ $language->code }}">
                                <div id="translation-container-{{ $language->code }}">
                                    @foreach ($attribute->values as $valueIndex => $value)
                                        @php
                                            $translation = $value->translations
                                                ->where('language_code', $language->code)
                                                ->first();
                                        @endphp
                                        <div class="input-group mb-2 translation-group">
                                            <input type="text" 
                                                   name="translations[{{ $language->code }}][]" 
                                                   value="{{ old('translations.' . $language->code . '.' . $valueIndex, optional($translation)->translated_value) }}" 
                                                   class="form-control" 
                                                   placeholder="Enter {{ $language->name }} value">
                                            <button type="button" class="btn btn-danger remove-translation">{{ __('cms.attributes.remove_value') }}</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary add-translation mt-2"
                                        data-lang="{{ $language->code }}">
                                    {{ __('cms.attributes.add_value_translation') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-success mt-3">{{ __('cms.attributes.update_attribute') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Add new value
    document.getElementById("add-value").addEventListener("click", function () {
        let container = document.getElementById("attribute-values-container");
        let newInputGroup = document.createElement("div");
        newInputGroup.classList.add("input-group", "mb-2");

        let input = document.createElement("input");
        input.type = "text";
        input.name = "values[]";
        input.classList.add("form-control");
        input.placeholder = "Enter value";

        let removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.classList.add("btn", "btn-danger", "remove-value");
        removeBtn.textContent = "Remove";
        removeBtn.addEventListener("click", function () {
            newInputGroup.remove();
        });

        newInputGroup.appendChild(input);
        newInputGroup.appendChild(removeBtn);
        container.appendChild(newInputGroup);
    });

    // Remove value
    document.querySelectorAll(".remove-value").forEach(button => {
        button.addEventListener("click", function () {
            this.parentElement.remove();
        });
    });

    // Add translation
    document.querySelectorAll(".add-translation").forEach(button => {
        button.addEventListener("click", function () {
            let lang = this.dataset.lang;
            let container = document.getElementById("translation-container-" + lang);

            let newGroup = document.createElement("div");
            newGroup.classList.add("input-group", "mb-2", "translation-group");

            let input = document.createElement("input");
            input.type = "text";
            input.name = "translations[" + lang + "][]";
            input.classList.add("form-control");
            input.placeholder = "Enter " + lang + " value";

            let removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.classList.add("btn", "btn-danger", "remove-translation");
            removeBtn.textContent = "Remove";
            removeBtn.addEventListener("click", function () {
                newGroup.remove();
            });

            newGroup.appendChild(input);
            newGroup.appendChild(removeBtn);
            container.appendChild(newGroup);
        });
    });

    // Remove translation
    document.querySelectorAll(".remove-translation").forEach(button => {
        button.addEventListener("click", function () {
            this.parentElement.remove();
        });
    });
});
</script>
@endsection
