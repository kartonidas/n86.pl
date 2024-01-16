@extends("emails.template")

@section("title")
    {{ $title }}
@endsection

@section("content")
    <div margin-bottom: 10px;>
        <p>
            To set a new password, click the link below:
            <div style="text-align: left; margin-top: 10px; margin-bottom: 10px">
                <a href="{{ $url }}" style="display:inline-block; background-color: #3B82F6; color: white; padding: 10px; text-decoration: none; border-radius: 5px;">
                    Set a new password
                </a>
            </div>
        </p>
    </div>
    
    <p style="font-size: 14px">
        If you did not request a password change or have any concerns, please contact us immediately.
    </p>
        
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection

