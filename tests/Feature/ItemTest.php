<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Item;

class ItemTest extends TestCase
{
    use RefreshDatabase;
    
    private function init()
    {
        $accountUserId = 2;
        $this->prepareMultipleUserAccount(['items' => true]);
        $data = [
            'email' => $this->getAccount($accountUserId)['email'],
            'password' => $this->getAccount($accountUserId)['data']['password'],
            'device_name' => 'test',
        ];
        $response = $this->postJson('/api/v1/login', $data);
        $response = json_decode($response->getContent());
        $token = $response->token;
        
        return $token;
    }
    
    public function test_get_item_empty_list_successfull(): void
    {
        $token = $this->getOwnerLoginToken();
        $response = $this->withToken($token)->getJson('/api/v1/items');
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'total_rows' => 0,
                'total_pages' => 0,
                'current_page' => 1,
                'has_more' => false,
                'data' => []
            ]);
    }
    
    public function test_get_item_nonempty_list_successfull(): void
    {
        $token = $this->init();
        $response = $this->withToken($token)->getJson('/api/v1/items');
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'total_rows' => 2,
                'total_pages' => 1,
                'current_page' => 1,
                'has_more' => false,
                'data' => []
            ]);
    }
    
    public function test_get_item_list_no_login(): void
    {
        $response = $this->getJson('/api/v1/items');
        $response->assertStatus(401);
        
        $response = $this->withToken("xxx")->getJson('/api/v1/items');
        $response->assertStatus(401);
    }
    
    public function test_create_item_successfull(): void
    {
        $token = $this->init();
        $data = [
            "type" => "estate",
            "active" => true,
            "name" => "Example state",
            "street" => "Example street",
            "house_no" => "Example house no",
            "apartment_no" => "Example apartment no",
            "city" => "Example city",
            "zip" => "Zip",
        ];
        $response = $this->withToken($token)->putJson('/api/v1/item', $data);
        $response->assertStatus(200);
    }
    
    public function test_create_item_invalid_params(): void {
        $token = $this->init();
        
        $validData = [
            "type" => "estate",
            "active" => true,
            "name" => "estate",
            "street" => "estate",
            "house_no" => "estate",
            "apartment_no" => "estate",
            "city" => "estate",
            "zip" => "estate",
        ];
        
        $requiredFields = ['name', 'active', 'name', 'street', 'house_no', 'city', 'zip'];
        foreach($requiredFields as $field)
        {
            $data = $validData;
            unset($data[$field]);
            
            $response = $this->withToken($token)->putJson('/api/v1/item', $data);
            $response->assertStatus(422);
        }
    }
    
    public function test_create_item_no_login(): void
    {
        $data = [
            "type" => "estate",
            "active" => true,
            "name" => "Example estate",
            "street" => "Example street",
            "house_no" => "Example house no",
            "apartment_no" => "Example apartment no",
            "city" => "Example city",
            "zip" => "ZIP",
        ];
        $response = $this->putJson('/api/v1/item', $data);
        $response->assertStatus(401);
    }
    
    public function test_get_item_details_successfull(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $response = $this->withToken($token)->getJson('/api/v1/item/' . $item->id);
        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $item->id,
                'name' => $item->name,
                'street' => $item->street,
                'house_no' => $item->house_no,
                'apartment_no' => $item->apartment_no,
                'city' => $item->city,
                'zip' => $item->zip,
            ]);
        
    }
    
    public function test_get_item_details_invalid_id(): void
    {
        $token = $this->init();
        $response = $this->withToken($token)->getJson('/api/v1/item/' . 0);
        $response->assertStatus(404);
    }
    
    public function test_get_item_details_invalid_uuid(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $item->uuid = "xxx";
        $item->save();
        
        $response = $this->withToken($token)->getJson('/api/v1/item/' . $item->id);
        $response->assertStatus(404);
    }
    
    public function test_get_item_details_no_login(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem("xxxx");
        
        $response = $this->getJson('/api/v1/item/' . $item->id);
        $response->assertStatus(401);
        
        $response = $this->withToken("xxxx")->getJson('/api/v1/item/' . $item->id);
        $response->assertStatus(401);
    }
    
    public function test_update_item_successfull(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $data = [
            "active" => false,
            "name" => "Example state after update",
            "street" => "Example street after update",
            "house_no" => "1",
            "apartment_no" => "2",
            "city" => "Example city after update",
            "zip" => "3",
        ];
        $response = $this->withToken($token)->putJson('/api/v1/item/' . $item->id, $data);
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => $data['name'],
            'street' => $data['street'],
            'house_no' => $data['house_no'],
            'apartment_no' => $data['apartment_no'],
            'city' => $data['city'],
            'zip' => $data['zip'],
        ]);
        
        $data2 = [
            "name" => "Example state after update2",
        ];
        $response = $this->withToken($token)->putJson('/api/v1/item/' . $item->id, $data2);
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => $data2['name'],
            'street' => $data['street'],
            'house_no' => $data['house_no'],
            'apartment_no' => $data['apartment_no'],
            'city' => $data['city'],
            'zip' => $data['zip'],
        ]);
    }
    
    public function test_update_item_invalid_id(): void
    {
        $token = $this->init();
        $response = $this->withToken($token)->putJson('/api/v1/item/' . 0);
        $response->assertStatus(404);
    }
    
    public function test_update_item_invalid_uuid(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $item->uuid = "xxx";
        $item->save();
        
        $response = $this->withToken($token)->putJson('/api/v1/item/' . $item->id, ['name' => 'Test']);
        $response->assertStatus(404);
    }
    
    public function test_update_item_invalid_params(): void
    {
        
    }
    
    public function test_update_item_no_login(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem("xxxx");
        
        $response = $this->putJson('/api/v1/item/' . $item->id, ['name' => 'Test']);
        $response->assertStatus(401);
        
        $response = $this->withToken("xxxx")->putJson('/api/v1/item/' . $item->id, ['name' => 'Test']);
        $response->assertStatus(401);
    }
    
    public function test_delete_item_successfull(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $response = $this->withToken($token)->deleteJson('/api/v1/item/' . $item->id);
        $response->assertStatus(200);
    }
    
    public function test_delete_item_invalid_id(): void
    {
        $token = $this->init();
        $response = $this->withToken($token)->deleteJson('/api/v1/item/' . 0);
        $response->assertStatus(404);
    }
    public function test_delete_item_invalid_uuid(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $item->uuid = "xxx";
        $item->save();
        
        $response = $this->withToken($token)->deleteJson('/api/v1/item/' . $item->id);
        $response->assertStatus(404);
    }
    
    public function test_delete_item_no_login(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem("xxxx");
        
        $response = $this->deleteJson('/api/v1/item/' . $item->id);
        $response->assertStatus(401);
        
        $response = $this->withToken("xxxx")->deleteJson('/api/v1/item/' . $item->id);
        $response->assertStatus(401);
    }
    
    public function test_get_item_tenants_empty_list_successfull(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $response = $this->getJson('/api/v1/item/' . $item->id . '/tenants');
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'total_rows' => 0,
                'total_pages' => 0,
                'current_page' => 1,
                'has_more' => false,
                'data' => []
            ]);
    }
    
    public function test_get_item_tenants_nonempty_list_successfull(): void
    {
        $token = $this->init();
        
        $uuid = $this->getAccountUuui($token);
        $item = $this->createExampleItem($uuid);
        $this->createExampleTenant($uuid, $item);
        $this->createExampleTenant($uuid, $item);
        
        $response = $this->getJson('/api/v1/item/' . $item->id . '/tenants');
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'total_rows' => 2,
                'total_pages' => 1,
                'current_page' => 1,
                'has_more' => false,
                'data' => []
            ]);
    }
    
    public function test_get_item_tenants_list_no_login(): void
    {
        $item = $this->createExampleItem("xxx");
        
        $response = $this->getJson('/api/v1/item/' . $item->id . '/tenants');
        $response->assertStatus(401);
        
        $response = $this->withToken("xxx")->getJson('/api/v1/item/' . $item->id . '/tenants');
        $response->assertStatus(401);
    }
    
    public function test_create_item_tenant_successfull(): void
    {
        $token = $this->init();
        $item = $this->createExampleItem($this->getAccountUuui($token));
        
        $data = [
            "active" => true,
            "name" => "Example state",
            "street" => "Example street",
            "house_no" => "Example house no",
            "apartment_no" => "Example apartment no",
            "city" => "Example city",
            "zip" => "Zip",
        ];
        $response = $this->withToken($token)->putJson('/api/v1/item/' . $item->id . '/tenant', $data);
        $response->assertStatus(200);
    }
}