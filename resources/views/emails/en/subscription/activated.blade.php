@extends("emails.template")

@section("content")
    <p>
        Thank you for purchasing the additional real estate package.
        <br/>
        <br/>
        Details of the order:
        <ul>
            <li>Number of properties: {{ $order->quantity }}</li>
            <li>The package will remain valid until: {{ date("Y-m-d H:i:s", $subscription->end) }}</li>
        </ul>
    </p>
        
    <p>
        You will find the invoice for your order after logging in, in the 'Invoices' tab.
    </p>
        
    <p>
        Best regards,
        <br/>
        n86.pl team
    </p>
@endsection