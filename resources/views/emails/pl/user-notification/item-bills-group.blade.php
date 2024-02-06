@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        poniżej znajduje się lista nieopłaconych rachunków dla których termin płatności upłynie w ciągu 
        @if($notification->days > 1)
            najbliższych {{ $notification->days }} dni.
        @else
            najbliższego dnia.
        @endif
    </div>
    
    <div style="margin-top: 10px">
        @foreach($data as $paymentDate => $rows)
            <div style="padding: 3px;font-size: 16px;">
                Termin płatności <b>{{ $paymentDate }}</b>:
            </div>
                
            <div style="margin-left: 25px; margin-bottom: 10px;">
                @foreach($rows as $row)
                    <b>
                        <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                            {{ $row["item"]["name"] }}
                        </a>
                    </b>
                    (<i>{{ Helper::generateAddress((object)$row["item"], ",") }}</i>):
                    <ul style="list-style-type: none; padding: 0; margin-top: 0; margin-bottom: 10px">
                        @foreach($row["bills"] as $bill)
                            <li style="margin-bttom: 5px;">
                                - {{ $bill["bill_type"] }}: <b>{{ Helper::amount($bill["cost"]) }} {{ $bill["currency"] }}</b> <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}/bill/{{ $bill["id"] }}" style="color: #3B82F6; text-decoration: none">szczegóły &raquo;</a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
                
            @if(array_key_last($data) != $paymentDate)
                <div style="border-bottom: 1px solid #e2e2e2; margin-bottom: 10px;"></div>
            @endif
        @endforeach
    </div>
        
    @include("emails.pl.user-notification._config_info")
@endsection