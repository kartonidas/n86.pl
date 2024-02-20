<?php

namespace App\Libraries;

class Data
{
    public static function getSystemBillTypes()
    {
        return [
            "rent" => [-1, __("Rent")],
            "deposit" => [-2, __("Deposit")],
        ];
    }
    
    public static function getHistoryFields($object)
    {
        switch($object)
        {
            case \App\Models\Item::class:
                return [
                    "type" => [__("Type")],
                    "rented" => [__("Rented")],
                    "customer_id" => [__("Customer")],
                    "name" => [__("Name")],
                    "street" => [__("Street")],
                    "house_no" => [__("House no")],
                    "apartment_no" => [__("Apartment no")],
                    "city" => [__("City")],
                    "zip" => [__("Zip")],
                    "country" => [__("Country")],
                    "area" => [__("Area")],
                    "ownership_type" => [__("Ownership type")],
                    "num_rooms" => [__("Number of rooms")],
                    "description" => [__("Description")],
                    "default_rent" => [__("Default rent value")],
                    "default_deposit" => [__("Default deposit value")],
                    "comments" => [__("Comment")],
                    "hidden" => [__("Hidden")],
                ];
            break;
        
            case \App\Models\ItemBill::class:
                return [
                    "bill_type_id" => [__("Bill type")],
                    "payment_date" => [__("Payment date")],
                    "paid" => [__("Paid")],
                    "paid_date" => [__("Paid date")],
                    "cost" => [__("Cost")],
                    "recipient_name" => [__("Recipient name")],
                    "recipient_desciption" => [__("Recipient description")],
                    "recipient_bank_account" => [__("Recipient bank account")],
                    "source_document_number" => [__("Recipient document number")],
                    "source_document_date" => [__("Recipient document date")],
                    "comments" => [__("Comment")],
                ];
            break;
        
            case \App\Models\Rental::class:
                return [
                    "document_date" => [__("Document date")],
                    "start" => [__("Start")],
                    "period" => [__("Period")],
                    "months" => [__("Months")],
                    "end" => [__("End")],
                    "termination_period" => [__("Termination period")],
                    "termination_months" => [__("Termination months")],
                    "termination_days" => [__("Termination days")],
                    "deposit" => [__("Deposit")],
                    "rent" => [__("Rent")],
                    "first_month_different_amount" => [__("First month different amount")],
                    "last_month_different_amount" => [__("Last month different amount")],
                    "payment_day" => [__("Payment day")],
                    "first_payment_date" => [__("First payment date")],
                    "number_of_people" => [__("Number of people")],
                    "comments" => [__("Comments")],
                    "termination" => [__("Termination")],
                    "termination_time" => [__("Termination time")],
                    "termination_added" => [__("Termination added")],
                    "termination_reason" => [__("Termination reason")],
                ];
            break;
        }
        return null;
    }
    
    public static function getHelpCategories()
    {
        return [
            "Nieruchomości" => "nieruchomosci",
            "Wynajem" => "wynajem",
            "Rozliczenia" => "rozliczenia",
            "Użytkownicy" => "uzytkownicy",
            "Pakiety" => "pakiety",
        ];
    }
}