@extends("emails.template")

@section("content")
    <p>
        Dziękujemy za rozszerzenie pakietu.
        <br/>
        <br/>
        Szczegóły zamówienia:
        <ul>
            <li>Zakupiona ilość pakietów: {{ $order->quantity }}</li>
            <li>Łączna ilość pakietów: {{ $subscription->items }}</li>
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