@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        poniżej znajduje się lista wynajmów kończących się w ciągu 
        @if($notification->days > 1)
            najbliższych {{ $notification->days }} dni.
        @else
            najbliższego dnia.
        @endif
    </div>
    
    <div style="margin-top: 10px">
        @foreach($data as $endDate => $rows)
            <div style="padding: 3px;font-size: 16px;">
                Koniec wynajmu <b>{{ $endDate }}</b>:
            </div>
                
            <div style="margin-left: 25px; margin-bottom: 10px;">
                @foreach($rows as $row)
                    <ul style="list-style-type: none; padding: 0; margin-top: 0; margin-bottom: 10px">
                        @foreach($row["rentals"] as $rental)
                            <li style="margin-bttom: 5px; margin-left: 0">
                                <b>
                                    <a href="{{ env("FRONTEND_URL") }}app/rental/{{ $rental["id"] }}" style="color: #3B82F6; text-decoration: none">
                                        {{ $rental["full_number"] }}
                                    </a>
                                </b>
                                z dnia {{ $rental["document_date"] }},
                                <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                                    {{ $row["item"]["name"] }}
                                </a>
                                (<i>{{ Helper::generateAddress((object)$row["item"], ",") }}</i>):
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
                
            @if(array_key_last($data) != $endDate)
                <div style="border-bottom: 1px solid #e2e2e2; margin-bottom: 10px;"></div>
            @endif
        @endforeach
    </div>
        
    @include("emails.pl.user-notification._config_info")
@endsection