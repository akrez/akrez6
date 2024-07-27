<strong>{{ $responseBuilder->getData()->product->code }}</strong>
<br>
{{ $responseBuilder->getMessage() }}
@if ($responseBuilder->getErrors())
    <br>
    <ul class="m-0">
        @foreach ($responseBuilder->getErrors()->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
