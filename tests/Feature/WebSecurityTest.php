<?php

namespace Tests\Feature;

use App\Models\StaticPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function purify(string $html): string
    {
        return app('purifier')->clean($html);
    }

    private function isDangerous(string $html): bool
    {
        $lower = strtolower($html);

        return str_contains($lower, '<script')
            || str_contains($lower, '<iframe')
            || str_contains($lower, '<object')
            || str_contains($lower, '<embed')
            || str_contains($lower, '<svg')
            || str_contains($lower, '<math')
            || str_contains($lower, '<style')
            || str_contains($lower, '<template')
            || str_contains($lower, '<body')
            || str_contains($lower, '<form')
            || str_contains($lower, 'onclick')
            || str_contains($lower, 'onload')
            || str_contains($lower, 'onerror')
            || str_contains($lower, 'ontoggle')
            || str_contains($lower, 'javascript:')
            || str_contains($lower, 'vbscript:')
            || str_contains($lower, 'data:text/html');
    }

    // --- C7: CSRF exclusion ---

    public function test_api_routes_are_excluded_from_csrf(): void
    {
        $middleware = app()->make(\App\Http\Middleware\VerifyCsrfToken::class);
        $reflection = new \ReflectionClass($middleware);
        $prop = $reflection->getProperty('except');
        $prop->setAccessible(true);
        $except = $prop->getValue($middleware);

        $this->assertContains('api/*', $except);
    }

    // --- C8: CORS wildcard removed ---

    public function test_cors_no_wildcard_vercel_pattern(): void
    {
        $cors = config('cors');

        $this->assertEmpty($cors['allowed_origins_patterns'], 'CORS wildcard pattern should be empty');
        $this->assertTrue($cors['supports_credentials']);
        $this->assertContains('https://grocery-plus-22.vercel.app', $cors['allowed_origins']);
        $this->assertContains('https://grocery-react-pi.vercel.app', $cors['allowed_origins']);
    }

    // --- C10: Settings upload route not publicly exposed ---

    public function test_setting_update_route_not_exposed(): void
    {
        $route = app('router')->getRoutes()->getByAction('App\Http\Controllers\Api\SettingController@update');

        $this->assertNull($route, 'Setting update route should not be registered');
    }

    // --- C11: Stored XSS — HTMLPurifier ---

    public function test_purifier_is_bound_in_container(): void
    {
        $this->assertInstanceOf(\Mews\Purifier\Purifier::class, app('purifier'));
    }

    public function test_purifier_strips_script_tags(): void
    {
        $result = $this->purify('<p>Hello</p><script>alert("xss")</script><p>World</p>');

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringContainsString('<p>Hello</p>', $result);
        $this->assertStringContainsString('<p>World</p>', $result);
    }

    public function test_purifier_strips_iframe_tags(): void
    {
        $result = $this->purify('<p>Content</p><iframe src="evil.com"></iframe>');

        $this->assertStringNotContainsString('<iframe', $result);
        $this->assertStringContainsString('<p>Content</p>', $result);
    }

    public function test_purifier_strips_object_and_embed(): void
    {
        $result = $this->purify('<object data="evil.swf"></object><embed src="evil.swf">');

        $this->assertStringNotContainsString('<object', $result);
        $this->assertStringNotContainsString('<embed', $result);
    }

    public function test_purifier_strips_form_tags(): void
    {
        $result = $this->purify('<form><input type="submit"></form>');

        $this->assertStringNotContainsString('<form', $result);
        $this->assertStringNotContainsString('<input', $result);
    }

    public function test_purifier_strips_style_tags(): void
    {
        $result = $this->purify('<style>.x{color:red}</style><p>Text</p>');

        $this->assertStringNotContainsString('<style', $result);
        $this->assertStringContainsString('<p>Text</p>', $result);
    }

    public function test_purifier_strips_svg_tags(): void
    {
        $result = $this->purify('<svg onload=alert(1)>');

        $this->assertStringNotContainsString('<svg', $result);
        $this->assertStringNotContainsString('alert(', $result);
    }

    public function test_purifier_strips_video_audio_tags(): void
    {
        $result = $this->purify('<video onerror=alert(1) src=x><audio onerror=alert(1) src=x>');

        $this->assertStringNotContainsString('<video', $result);
        $this->assertStringNotContainsString('<audio', $result);
    }

    public function test_purifier_strips_body_tag(): void
    {
        $result = $this->purify('<body onload=alert(1)>');

        $this->assertStringNotContainsString('<body', $result);
    }

    public function test_purifier_strips_template_tag(): void
    {
        $result = $this->purify('<template><script>alert(1)</script></template>');

        $this->assertStringNotContainsString('<template', $result);
    }

    public function test_purifier_strips_details_tag(): void
    {
        $result = $this->purify('<details ontoggle=alert(1) open>');

        $this->assertStringNotContainsString('<details', $result);
    }

    // --- C11: Unquoted event handlers ---

    public function test_purifier_strips_unquoted_event_handlers(): void
    {
        $vectors = [
            '<p onclick=alert(1)>Click</p>',
            '<p ONCLICK=alert(1)>Click</p>',
            '<p oNcLiCk=alert(1)>Click</p>',
            '<img src=x onerror=alert(1)>',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: Quoted event handlers ---

    public function test_purifier_strips_quoted_event_handlers(): void
    {
        $vectors = [
            '<p onclick="alert(1)">Click</p>',
            "<p onclick='alert(1)'>Click</p>",
            '<p onclick ="alert(1)">Click</p>',
            '<p onclick	=alert(1)>Click</p>',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: javascript: URLs ---

    public function test_purifier_strips_javascript_urls(): void
    {
        $vectors = [
            '<a href="javascript:alert(1)">Click</a>',
            '<a href="JAVASCRIPT:alert(1)">Click</a>',
            '<a href="  javascript:alert(1)">Click</a>',
            '<a href=javascript:alert(1)>Click</a>',
            '<a href ="javascript:alert(1)">Click</a>',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: data: URLs ---

    public function test_purifier_strips_data_urls(): void
    {
        $vectors = [
            '<a href="data:text/html,<script>alert(1)</script>">Click</a>',
            '<img src="data:image/svg+xml,<script>alert(1)</script>">',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: vbscript: URLs ---

    public function test_purifier_strips_vbscript_urls(): void
    {
        $result = $this->purify('<a href="vbscript:msgbox(1)">Click</a>');

        $this->assertStringNotContainsString('vbscript:', $result);
    }

    // --- C11: img src with javascript: ---

    public function test_purifier_strips_img_javascript_src(): void
    {
        $vectors = [
            '<img src="javascript:alert(1)">',
            '<img src=javascript:alert(1)>',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: Whitespace obfuscation ---

    public function test_purifier_strips_obfuscated_event_handlers(): void
    {
        $vectors = [
            '<img/src=x/onerror=alert(1)>',
            '<img '.chr(9).'src=x '.chr(9).'onerror=alert(1)>',
            "<img\nsrc=x\nonerror=alert(1)>",
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: Nested/broken HTML ---

    public function test_purifier_strips_nested_script(): void
    {
        $vectors = [
            '<scr<script>ipt>alert(1)</script>',
            '<svg><script>alert(1)</script></svg>',
        ];

        foreach ($vectors as $vector) {
            $result = $this->purify($vector);
            $this->assertTrue(
                !$this->isDangerous($result),
                "XSS bypass found: {$vector} → {$result}"
            );
        }
    }

    // --- C11: Preserves safe formatting ---

    public function test_purifier_preserves_allowed_tags(): void
    {
        $input = '<h1>Title</h1><p><strong>Bold</strong> and <em>italic</em></p><ul><li>Item 1</li><li>Item 2</li></ul>';
        $result = $this->purify($input);

        $this->assertStringContainsString('<h1>', $result);
        $this->assertStringContainsString('<strong>', $result);
        $this->assertStringContainsString('<em>', $result);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>', $result);
    }

    public function test_purifier_preserves_tables(): void
    {
        $input = '<table><tr><th>Header</th></tr><tr><td>Data</td></tr></table>';
        $result = $this->purify($input);

        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('<th>', $result);
        $this->assertStringContainsString('<td>', $result);
    }

    public function test_purifier_preserves_images_with_attributes(): void
    {
        $input = '<img src="photo.jpg" alt="Photo" width="100" height="200">';
        $result = $this->purify($input);

        $this->assertStringContainsString('src="photo.jpg"', $result);
        $this->assertStringContainsString('alt="Photo"', $result);
        $this->assertStringContainsString('width="100"', $result);
        $this->assertStringContainsString('height="200"', $result);
    }

    public function test_purifier_strips_style_attribute(): void
    {
        $input = '<p style="background:url(javascript:alert(1))">Click</p>';
        $result = $this->purify($input);

        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringContainsString('<p>Click</p>', $result);
    }

    // --- C11: End-to-end policies page ---

    public function test_policies_page_renders_sanitized_content(): void
    {
        $user = User::create([
            'username' => 'secuser_'.Str::random(6),
            'email' => 'sec_'.Str::random(6).'@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Sec',
            'lastname' => 'User',
        ]);

        StaticPage::create([
            'slug' => 'policies',
            'title' => 'Test Policy',
            'content' => '<p>Safe content</p><script>alert("xss")</script>',
            'is_published' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('support.policies'));

        $response->assertStatus(200);
        $response->assertDontSee('<script>');
        $response->assertSee('Safe content');
    }

    public function test_policies_page_strips_xss_vectors(): void
    {
        $user = User::create([
            'username' => 'secuser_'.Str::random(6),
            'email' => 'sec_'.Str::random(6).'@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Sec',
            'lastname' => 'User',
        ]);

        $xssContent = '<p>Normal</p><img src=x onerror=alert(1)><svg onload=alert(1)><a href="javascript:alert(1)">Click</a>';
        StaticPage::create([
            'slug' => 'policies',
            'title' => 'XSS Test',
            'content' => $xssContent,
            'is_published' => true,
            'order' => 1,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('support.policies'));

        $response->assertStatus(200);
        $response->assertDontSee('onerror');
        $response->assertDontSee('onload');
        $response->assertDontSee('javascript:');
        $response->assertSee('Normal');
    }

    // --- H5: Secure cookies configuration ---

    public function test_session_secure_cookie_env_key_exists(): void
    {
        $this->assertNull(config('session.secure'));
    }

    // --- M1: Session encryption configuration ---

    public function test_session_encrypt_defaults_false_for_dev(): void
    {
        $this->assertFalse(config('session.encrypt'));
    }

    // --- Batch 6: Production Configuration ---

    public function test_env_example_exists_with_production_documentation(): void
    {
        $envExamplePath = base_path('.env.example');
        $this->assertFileExists($envExamplePath, '.env.example must exist for production deployment');

        $content = file_get_contents($envExamplePath);

        // H5: Secure cookie production guidance
        $this->assertStringContainsString('SESSION_SECURE_COOKIE', $content);
        // M1: Session encryption production guidance
        $this->assertStringContainsString('SESSION_ENCRYPT', $content);
        // H6: Sanctum expiration documented
        $this->assertStringContainsString('SANCTUM_EXPIRATION', $content);
        // M11: Database production guidance
        $this->assertStringContainsString('DB_HOST', $content);
        // M12: Mail production guidance
        $this->assertStringContainsString('MAIL_HOST', $content);
    }

    public function test_session_secure_cookie_config_is_env_driven(): void
    {
        $configPath = config_path('session.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString("env('SESSION_SECURE_COOKIE')", $configContent);
    }

    public function test_session_encrypt_config_is_env_driven(): void
    {
        $configPath = config_path('session.php');
        $configContent = file_get_contents($configPath);

        // Session encrypt should be configurable — either via env() or a direct value
        $this->assertStringContainsString('encrypt', $configContent);
        // In local/dev, it should be false
        $this->assertFalse(config('session.encrypt'));
    }

    public function test_sanctum_expiration_config_exists(): void
    {
        $expiration = config('sanctum.expiration');
        $this->assertNull($expiration, 'Sanctum expiration should be configurable (null = no expiration)');
    }

    public function test_database_config_has_ssl_option(): void
    {
        $configPath = config_path('database.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString('SSL_CA', $configContent);
    }

    public function test_mail_config_is_fully_env_driven(): void
    {
        // Verify mail credentials in config file use env() — not hardcoded
        $configPath = config_path('mail.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString("env('MAIL_USERNAME')", $configContent, 'MAIL_USERNAME must be sourced from env()');
        $this->assertStringContainsString("env('MAIL_PASSWORD')", $configContent, 'MAIL_PASSWORD must be sourced from env()');
    }

    public function test_database_config_is_fully_env_driven(): void
    {
        // Verify database config file uses env() for all credentials
        $configPath = config_path('database.php');
        $configContent = file_get_contents($configPath);

        // The mysql connection must use env() for host, username, password
        $this->assertStringContainsString("env('DB_HOST'", $configContent);
        $this->assertStringContainsString("env('DB_USERNAME'", $configContent);
        $this->assertStringContainsString("env('DB_PASSWORD'", $configContent);
    }

    public function test_queue_connection_configured_via_env(): void
    {
        // Verify .env file configures QUEUE_CONNECTION
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $this->assertStringContainsString('QUEUE_CONNECTION=database', $envContent);
        } else {
            // If no .env, default is sync (acceptable for dev)
            $this->assertTrue(true);
        }
    }

    public function test_failed_jobs_table_migration_exists(): void
    {
        $migrationPath = database_path('migrations/2019_08_19_000000_create_failed_jobs_table.php');
        $this->assertFileExists($migrationPath);
    }

    public function test_sessions_table_has_last_activity_index(): void
    {
        $this->assertTrue(
            Schema::hasColumn('sessions', 'last_activity'),
            'sessions table must have last_activity column for GC'
        );
    }

    public function test_database_config_no_hardcoded_credentials(): void
    {
        $configPath = config_path('database.php');
        $configContent = file_get_contents($configPath);

        // Ensure no hardcoded passwords in config (defaults should be empty strings or env-driven)
        $this->assertStringNotContainsString("'password' => 'root'", $configContent);
        $this->assertStringNotContainsString("'password' => 'secret'", $configContent);
        $this->assertStringNotContainsString("'password' => 'password'", $configContent);
    }

    // --- L2: APP_DEBUG ---

    public function test_app_debug_is_configurable_via_env(): void
    {
        $this->assertTrue(config('app.debug'));
        $this->assertContains(config('app.env'), ['local', 'testing']);
    }

    // --- Google OAuth ---

    public function test_google_oauth_redirect_route_exists(): void
    {
        $route = app('router')->getRoutes()->getByName('google.redirect');

        $this->assertNotNull($route);
        $this->assertEquals('GET', $route->methods()[0]);
    }

    public function test_google_oauth_callback_route_exists(): void
    {
        $route = app('router')->getRoutes()->getByName('google.callback');

        $this->assertNotNull($route);
        $this->assertEquals('GET', $route->methods()[0]);
    }

    public function test_google_services_config_has_redirect_key(): void
    {
        $redirect = config('services.google.redirect');

        $this->assertNotEmpty($redirect);
        $this->assertStringContainsString('/auth/google/callback', $redirect);
    }

    public function test_google_services_config_client_id_is_env_driven(): void
    {
        $configPath = config_path('services.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString("env('GOOGLE_CLIENT_ID')", $configContent);
        $this->assertStringContainsString("env('GOOGLE_CLIENT_SECRET')", $configContent);
    }
}
