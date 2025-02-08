<?php

namespace Niladam\FilamentAutoLogout;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use ReflectionClass;

class AutoLogoutPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool | Closure $enabled = true;

    public bool | Closure $hasWarning = true;

    public bool | Closure $showTimeLeft = true;

    public int | Closure $duration = 900;

    public int | Closure $warnBeforeSeconds = 30;

    public array | Closure $color = Color::Zinc;

    public ?string $timeleftText = null;

    private string $location = PanelsRenderHook::GLOBAL_SEARCH_BEFORE;

    public function getId(): string
    {
        return 'filament-auto-logout';
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            name: $this->getLocation(),
            hook: fn () => $this->renderView($panel->getLogoutUrl())
        );
    }

    public function boot(Panel $panel): void
    {
        $this->timeleftText = $this->timeleftText ?? config('filament-auto-logout.time_left_text');
    }

    protected function renderView(string $logoutUrl): ?string
    {
        return View::make('filament-auto-logout::main', [
            'enabled' => $this->evaluate($this->enabled),
            'has_warning' => $this->evaluate($this->hasWarning),
            'show_time_left' => $this->evaluate($this->showTimeLeft),
            'duration' => $this->evaluate($this->duration),
            'warn_before' => $this->evaluate($this->hasWarning) ? $this->evaluate($this->warnBeforeSeconds) : 0,
            'time_left_text' => $this->timeleftText,
            'color' => $this->getColor(),
            'logout_url' => $logoutUrl,
            'notification_title' => __('filament-auto-logout::auto-logout.notification.title'),
            'notification_body' => __('filament-auto-logout::auto-logout.notification.body'),
            'units' => __('filament-auto-logout::auto-logout.units'),
        ])->render();
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function enableIf(bool | Closure $enabled = true): static
    {
        $this->enabled = $enabled instanceof Closure
            ? (bool) $this->evaluate($enabled)
            : $enabled;

        return $this;
    }

    public function disableIf(Closure $callback): static
    {
        return $this->enableif((bool) $this->evaluate($callback));
    }

    public function withoutWarning(): static
    {
        $this->hasWarning = false;

        return $this;
    }

    public function withoutTimeLeft(): static
    {
        $this->showTimeLeft = false;

        return $this;
    }

    public function warnBefore(int | Closure $warnBefore): static
    {
        $this->warnBeforeSeconds = $warnBefore instanceof Closure
            ? (int) $this->evaluate($warnBefore)
            : $warnBefore;

        return $this;
    }

    public function logoutAfter(int | Closure $duration): static
    {
        $this->duration = $duration instanceof Closure
            ? (int) $this->evaluate($duration)
            : $duration;

        return $this;
    }

    public function timeLeftText(?string $timeLeftText): static
    {
        $this->timeleftText = $timeLeftText;

        return $this;
    }

    public function color(array | Closure $color = Color::Zinc): static
    {
        $this->color = $color;

        return $this;
    }

    protected function getColor(): array
    {
        return $this->evaluate($this->color);
    }

    public function location(string $location = PanelsRenderHook::GLOBAL_SEARCH_BEFORE): static
    {
        $this->location = $this->isValidPanelHook($location)
            ? $location
            : PanelsRenderHook::GLOBAL_SEARCH_BEFORE;

        return $this;
    }

    protected function isValidPanelHook(string $location): bool
    {
        static $validLocations = null;

        if ($validLocations === null) {
            $reflection = new ReflectionClass(PanelsRenderHook::class);
            $validLocations = array_values($reflection->getConstants());
        }

        return in_array($location, $validLocations, true);
    }

    protected function getLocation(): string
    {
        return $this->evaluate($this->location);
    }
}
