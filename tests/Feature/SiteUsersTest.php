<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_detached_from_site()
    {
        $site = Site::factory()->create();
        $user = User::factory()->create();
        $site->users()->attach($user->id);
        $this->assertDatabaseHas('site_user', [
            'site_id' => $site->id,
            'user_id' => $user->id,
        ]);

        $site->users()->detach($user->id);

        $this->assertDatabaseMissing('site_user', [
            'site_id' => $site->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_be_detached_from_site_using_inverse()
    {
        $site = Site::factory()->create();
        $user = User::factory()->create();
        $site->users()->attach($user->id);
        $this->assertDatabaseHas('site_user', [
            'site_id' => $site->id,
            'user_id' => $user->id,
        ]);

        $user->sites()->detach($site->id);

        $this->assertDatabaseMissing('site_user', [
            'site_id' => $site->id,
            'user_id' => $user->id,
        ]);
    }
}
