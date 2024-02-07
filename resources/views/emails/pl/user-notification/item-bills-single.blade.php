@php
    use App\Libraries\Helper;
@endphp

@extends("emails.template", ["mb" => "10px"])

@section("content")
    <div>
        <b>Dzień dobry,</b>
        <br/>
        <br/>
        za {{ $notification->days }} {{ Helper::plurals($notification->days, "dzień", "dni", "dni") }}
        upłynie termin płatności rachunku:
        <br/>
        {{ $data["bill"]["bill_type"] }}: <b>{{ Helper::amount($data["bill"]["cost"]) }} {{ $data["bill"]["currency"] }}</b>, termin płatności: <b>{{ $data["bill"]["payment_date"] }}</b>, <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}/bill/{{ $data["bill"]["id"] }}" style="color: #3B82F6; text-decoration: none">szczegóły &raquo;</a>
        <br/>
        Przypisany do nieruchomości:
        <b>
            <a href="{{ env("FRONTEND_URL") }}app/item/{{ $data["item"]["id"] }}" style="color: #3B82F6; text-decoration: none">
                {{ $data["item"]["name"] }}
            </a>
        </b> (<i>{{ Helper::generateAddress((object)$data["item"], ",") }}</i>)
    </div>
    @include("emails.pl.user-notification._config_info")
@endsection