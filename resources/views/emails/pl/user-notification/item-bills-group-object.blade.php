@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        za {{ $notification->days }} {{ Helper::plurals($notification->days, "dzień", "dni", "dni") }} (w dniu: {{ $paymentDate->format("Y-m-d") }})
        upłynie termin płatności rachunków przypisanych do nieruchomości:
        <b>
            <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                {{ $data["item"]["name"] }}
            </a>
        </b> (<i>{{ Helper::generateAddress((object)$data["item"], ",") }}</i>). Lista rachunków:
    </div>
    
    <ul style="list-style-type: none; padding: 0; margin-top: 10px">
        @foreach($data["bills"] as $bill)
            <li style="margin-bttom: 5px;">
                - {{ $bill["bill_type"] }}: <b>{{ Helper::amount($bill["cost"]) }} {{ $bill["currency"] }}</b>, termin płatności: <b>{{ $paymentDate->format("Y-m-d") }}</b>, <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}/bill/{{ $bill["id"] }}" style="color: #3B82F6; text-decoration: none">szczegóły &raquo;</a>
            </li>
        @endforeach
    </ul>
        
    @include("emails.pl.user-notification._config_info")
@endsection