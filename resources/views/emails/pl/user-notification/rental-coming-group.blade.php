@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        za {{ $notification->days }} {{ Helper::plurals($notification->days, "dzień", "dni", "dni") }} (w dniu: {{ $comingDate->format("Y-m-d") }})
        rozpoczną się poniższe wynajmy:
    </div>
    
    <div style="margin-top: 10px">
        <div style="margin-left: 25px; margin-bottom: 10px;">
            <ul style="list-style-type: none; padding: 0; margin-top: 0; margin-bottom: 10px">
                @foreach($data as $i => $row)
                    <li style="margin-bttom: 10px; margin-left: 0">
                        <b>
                            <a href="{{ env("FRONTEND_URL") }}app/rental/{{ $row["rental"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                                {{ $row["rental"]["full_number"] }}
                            </a>
                        </b>
                        z dnia {{ $row["rental"]["document_date"] }},
                        <a href="{{ env("FRONTEND_URL") }}app/item/{{ $row["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                            {{ $row["item"]["name"] }}
                        </a>
                        (<i>{{ Helper::generateAddress((object)$row["item"], ",") }}</i>)
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
        
    @include("emails.pl.user-notification._config_info")
@endsection