@extends("emails.template")

@section("content")
    <p>
        Twój pakiet nieruchomości wygasł.
    </p>
    <p>
        W każdej chwili możesz ponownie wykupić pakiet premium. Aby to zrobić kliknij w poniższy link:
        <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
            <a href="{{ env("FRONTEND_URL") }}app/order" style="display:inline-block; background-color: #506fd9; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                Wykup pakiet premium
            </a>
        </div>
    </p>
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection