<p align="center">
  <img src="https://www.seven.io/wp-content/uploads/Logo.svg" width="250" alt="seven logo" />
</p>

<h1 align="center">seven SMS for BoltCMS</h1>

<p align="center">
  Send SMS and text-to-speech calls to your <a href="https://bolt.cm">BoltCMS</a> content types via the seven gateway.
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-teal.svg" alt="MIT License" /></a>
  <img src="https://img.shields.io/badge/Bolt-4%20|%205-blue" alt="Bolt 4 | 5" />
  <img src="https://img.shields.io/badge/PHP-7.2%2B-purple" alt="PHP 7.2+" />
  <a href="https://packagist.org/packages/seven.io/bolt"><img src="https://img.shields.io/packagist/v/seven.io/bolt" alt="Packagist" /></a>
</p>

---

## Features

- **Admin Widget** - Send messages from the Bolt dashboard with a single click
- **SMS & Voice** - Switch between text messages and text-to-speech voice calls
- **Field Placeholders** - Reference any content-type field with `{{fieldname}}` in the message body
- **Configurable Mappings** - Map each content type to its mobile-number field via YAML

## Prerequisites

- BoltCMS 4 or 5
- PHP 7.2+
- A [seven account](https://www.seven.io/) with API key ([How to get your API key](https://help.seven.io/en/developer/where-do-i-find-my-api-key))

## Installation

### 1. Install via Composer

```bash
composer require seven.io/bolt
```

### 2. Add a mobile-phone field to your content types

```yaml
people:
  # ...
  mobile:
    type: text
    variant: inline
  # ...
```

### 3. Configure the extension

Open `/bolt/file-edit/config?file=/extensions/seven-bolt.yaml` (or **Maintenance > Extensions > Configuration** in the admin) and fill in:

| Field | Description |
|-------|-------------|
| `apiKey` | Your seven API key |
| `mappings` | Map each content type to its mobile-number field, e.g. `people: mobile` |

## Usage

### Send SMS

Pick a content type in the seven dashboard widget. Every record with a mobile-number field will receive the message.

### Make text-to-speech calls

Use the same widget but switch the message type to *Voice*. The recipient will receive an automated call reading out the text.

### Message placeholders

Any content-type field can be referenced inside the message body via `{{fieldname}}`:

```
Hi {{name}}, your order is on its way!
```

Make sure the value has a `__toString()` representation - there is no type-coercion magic.

## Support

Need help? Feel free to [contact us](https://www.seven.io/en/company/contact/) or [open an issue](https://github.com/seven-io/bolt/issues).

## License

[MIT](LICENSE)
