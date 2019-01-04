<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\CURD\TestDestroy;
use Tests\Feature\Admin\CURD\TestEdit;
use Tests\Feature\Admin\CURD\TestForceDelete;
use Tests\Feature\Admin\CURD\TestIndex;
use Tests\Feature\Admin\CURD\TestRestore;

class UserControllerTest extends TestCase
{
    use TestIndex, TestEdit, TestDestroy, TestRestore, TestForceDelete;

    protected $urlPrefix = 'admin/user/';

    public function testUpdate()
    {
        $this->adminPost('update/' . $this->edit_id, [
            'name' => 'example',
            'email' => 'user@example.com',
            'password' => '666666'
        ])->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'name' => 'example',
            'email' => 'user@example.com',
        ]);
    }

}
