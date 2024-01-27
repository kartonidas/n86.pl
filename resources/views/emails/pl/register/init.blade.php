@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            Dziękujemy za zainteresowanie i rejestrację w naszym serwisie.
            <br/>
            Aby dokończyć proces rejestracji i móc koszystać z serwisu kliknij w poniższy link.
            Po kliknięciu zostaniesz przeniesiony na stronę na której uzupełnisz wszystkie niezbędne dane i utworzysz swoje konto.
        </p>
    </div>
        
    <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
        <a href="{{ env("FRONTEND_URL") }}sign-up/confirm/{{ $token->token }}" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
            Potwierdź rejestrację
        </a>
    </div>
    
    <p>
        Z wyrazami szacunku,
        <br/>
        Zespół n86.pl
    </p>
@endsection