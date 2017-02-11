SessionPanel
=======================

Tracy add-on

Author: Jiří Dorazil

Licence: MIT

Usage
-------

Configuration in `app/config/config.neon`:

```html
services:
	sessionPanel: \Webwings\Tracy\SessionPanel
  tracy.bar:
        setup:            
            - @sessionPanel::register(@session)
```
