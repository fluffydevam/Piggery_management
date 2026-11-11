<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_url_redirects_to_the_admin_panel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_admin_login_page_loads(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_an_authenticated_user_can_open_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function listPageProvider(): array
    {
        return [
            'cages' => ['/admin/cages'],
            'pigs' => ['/admin/pigs'],
            'breedings' => ['/admin/breedings'],
            'farrowings' => ['/admin/farrowings'],
            'litters' => ['/admin/litters'],
            'feeds' => ['/admin/feeds'],
            'feed transactions' => ['/admin/feed-transactions'],
            'medications' => ['/admin/medications'],
            'medical logs' => ['/admin/medical-logs'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function createPageProvider(): array
    {
        return [
            'cages' => ['/admin/cages/create'],
            'pigs' => ['/admin/pigs/create'],
            'breedings' => ['/admin/breedings/create'],
            'feeds' => ['/admin/feeds/create'],
            'feed transactions' => ['/admin/feed-transactions/create'],
            'medications' => ['/admin/medications/create'],
            'medical logs' => ['/admin/medical-logs/create'],
        ];
    }

    #[DataProvider('listPageProvider')]
    public function test_every_resource_list_page_loads(string $url): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get($url)->assertOk();
    }

    #[DataProvider('createPageProvider')]
    public function test_every_resource_create_page_loads(string $url): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get($url)->assertOk();
    }

    public function test_farrowings_and_litters_cannot_be_created_manually(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/farrowings/create')->assertNotFound();
        $this->actingAs($user)->get('/admin/litters/create')->assertNotFound();
    }

    /**
     * Regression guard: table column callbacks (accessors, relationships,
     * date math) only run when rows exist. Seed the demo farm and render
     * every list page with real data.
     */
    public function test_every_list_page_renders_with_seeded_data(): void
    {
        $this->seed();

        $user = User::factory()->create();

        foreach (self::listPageProvider() as [$url]) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_the_dashboard_renders_all_widgets_with_seeded_data(): void
    {
        $this->seed();

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    /**
     * Dashboard widgets are lazy-loaded, so their tables never render in the
     * initial page response. Test each widget as its own Livewire component.
     */
    public function test_dashboard_widgets_render_with_seeded_data(): void
    {
        $this->seed();

        $user = User::factory()->create();
        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Filament\Widgets\FarmStatsOverview::class)->assertOk();
        \Livewire\Livewire::test(\App\Filament\Widgets\UpcomingFarrowings::class)->assertOk();
        \Livewire\Livewire::test(\App\Filament\Widgets\LowStockFeeds::class)->assertOk();
        \Livewire\Livewire::test(\App\Filament\Widgets\MedicalScheduleWidget::class)->assertOk();
    }
}
