<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\Exception;
use App\Models\Firm;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\ItemTenant;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserRegisterToken;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function getAccount($id = 0)
    {
        return config('testing.accounts')[$id];
    }
    
    protected function userRegister($id = 0)
    {
        $account = $this->getAccount($id);
            
        $response = $this->post('/api/v1/register', ['email' => $account['email']]);
        $status = $response->getContent();
        
        if($status !== "1")
            throw new Exception('Invalid response status');
        
        $user = User::where('email', $account['email'])->where('owner', 1)->first();
        if(!$user)
            throw new Exception('User not exists');
        
        $tokenRow = UserRegisterToken::where('user_id', $user->id)->first();
        if(!$tokenRow)
            throw new Exception('Token not exists');
        
        return $tokenRow->token;
    }
    
    protected function userRegisterWithConfirmation($id = 0)
    {
        $token = $this->userRegister($id);
        
        $response = $this->post('/api/v1/register/confirm/' . $token, $this->getAccount($id)['data']);
        $response->getContent();
    }
    
    protected function getOwnerLoginToken($id = 0)
    {
        $this->userRegisterWithConfirmation($id);
        $data = [
            'email' => $this->getAccount($id)['email'],
            'password' => $this->getAccount($id)['data']['password'],
            'device_name' => 'test'
        ];
        if(User::where("email", $this->getAccount($id)['email'])->active()->count() > 1)
        {
            $user = User::where("email", $this->getAccount($id)['email'])->where('owner', 1)->active()->first();
            $data["firm_id"] = $user->firm_id;
        }
        
        $response = $this->post('/api/v1/get-token', $data);
        $response->assertStatus(200);
        
        $response = json_decode($response->getContent());
        return $response->token;
    }
    
    protected function prepareMultipleUserAccount($params = [])
    {
        for($id = 0; $id < count(config('testing.accounts')); $id++)
        {
            $this->getOwnerLoginToken($id);
            
            $ownerUser = User::where('email', $this->getAccount($id)['email'])->where('owner', 1)->first();
            foreach($this->getAccount($id)['workers'] as $data)
            {
                $user = new User;
                $user->firm_id = $ownerUser->firm_id;
                $user->firstname = $data["firstname"];
                $user->lastname = $data["lastname"];
                $user->email = $data["email"];
                $user->password = Hash::make($data["password"]);
                $user->phone = $data["phone"];
                $user->owner = 0;
                $user->activated = 1;
                $user->user_permission_id = 0;
                $user->superuser = $data["superuser"];
                $user->save();
            }
            
            if(!empty($params["items"]) && !empty($this->getAccount($id)['items']))
            {
                foreach($this->getAccount($id)['items'] as $data)
                {
                    $item = new Item;
                    $item->uuid = $ownerUser->getUuid();
                    
                    $item->type = $data["type"];
                    $item->active = $data["active"];
                    $item->name = $data["name"];
                    $item->street = $data["street"];
                    $item->house_no = $data["house_no"];
                    $item->apartment_no = $data["apartment_no"];
                    $item->city = $data["city"];
                    $item->zip = $data["zip"];
                    $item->saveQuietly();
                }
            }
        }
    }
    
    protected function getAccountUuui($token)
    {
        $response = $this->withToken($token)->getJson('/api/v1/get-firm-id');
        $firmId = $response->getContent();
        
        $firm = Firm::find($firmId);
        return $firm->uuid;
    }
    
    protected function setUserPermission($email, $permissions)
    {
        if(User::where('email', $email)->count() > 1)
            throw new Exception('User with email address ' . $email . ' exists more than once');
        
        $user = User::where('email', $email)->first();
        if(!$user)
            throw new Exception('User with email address ' . $email . ' not exist');
        
        $firm = Firm::find($user->firm_id);
        
        $userPermission = new UserPermission;
        $userPermission->name = "Test";
        $userPermission->uuid = $firm->uuid;
        $userPermission->permissions = $permissions;
        $userPermission->saveQuietly();
        
        $user->superuser = 0;
        $user->user_permission_id = $userPermission->id;
        $user->save();
    }
    
    protected function createExampleItem($uuid)
    {
        $item = new Item;
        $item->uuid = $uuid;
        $item->name = 'Example estate';
        $item->active = 1;
        $item->name = "Example name";
        $item->street = "Example street";
        $item->house_no = "Example house no";
        $item->apartment_no = "Example apartment no";
        $item->city = "Example city";
        $item->zip = "Zip";
        $item->saveQuietly();
        
        return $item;
    }
    
    protected function createExampleTenant($uuid, $item)
    {
        $tenant = new Tenant;
        $tenant->uuid = $uuid;
        $tenant->name = 'Example tenant';
        $tenant->active = 1;
        $tenant->name = "Example name";
        $tenant->street = "Example street";
        $tenant->house_no = "Example house no";
        $tenant->apartment_no = "Example apartment no";
        $tenant->city = "Example city";
        $tenant->zip = "Zip";
        $tenant->save();
        
        $itemTenant = new ItemTenant;
        $itemTenant->tenant_id = $tenant->id;
        $itemTenant->item_id = $item->id;
        $itemTenant->save();
        
        return $tenant;
    }
}
