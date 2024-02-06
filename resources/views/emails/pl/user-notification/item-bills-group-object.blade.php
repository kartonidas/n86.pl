@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        poniżej znajduje się lista nieopłaconych rachunków przypisanych do nieruchomości:
        <b>
            <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                {{ $data["item"]["name"] }}
            </a>
        </b> (<i>{{ Helper::generateAddress((object)$data["item"], ",") }}</i>)
        dla których termin płatności upłynie w ciągu 
        @if($notification->days > 1)
            najbliższych {{ $notification->days }} dni.
        @else
            najbliższego dnia.
        @endif
    </div>
    
    <ul style="list-style-type: none; padding: 0; margin-top: 10px">
        @foreach($data["bills"] as $date => $billDate)
            @foreach($billDate as $bill)
                <li style="margin-bttom: 5px;">
                    - {{ $bill["bill_type"] }}: <b>{{ Helper::amount($bill["cost"]) }} {{ $bill["currency"] }}</b>, termin płatności: <b>{{ $date }}</b>, <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}/bill/{{ $bill["id"] }}" style="color: #3B82F6; text-decoration: none">szczegóły &raquo;</a>
                </li>
            @endforeach
        @endforeach
    </ul>
        
    @include("emails.pl.user-notification._config_info")
@endsection