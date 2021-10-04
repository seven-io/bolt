![Sms77.io Logo](https://www.sms77.io/wp-content/uploads/2019/07/sms77-Logo-400x79.png "Sms77.io Logo")

# Official Extension for [BoltCMS](https://bolt.cm)
Send SMS and make text-to-speech calls.

## Installation

1. `composer require sms77/bolt`

2. Add Content Type for mobile phone field:

```yaml
    people:
      # ...
      mobile:
        type: text
        variant: inline
      # ...
```

3. Head to `/bolt/file-edit/config?file=/extensions/sms77-bolt.yaml` and fill out apiKey.
   Alternatively adjust `mappings` where each key represents a content type and the
   corresponding value is a mobile number field. **Notice:** You can also edit this file via `Maintenance->Extensions->Configuration` in the administration area.

## Usage
Check out the widget in the administration dashboard.

### Send SMS
Send SMS to all of your content types.

### Make Text-To-speech calls
Calls a given phone number and reads the given text out loud.

### Message Placeholders

Each content type field can be used in the text surrounded by {{...}} e.g. {{name}}
resolves to the field `name`. Make sure that the value has implemented a `.toString()`
method as there is no type checking implemented as of now.

#### Support

Need help? Feel free to [contact us](https://www.sms77.io/en/company/contact/).

[![MIT](https://img.shields.io/badge/License-MIT-teal.svg)](LICENSE)
