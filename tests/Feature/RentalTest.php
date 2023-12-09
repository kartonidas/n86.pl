<?php

namespace Tests\Feature;

use DateTime;
use DateInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Firm;
use App\Models\Item;
use App\Models\User;
use App\Models\Rental;

class RentalTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_date_single_non_indeterminate_validation_ok(): void
    {
        $validDates = [
            [
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P11D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P12D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P15D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P20D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P21D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P30D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P1D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P4D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P13D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P13D"))->format("Y-m-d")
            ]
        ];
        
        $token = $this->getOwnerLoginToken();
        $item = $this->createItem($this->getAccountUuui($token));
        $tenant = $this->createTenant($this->getAccountUuui($token));
        
        $data = [
            "item" => $item->toArray(),
            "tenant" => $tenant->toArray(),
        ];
        
        foreach($validDates as $validDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $validDate[0], Rental::PERIOD_DATE, null, $validDate[1]);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(200);
        }
    }
    
    public function test_date_single_non_indeterminate_validation_faild(): void
    {
        $validDates = [
            [
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P6D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P8D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d")
            ]
        ];
        
        $invalidDates = [
            [
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P12D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P12D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P6D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P9D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P12D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P12D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P7D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P9D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d")
            ]
        ];
        
        $invalidDatesIndeterminate = [
            (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
            (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
            (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d"),
        ];
        
        $token = $this->getOwnerLoginToken();
        $item = $this->createItem($this->getAccountUuui($token));
        $tenant = $this->createTenant($this->getAccountUuui($token));
        
        $data = [
            "item" => $item->toArray(),
            "tenant" => $tenant->toArray(),
        ];
        foreach($validDates as $validDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $validDate[0], Rental::PERIOD_DATE, null, $validDate[1]);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(200);
        }
        
        foreach($invalidDates as $invalidDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $invalidDate[0], Rental::PERIOD_DATE, null, $invalidDate[1]);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(409);
        }
        
        foreach($invalidDatesIndeterminate as $invalidDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $invalidDate, Rental::PERIOD_INDETERMINATE, null, null);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(409);
        }
    }
    
    public function test_date_single_indeterminate_validation_ok(): void
    {
        $validDate = [
            (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
        ];
        
        $token = $this->getOwnerLoginToken();
        $item = $this->createItem($this->getAccountUuui($token));
        $tenant = $this->createTenant($this->getAccountUuui($token));
        
        $data = [
            "item" => $item->toArray(),
            "tenant" => $tenant->toArray(),
        ];
        
        $data["rent"] = $this->prepareRentedData($item->id, $validDate[0], Rental::PERIOD_INDETERMINATE, null, null);
        $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
        $response->assertStatus(200);
        
        $validDates = [
            [
                (new DateTime())->add(new DateInterval("P1D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P2D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P4D"))->format("Y-m-d")
            ],
        ];
        
        foreach($validDates as $invalidDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $invalidDate[0], Rental::PERIOD_DATE, null, $invalidDate[1]);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(200);
        }
    }
    
    public function test_date_single_indeterminate_validation_faild(): void
    {
        $validDate = [
            (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
        ];
        
        $token = $this->getOwnerLoginToken();
        $item = $this->createItem($this->getAccountUuui($token));
        $tenant = $this->createTenant($this->getAccountUuui($token));
        
        $data = [
            "item" => $item->toArray(),
            "tenant" => $tenant->toArray(),
            "rent" => $this->prepareRentedData($item->id, $validDate[0], Rental::PERIOD_INDETERMINATE, null, null),
        ];
        $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
        $response->assertStatus(200);
        
        
        $invalidDates = [
            [
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P8D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P6D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P8D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P8D"))->format("Y-m-d")
            ],
            [
                (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
                (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d")
            ],
        ];
        
        $invalidDatesIndeterminate = [
            (new DateTime())->add(new DateInterval("P3D"))->format("Y-m-d"),
            (new DateTime())->add(new DateInterval("P5D"))->format("Y-m-d"),
            (new DateTime())->add(new DateInterval("P10D"))->format("Y-m-d"),
        ];
        
        foreach($invalidDates as $invalidDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $invalidDate[0], Rental::PERIOD_DATE, null, $invalidDate[1]);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(409);
        }
        
        foreach($invalidDatesIndeterminate as $invalidDate)
        {
            $data["rent"] = $this->prepareRentedData($item->id, $invalidDate, Rental::PERIOD_INDETERMINATE, null, null);
            $response = $this->withToken($token)->putJson('/api/v1/rental/rent', $data);
            $response->assertStatus(409);
        }
    }
    
    private function prepareRentedData($itemId, $start, $period, $months = null, $end = null)
    {
        $data = [
            "start_date" => $start,
            "period" => $period,
            "item_id" => $itemId,
            "termination_period" => "months",
            "termination_months" => 3,
            "payment" => "cyclical",
            "rent" => 1000,
            "payment_day" => 5,
            "first_payment_date" => $start,
            "number_of_people" => 1,
        ];
        
        if($period == Rental::PERIOD_DATE)
            $data["end_date"] = $end;
            
        if($period == Rental::PERIOD_MONTH)
            $data["months"] = $months;
            
        return $data;
    }
    
    private function createItem($uuid)
    {
        $item = new Item;
        $item->uuid = $uuid;
        $item->type = "apartment";
        $item->name = "Test apartment";
        $item->street = "Street";
        $item->city = "City";
        $item->zip = "Zip";
        $item->country = "PL";
        $item->area = "50";
        $item->ownership_type = "property";
        $item->room_rental = 0;
        $item->saveQuietly();
        
        return $item;
    }
    
    private function createTenant($uuid)
    {
        $tenant = new Customer;
        $tenant->uuid = $uuid;
        $tenant->role = Customer::ROLE_TENANT;
        $tenant->type = "person";
        $tenant->name = "John Doe";
        $tenant->saveQuietly();
        
        return $tenant;
    }
}