@php
    $productTagsCollection = collect($productTags);
    $isNew = $productTagsCollection->isEmpty();
@endphp

@section('content')
    <form enctype="multipart/form-data" action="{{ route('products.product_tags.store', ['product_id' => $product->id]) }}"
        method="POST">
        @csrf
        @include('components/formBuilder', [
            'type' => 'textarea',
            'name' => 'tag_names',
            'value' => $productTagsCollection->pluck('tag_name')->implode("\n"),
            'label' => '',
            'hint' => __('Separate :names using :characters characters', [
                'names' => __('Tags'),
                'characters' => implode(' ', \App\Support\ArrayHelper::SEPARATOR_KEY_VALUES + ["\t" => 'Tab', "\n" => 'Enter']),
            ]),
        ])
        @include('components/formBuilder', [
            'type' => 'submit',
            'name' => 'submit',
            'label' => $isNew ? __('Create') : __('Edit'),
            'size' => 2,
            'class' => 'btn w-100 ' . ($isNew ? 'btn-success' : 'btn-primary'),
        ])
    </form>
@endsection
