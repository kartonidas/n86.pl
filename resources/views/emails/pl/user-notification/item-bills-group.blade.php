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
        upłynie termin płatności poniższych rachunków:
    </div>
    
    <div style="margin-top: 10px">
        @foreach($data as $i => $row)
            <div style="margin-left: 25px; margin-bottom: 10px;">
                <b>
                    <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                        {{ $row["item"]["name"] }}
                    </a>
                </b>
                (<i>{{ Helper::generateAddress((object)$row["item"], ",") }}</i>):
                <ul style="list-style-type: none; padding: 0; margin-top: 0; margin-bottom: 10px">
                    @foreach($row["bills"] as $bill)
                        <li style="margin-bttom: 5px;">
                            - {{ $bill["bill_type"] }}: <b>{{ Helper::amount($bill["cost"]) }} {{ $bill["currency"] }}</b>, termin płatności: <b>{{ $paymentDate->format("Y-m-d") }}</b>, <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}/bill/{{ $bill["id"] }}" style="color: #3B82F6; text-decoration: none">szczegóły &raquo;</a>
                        </li>
                    @endforeach
                </ul>
            </div>
                
            @if(array_key_last($data) != $i)
                <div style="border-bottom: 1px solid #e2e2e2; margin-bottom: 10px;"></div>
            @endif
        @endforeach
    </div>
        
    @include("emails.pl.user-notification._config_info")
@endsection