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
        nastąpi rozpoczęcie najmu: 
        <b>
            <a href="{{ env("FRONTEND_URL") }}app/rental/{{ $data["rental"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                {{ $data["rental"]["full_number"] }}
            </a>
        </b>
        z dnia {{ $data["rental"]["document_date"] }}
        (<a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">{{ $data["item"]["name"] }}</a>, <i>{{ Helper::generateAddress((object)$data["item"], ",") }}</i>)
    </div>
        
    @include("emails.pl.user-notification._config_info")
@endsection