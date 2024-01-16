@extends("emails.template")

@section("content")
    <p>
        Dziękujemy za przedłużenie pakietu.
        <br/>
        <br/>
        Szczegóły zamówienia:
        <ul>
            <li>Pakiet pozostanie ważny do: {{ date("Y-m-d H:i:s", $subscription->end) }}</li>
        </ul>
    </p>
    
    <p>
        Fakturę do zamówienia znajdziesz po zalogowaniu, w zakładzce 'Faktury'.
    </p>
        
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection