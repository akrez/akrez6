@php
    $title = \Arr::get($data, 'name');
    $shortDescription = \Arr::get($data, 'short_description', '');
    $description = \Arr::get($data, 'description', '');
    $titleShortDescription = $title . ($shortDescription ? ' | ' . $shortDescription : '');
    $tags = collect(Arr::get($data, 'products', []))->pluck('product_tags')->flatten()->unique()->toArray();
    $products = collect(Arr::get($data, 'products', []));
    $contacts = collect(Arr::get($data, 'contacts', []));
    $contactSize = $contacts->count() ? max(4, intval(12 / count($contacts))) : 4;
    $logoUrl = \Arr::get($data, 'logo.url', null);
    $heroUrl = url('images/hero.jpg');
@endphp
<!doctype html>
<html class="h-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ $logoUrl }}">

    <title>{{ $titleShortDescription }}</title>
    <meta name="description" content="{{ $description }}">

    <!-- CSS files -->
    <link rel="stylesheet" href="{{ url('libs/bootstrap/dist/css/bootstrap.rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ url('libs/vazirmatn/Vazirmatn-font-face.css') }}" />
    <link rel="stylesheet" href="{{ url('libs/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/blog.css') }}">

    @yield('POS_HEAD')
</head>

<body dir="rtl">
    @yield('POS_BEGIN')
    <div class="container">
        <div class="row align-items-center p-0 pt-4">
            <div class="col-sm-4">
                @if ($logoUrl)
                    <img class="w-100 rounded m-auto" alt="{{ $title }}" src="{{ $logoUrl }}">
                @endif
            </div>
            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-12 d-flex ">
                        <h1 class="me-2">{{ $title }}</h1>
                        <h2 class="h1 text-secondary">{{ $shortDescription }}</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="text-justify">{{ $description }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pb-3">
            <div class="col-12 text-center">
                <button class="btn btn-primary rounded-pill mb-2" data-filter-tag="">
                    {{ 'همه محصولات ' . $title }}
                </button>
            </div>
            <div class="col-12 text-center">
                @foreach ($tags as $tagKey => $tag)
                    <button class="btn btn-info rounded-pill mb-1" data-filter-tag="{{ md5($tag) }}">
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 mb-4">
                <div class="container-fluid">
                    <div class="row">
                        @foreach ($products as $productKey => $product)
                            <div class="thumbnail border pt-3 pb-3 col-sm-4 col-md-3 col-lg-2"
                                data-filter-tags="{{ json_encode(array_map('md5', $product['product_tags'])) }}">
                                @if (count($product['images']) == 1)
                                    <img class="w-100 pb-3 rounded" src="{{ $product['images'][0]['url'] }}"
                                        alt="{{ $product['name'] }}">
                                @elseif (count($product['images']) > 1)
                                    <div id="product-carousel-{{ $productKey }}"
                                        class="carousel pb-3 carousel-dark slide">
                                        <div class="carousel-inner">
                                            @foreach ($product['images'] as $productImage)
                                                <div
                                                    class="carousel-item @if ($loop->first) active @endif">
                                                    <img class="w-100 pb-2 rounded" src="{{ $productImage['url'] }}"
                                                        alt="{{ $product['name'] }}">
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="carousel-control-prev" type="button"
                                            data-bs-target="#product-carousel-{{ $productKey }}"
                                            data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                            data-bs-target="#product-carousel-{{ $productKey }}"
                                            data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h5 class="card-title font-weight-bold pb-2">{{ $product['name'] }}</h5>
                                    <p class="card-text">
                                        @foreach ($product['product_properties'] as $property)
                                            <strong>{{ $property['property_key'] }}:</strong>
                                            {{ implode(', ', $property['property_values']) }}<br>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($contacts->count())
        <footer class="footer mt-auto py-3 bg-light">
            <div class="container">
                <div class="row">
                    @foreach ($contacts as $contact)
                        @php
                            if ('address' == $contact['contact_type']) {
                                $icon = 'bi bi-geo-alt';
                            } elseif ('telegram' == $contact['contact_type']) {
                                $icon = 'bi bi-telegram';
                            } elseif ('whatsapp' == $contact['contact_type']) {
                                $icon = 'bi bi-whatsapp';
                            } elseif ('email' == $contact['contact_type']) {
                                $icon = 'bi bi-envelope';
                            } elseif ('instagram' == $contact['contact_type']) {
                                $icon = 'bi bi-instagram';
                            } else {
                                $icon = 'bi bi-telephone';
                            }
                        @endphp
                        <div class="col-lg-{{ $contactSize }} py-3">
                            <div class="info-item text-center">
                                <div class="contact d-inline-block text-center">
                                    <div class="d-flex justify-content-center">
                                        <i class="{{ $icon }} fs-3em"></i>
                                    </div>
                                    <h3>{{ $contact['contact_key'] }}</h3>
                                    @if ($contact['contact_link'])
                                        <a class="h4 text-primary text-decoration-none"
                                            href="{{ $contact['contact_link'] }}"
                                            dir="ltr">{{ $contact['contact_value'] }}</a>
                                    @else
                                        <div class="h4 text-secondary text-decoration-none" dir="ltr">
                                            {{ $contact['contact_value'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </footer>
    @endif
    <script src="{{ asset('libs/bootstrap/dist/js/bootstrap.bundle.js') }}"></script>
    <script>
        document.querySelectorAll("[data-filter-tag]").forEach(function(radioFilterElement) {
            radioFilterElement.onclick = function() {
                tag = this.getAttribute('data-filter-tag');
                document.querySelectorAll("[data-filter-tags]").forEach(productElement => {
                    const hasTag = JSON.parse((productElement.getAttribute('data-filter-tags')))
                        .includes(tag);
                    productElement.style.display = (tag && !hasTag ? 'none' : 'block');
                });
            }
        });
    </script>
    @yield('POS_END')
</body>

</html>
