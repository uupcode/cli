# uupcode/cli

WP-CLI scaffolding tool that generates WordPress plugins pre-wired with [uupcode/utilities](https://github.com/uupcode/utilities).

## Requirements

- PHP 8.1+
- WP-CLI 2.0+

> **Note:** `uupcode/utilities` is currently installed directly from GitHub. The generated plugin's `composer.json` includes the VCS repository entry automatically. Ensure your environment has SSH access to GitHub, or swap the repository URL to the HTTPS equivalent (`https://github.com/uupcode/utilities`).

## Installation

```bash
wp package install uupcode/cli
```

## Usage

```bash
wp uup-plugin scaffold
```

You will be prompted for plugin details interactively. Alternatively, pass everything as flags to skip the prompts:

```bash
wp uup-plugin scaffold \
  --name="My Plugin" \
  --vendor=myvendor \
  --description="A WordPress plugin." \
  --author="Jane Doe" \
  --author-uri="https://example.com" \
  --plugin-uri="https://example.com/my-plugin" \
  --dir=/path/to/output
```

| Option | Description | Default |
|---|---|---|
| `--name` | Plugin name | prompted |
| `--vendor` | Packagist vendor prefix (lowercase) | prompted |
| `--description` | Plugin description | prompted |
| `--author` | Author name | prompted |
| `--author-uri` | Author URI | prompted |
| `--plugin-uri` | Plugin URI | prompted |
| `--dir` | Output directory | `<cwd>/<plugin-slug>` |

## What gets generated

```
my-plugin/
├── my-plugin.php               # Main plugin file
├── composer.json               # Requires uupcode/utilities
├── package.json                # @wordpress/scripts build setup
├── webpack.config.js
├── src/
│   ├── Plugin.php              # Boot + lifecycle hooks
│   ├── Http/
│   │   ├── Controllers/ExampleController.php
│   │   └── Requests/ExampleRequest.php
│   ├── Models/ExampleModel.php
│   └── Providers/
│       ├── HookServiceProvider.php
│       ├── AssetServiceProvider.php
│       ├── AdminServiceProvider.php
│       ├── RestServiceProvider.php
│       ├── BlockServiceProvider.php
│       ├── AjaxServiceProvider.php     # optional
│       ├── PostTypeServiceProvider.php # optional
│       ├── CronServiceProvider.php     # optional
│       └── ShortcodeServiceProvider.php# optional
├── resources/
│   ├── index.js / index.css
│   ├── admin.js / admin.css
│   └── blocks/example/
├── tests/
│   ├── Unit/
│   └── bootstrap.php
└── languages/
```

`composer install` runs automatically after generation.

## Development

```bash
composer test       # PHPUnit
composer analyse    # PHPStan (level 6)
composer cs         # Check code style
composer cs:fix     # Fix code style
```

## License

GPL-2.0-or-later
