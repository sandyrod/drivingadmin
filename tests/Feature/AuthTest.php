<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ImageUploads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/', '/driving', '/test-design', '/payments/1/receipt'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $this->postJson('/students', [])->assertUnauthorized();
        $this->deleteJson('/vehicles/1')->assertUnauthorized();
        $this->putJson('/reviews/1/visibility')->assertUnauthorized();
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-1'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_locked_after_too_many_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        // Even the right password is refused while locked
        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-1'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_user_is_sent_away_from_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_uploads_require_login_and_reject_bad_paths(): void
    {
        $dir = ImageUploads::root() . DIRECTORY_SEPARATOR . 'students';
        @mkdir($dir, 0755, true);
        $file = $dir . DIRECTORY_SEPARATOR . 'test_auth_upload.png';
        // 1x1 transparent PNG
        file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));

        try {
            $this->get('/uploads/students/test_auth_upload.png')->assertRedirect('/login');

            $this->actingAs(User::factory()->create());
            $this->get('/uploads/students/test_auth_upload.png')->assertOk();
            $this->get('/uploads/students/missing.png')->assertNotFound();
            $this->get('/uploads/other/test_auth_upload.png')->assertNotFound();
        } finally {
            @unlink($file);
        }
    }

    public function test_upload_url_validation_blocks_path_traversal(): void
    {
        $this->assertTrue(ImageUploads::isValidUrl('/uploads/licenses/lic_front_abc123.jpeg'));
        $this->assertFalse(ImageUploads::isValidUrl('/uploads/../../.env'));
        $this->assertFalse(ImageUploads::isValidUrl('/uploads/students/../../.env'));
        $this->assertFalse(ImageUploads::isValidUrl('/uploads/students/shell.php'));
        $this->assertFalse(ImageUploads::isValidUrl('/uploads/students/a/b.png'));
    }

    public function test_dashboard_renders_for_logged_in_user(): void
    {
        $user = User::factory()->create(['name' => 'Ana Admin']);

        $this->actingAs($user)->get('/driving')
            ->assertOk()
            ->assertSee('Ana Admin')
            ->assertSee('Cerrar sesión');
    }

    public function test_non_image_content_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Declared as PNG but the content is PHP code
        ImageUploads::store('data:image/png;base64,' . base64_encode('<?php echo 1; ?>'), 'students', 'student_profile');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
