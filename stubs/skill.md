# {{PluginName}} — AI Agent Skill Reference

This file gives AI agents the context needed to develop in this plugin correctly and structurally.

**Package:** `{{vendor}}/{{plugin-slug}}`
**Root namespace:** `{{Namespace}}`
**PSR-4 root:** `src/`
**Text domain:** `{{plugin-slug}}`

> **Utilities reference:** All framework APIs (Hook, DB, Ajax, Rest, Asset, Cache, Log, Meta, PostType, Cron, etc.) are documented in the
> [uupcode/utilities skill.md](https://github.com/uupcode/utilities/blob/main/skill.md).
> Read that file first when you need the API for any utility class.

---

## Plugin Structure

```
{{plugin-slug}}/
├── {{plugin-slug}}.php              # Entry point — calls Plugin::boot(__FILE__)
├── src/
│   ├── Plugin.php                   # Lifecycle: boot, init, activate, deactivate, uninstall
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ExampleController.php   # REST response handlers
│   │   └── Requests/
│   │       └── ExampleRequest.php      # AJAX nonce + authorization contract
│   ├── Models/
│   │   └── ExampleModel.php            # DB model extending UupCode\Utilities\Database\Model
│   └── Providers/
│       ├── HookServiceProvider.php         # General actions/filters
│       ├── AssetServiceProvider.php        # wp_enqueue_scripts / admin_enqueue_scripts
│       ├── AdminServiceProvider.php        # Admin menu pages and action links
│       ├── RestServiceProvider.php         # REST API routes (rest_api_init)
│       ├── BlockServiceProvider.php        # Gutenberg block registration
│       ├── AjaxServiceProvider.php         # AJAX handlers (optional)
│       ├── PostTypeServiceProvider.php     # Custom post types + taxonomies (optional)
│       ├── CronServiceProvider.php         # Scheduled events (optional)
│       └── ShortcodeServiceProvider.php    # Shortcodes (optional)
├── resources/                       # JS/CSS source — compiled by @wordpress/scripts
│   ├── index.js / index.css         # Frontend entry
│   ├── admin.js / admin.css         # Admin entry
│   └── blocks/example/             # Example Gutenberg block
│       ├── block.json
│       ├── index.js
│       ├── editor.css
│       └── style.css
├── build/                           # Compiled assets (gitignored — run npm run build)
├── languages/                       # .pot / .json translation files
├── templates/                       # PHP view templates (loaded via View or require)
├── tests/Unit/                      # PHPUnit tests
├── webpack.config.js
└── package.json
```

---

## Boot Sequence

```
{{plugin-slug}}.php
  └── Plugin::boot(__FILE__)
        ├── BasePlugin::boot(__FILE__)         // registers path/URL helpers
        ├── onActivate / onDeactivate / onUninstall  // must be registered immediately
        └── add_action('plugins_loaded', Plugin::init)
              ├── load_plugin_textdomain()
              ├── (new HookServiceProvider())->register()
              ├── (new RestServiceProvider())->register()
              ├── (new AssetServiceProvider())->register()
              ├── (new AdminServiceProvider())->register()
              └── (new BlockServiceProvider())->register()
              // Optional — uncomment in Plugin::init() as needed:
              // (new AjaxServiceProvider())->register()
              // (new PostTypeServiceProvider())->register()
              // (new CronServiceProvider())->register()
              // (new ShortcodeServiceProvider())->register()
```

Activation, deactivation, and uninstall hooks are registered inside `Plugin::boot()` before
`plugins_loaded` fires. All other providers are registered inside `Plugin::init()`.

---

## Where to Add New Code

| What you need to add | Where to put it |
|---|---|
| A new action or filter | `HookServiceProvider` — add a `#[Action]` or `#[Filter]` method |
| A new REST endpoint | `RestServiceProvider::registerRoutes()` — add a `Rest::get/post/...` call |
| A new AJAX handler | `AjaxServiceProvider::register()` — add `Ajax::handle(...)` |
| A new admin page | `AdminServiceProvider` — add to `addMenuPages()` |
| Enqueue a new script/style | `AssetServiceProvider` — add to `enqueueFrontend()` or `enqueueAdmin()` |
| A new custom post type | `PostTypeServiceProvider::register()` — add `PostType::make(...)` |
| A new taxonomy | `PostTypeServiceProvider::register()` — add `Taxonomy::make(...)` |
| A new scheduled task | `CronServiceProvider::register()` — add `Cron::add(...)` |
| A new shortcode | `ShortcodeServiceProvider` — add `Shortcode::register(...)` |
| A new Gutenberg block | Add a directory under `resources/blocks/`, add a `block.json` |
| A new database table | Add a `Model` in `src/Models/`, call `createTable()` from `Plugin::activate()` |
| Plugin option storage | Use `Option::get/set` or `Cache::remember` in the relevant provider |

---

## Provider Patterns

### Attribute-based (most providers)

Use `#[Action]` and `#[Filter]` attributes. `register()` is inherited and handles hook registration automatically.

```php
use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Attributes\Filter;

final class HookServiceProvider extends ServiceProvider
{
    #[Action('init')]
    public function onInit(): void { }

    #[Action('save_post', priority: 20, args: 2)]
    public function onSavePost(int $postId, \WP_Post $post): void { }

    #[Filter('the_content')]
    public function filterContent(string $content): string
    {
        return $content;
    }
}
```

### Override-based (Ajax, PostType, Cron, Shortcode)

These providers manage their own hook registration — override `register()` directly.

```php
final class AjaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Ajax::handle('{{plugin_slug}}_save', function(SaveRequest $request) {
            Ajax::json(['ok' => true]);
        })->register();
    }
}
```

---

## Namespacing Conventions

| Type | Namespace | Example |
|---|---|---|
| Providers | `{{Namespace}}\Providers` | `HookServiceProvider` |
| HTTP controllers | `{{Namespace}}\Http\Controllers` | `ExampleController` |
| AJAX requests | `{{Namespace}}\Http\Requests` | `ExampleRequest` |
| Models | `{{Namespace}}\Models` | `ExampleModel` |
| Plugin core | `{{Namespace}}` | `Plugin` |

---

## Models

Extend `UupCode\Utilities\Database\Model`. Table name uses the `wp_` prefix automatically.

```php
use UupCode\Utilities\Database\Model;

final class ExampleModel extends Model
{
    protected static string $table = '{{plugin_slug}}_examples'; // → wp_{{plugin_slug}}_examples

    protected static array $casts = [
        'meta' => 'array',
    ];

    public static function createTable(): void
    {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS " . self::table() . " (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$wpdb->get_charset_collate()};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
```

Call `ExampleModel::createTable()` inside `Plugin::activate()`.

---

## AJAX Request Contract

Every AJAX handler should have a dedicated `AjaxRequest` subclass:

```php
use UupCode\Utilities\Http\AjaxRequest;

final class ExampleRequest extends AjaxRequest
{
    public function authorize(): bool
    {
        return is_user_logged_in();
    }

    public function nonceAction(): string
    {
        return '{{plugin_slug}}_example';
    }
}
```

Pass the nonce from PHP to JS via `Asset::script()->localize()` in `AssetServiceProvider`.

---

## Assets

Assets are compiled from `resources/` to `build/` by `@wordpress/scripts`.

```bash
npm run build    # production build
npm run start    # watch mode
```

Always read the `.asset.php` manifest for dependency and version data:

```php
$asset = file_exists(Plugin::path('build/index.asset.php'))
    ? require Plugin::path('build/index.asset.php')
    : ['dependencies' => [], 'version' => '1.0.0'];

Asset::script('{{plugin-slug}}', Plugin::url('build/index.js'))
    ->deps(...$asset['dependencies'])
    ->version($asset['version'])
    ->footer()
    ->enqueue();
```

---

## Blocks

Each block lives in `resources/blocks/{block-name}/` with a `block.json`.
`BlockServiceProvider` auto-registers every block found in `build/blocks/` at runtime — no manual registration needed when you add a new block.

Block name format: `{{plugin-slug}}/{block-name}` (defined in `block.json`).

---

## Development Commands

```bash
# PHP
composer test       # PHPUnit
composer analyse    # PHPStan (level 6)
composer cs         # Check code style (PSR-12)
composer cs:fix     # Auto-fix code style

# JS/CSS
npm run build       # Compile assets
npm run start       # Watch mode
npm run lint:js     # Lint JS
npm run lint:css    # Lint CSS

# i18n
npm run i18n:pot    # Generate .pot file
npm run i18n:json   # Generate JSON files for JS translations

# Distribution
npm run bundle      # Build + generate POT + zip to dist/{{plugin-slug}}.zip
```

---

## Key Rules

- **Never call WordPress functions before `plugins_loaded`** (except activation/deactivation hooks, which must be registered in `boot()`).
- **All providers are registered in `Plugin::init()`**, not in `boot()`.
- **Optional providers** (Ajax, PostType, Cron, Shortcode) are commented out in `Plugin::init()` — uncomment only the ones you need.
- **Table creation belongs in `Plugin::activate()`**, not in provider `register()` calls.
- **Use `{{plugin-slug}}` as the text domain** for all translation functions.
- **Use `{{plugin_slug}}_` as the prefix** for option names, meta keys, AJAX actions, cron hooks, and nonce actions to avoid collisions.
