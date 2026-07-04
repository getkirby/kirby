# Kirby 6.0.0 Alpha Changelog

Kirby 6 and this document are work in progress. The changelog will be updated with every pre-release for Kirby 6.

> [!IMPORTANT]  
> Don't use Kirby 6 in production yet.

## 🎉 Features

### Preview view

Live editor mode that let's you work on your content right next to a live preview of your page (https://feedback.getkirby.com/736)

- Preview view syncs navigation in the browser(s)
- Open the preview in a second browser window and still see a live preview of your changes
- Preview your site in different viewport widths in the preview view
- New `panel.preview.sizes` option to define custom viewport sizes

https://github.com/user-attachments/assets/b9bde4cc-ce03-410d-8cd7-e470142e51e0

### New buttons field [#7828](https://github.com/getkirby/kirby/pull/7828)

New `buttons` field to display (a list of) buttons to open a link, dialog or drawer

<img width="954" height="712" alt="image" src="https://github.com/user-attachments/assets/1676c25b-2614-4f2c-8563-216906079ae3" />

**Docs**

```yaml
simple:
  type: buttons
  buttons:
    - text: Button A
      link: <https://getkirby.com>
    - text: Button B
      link: <https://getkirby.com>
    - text: Button C
      link: <https://getkirby.com>

icons:
  type: buttons
  buttons:
    - text: Button A
      icon: heart
      link: <https://getkirby.com>
    - text: Button B
      icon: star
      link: <https://getkirby.com>
    - text: Button C
      icon: badge
      link: <https://getkirby.com>
```

### Security & authentication

- User security drawer in the Panel: a single place on the user/account view to handle security-related matters, e.g. email, password, two-factor (TOTP) management. [#7848](https://github.com/getkirby/kirby/pull/7848)
- New `auth.passwords` option to define a password policy (e.g. custom min/max length, custom regex etc.) https://feedback.getkirby.com/697 [#8211](https://github.com/getkirby/kirby/pull/8211)
- Switch between challenges mid-login: when a challenge is pending, users can switch the active challenge (e.g. authenticator-app TOTP vs. emailed code) without restarting [#7848](https://github.com/getkirby/kirby/pull/7848)
- Auth methods extension: plugins can ship their own login method, just like custom challenges. [#7848](https://github.com/getkirby/kirby/pull/7848)
- Custom forms for auth methods and challenges: each method/challenge can declare its own Panel form via `::form()`, which is what makes the login UI fully extensible end-to-end. [#7848](https://github.com/getkirby/kirby/pull/7848)

### Simplified blueprint definition

We know that our blueprints can be very complex to understand. One problem is the deeply nested structure, when defining tabs, columns and sections. In Kirby 6, blueprint definition gets a lot easier. Fields can now be defined on the root level, globally and referenced anywhere in your layout afterwards. This will reduce nesting and makes it easier to change layouts later. The old way of defining fields inline in a nested structure still works for perfect backward compatibility. [#7899](https://github.com/getkirby/kirby/pull/7899)

**Example**

```yaml
title: Article

fields:
  subheading:
    type: text
  cover:
    type: files
  text:
    type: blocks
  date:
    type: date
  author:
    type: users
  tags:
    type: tags

columns:
  - width: 2/3
    fields:
      - subheading
      - cover
      - text

  - width: 1/3
    fields:
      - date
      - author
      - tags
```

Of course, this is also possible with multiple tabs:

```yaml
title: Article

fields:
  subheading:
    type: text
  cover:
    type: files
  text:
    type: blocks
  date:
    type: date
  author:
    type: users
  tags:
    type: tags
  seoTitle:
    type: text
  seoDescription:
    type: textarea

tabs:
  content:
    columns:
      - width: 2/3
        fields:
          - subheading
          - cover
          - text

      - width: 1/3
        fields:
          - date
          - author
          - tags
  seo:
    fields:
      - seoTitle
      - seoDescription
```

### Template stacks

Template stacks let snippets and templates push output onto a stack and render this stack anywhere else in your template (e.g. pushing a CSS `<link>` tag on a stack from inside your blocks snippets and render this stack inside `<head>`). This can be done independent of rendering the stack and pushing to the stack. [#7867](https://github.com/getkirby/kirby/pull/7867)

```php
<head>

  <style>
    <?= stack('style') ?>
  </style>
</head>

// snippet
<?php push('style') ?>

body {
  background: red;
}

<?php endpush(); ?>

// in template (e.g. header.php)
<?php stack('head') ?>

// in any snippet or template (order doesn’t matter)
<?php push('head') ?>
<link rel="stylesheet" href="/assets/css/foo.css">
<?php endpush() ?>
```

Direct push (no buffering)

```php
<?php push('head', '<link rel="stylesheet" href="/assets/css/foo.css">') ?>
```

Unique pushes (dedupe identical content)

```php
<?php push('head', '<link rel="stylesheet" href="/assets/css/foo.css">', unique: true) ?>
```

Returning instead of echoing

```php
<?php $styles = stack('head', return: true); ?>
```

Custom glue

```php
<?php stack('head', glue: '') ?> // join with newlines
```

### Better error handling in the panel

- New `<k-error-trace>` component to display PHP stack traces. We will use it for PHP error stacks in error dialogs, but it's universal enough to also be used in other places or for JS traces. [#7774](https://github.com/getkirby/kirby/pull/7774)
  <img width="543" height="463" alt="image" src="https://github.com/user-attachments/assets/aa54de2e-d011-44a6-9d0e-e2facd52accb" />
- New `<k-validation-issues>` component to list various issues in fields after a form has been submitted. This will be used in our new error dialogs, but can also be used as a stand-alone component in other parts of the panel. [#7775](https://github.com/getkirby/kirby/pull/7775)
  <img width="594" height="395" alt="image" src="https://github.com/user-attachments/assets/94122230-af87-4404-a5bd-dc896d892446" />
- New `<k-request-error-dialog>` component [#7782](https://github.com/getkirby/kirby/pull/7782)  
   <img width="686" height="829" alt="image" src="https://github.com/user-attachments/assets/20b390a4-feb1-4a1e-9fc4-c7cbd04ed3a5" />
- New `<k-validation-error-dialog>` to improve the readability of field validation problems. [#7785](https://github.com/getkirby/kirby/pull/7785)
  <img width="695" height="336" alt="image" src="https://github.com/user-attachments/assets/65404617-6957-4c6e-a30e-4fe1e2575786" />

### More

- New `picker` option for `files`, `pages` and `users` field to customize the picker dialogs (e.g. `size`, `layout`) [#7687](https://github.com/getkirby/kirby/pull/7687)

---

## ✨ Enhancements

### For editors

- Field labels get highlighted when field has unsaved changes [#7794](https://github.com/getkirby/kirby/pull/7794)
  <img width="679" height="360" alt="image" src="https://github.com/user-attachments/assets/644a3dcf-9e1b-4aad-a526-aadb25b5954e" />
  <img width="670" height="364" alt="image" src="https://github.com/user-attachments/assets/08fff2f9-c77b-4c57-bfec-2626df04ce29" />
- Values of disabled Panel fields can be selected [#7581](https://github.com/getkirby/kirby/pull/7581)
- Better drag sorting (e.g. pages/files sections) in list layouts [#7349](https://github.com/getkirby/kirby/issues/7349)
- Blocks field: `image` and `gallery` block previews now load properly sized thumbs that also respect the `cover` and `ratio` settings [#7756](https://github.com/getkirby/kirby/pull/7756)
- Pages field: picker dialog shows badge for selected children [#7687](https://github.com/getkirby/kirby/pull/7687)
- Files field: upload files by drag'n'drop onto the dialog https://feedback.getkirby.com/729 [#7888](https://github.com/getkirby/kirby/pull/7888)
- New Panel login UI/UX: the login view has been refactored to be more driven by the backend. It now features a method picker, a challenge switcher and centralized loading/error handling. [#7848](https://github.com/getkirby/kirby/pull/7848)
- TOTP management in one drawer: enable/disable TOTP in a single drawer with QR code, a clickable `otpauth://` setup-key link and password confirmation. [#7848](https://github.com/getkirby/kirby/pull/7848)

### For site developers

- Improved IDE autocompletion for field methods [#7082](https://github.com/getkirby/kirby/pull/7082)
- Panel only shows missing blueprint info when debug mode is active https://feedback.getkirby.com/392
- On installation, all required PHP extensions are checked and installation is blocked if one is missing [#7812](https://github.com/getkirby/kirby/pull/7812)
- Panel system view shows an alert if a required PHP extension is missing [#7812](https://github.com/getkirby/kirby/pull/7812)

### For plugin developers

#### Core

- Custom fields can now fully be implemented as PHP classes (base on `Kirby\Form\BaseField` or any other from the `Kirby\Form\` field classes). You can learn more about this in the "Refactored" section. Custom fields based on array definitions are still supported for now but deprecated.
- Hardened security of `Kirby\Session\Session` (thx [@XananasX7](https://github.com/XananasX7))

#### Frontend

- Migrated from Vue 2 to Vue 3 [#7104](https://github.com/getkirby/kirby/pull/7104)
- Dialogs and drawers: Added dropzone that becomes active when a `@drop` listener is added on the component [#7520](https://github.com/getkirby/kirby/pull/7520)
- New `<k-video-frame>` component [#7755](https://github.com/getkirby/kirby/pull/7755)
- The `<k-table>` component has a new `responsive` prop, which is set to `true` by default. Switching the responsive mode off will no longer hide columns that are not marked with `data-mobile="true"`. This gives more control over custom responsive behaviors for tables and also helps to improve the usability in narrow widths. [#7770](https://github.com/getkirby/kirby/pull/7770)
- `<k-item>` has a new `selected` prop to control it's selection checked state and `seelctmode` prop to define whether to select single or multiple items [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-collection>`/`<k-items>` can receive a list of `selected` items to control selection status from parent [#7516](https://github.com/getkirby/kirby/pull/7516)
- New `<k-models-picker-dialog>` components [#7529](https://github.com/getkirby/kirby/pull/7529)
- New `<k-collapsible>` component that can wrap a list of elements with a default and fallback slot and provide the necessary data how many elements fit in the current container width to render the element as a responsive list of visible and hidden elements (the latter represented by the fallback slot content). [#7901](https://github.com/getkirby/kirby/pull/7901)
- New `RequestError.dialog()` method to create all props for the request error dialog according to the details from the error object. [#7782](https://github.com/getkirby/kirby/pull/7782)
- All errors are now converted to Error objects in `panel.notification.error()` [#7782](https://github.com/getkirby/kirby/pull/7782)
- New `this.$panel.observers` JS module
- The Panel URL is available inside the Panel as `this.$panel.urls.panel` [#7800](https://github.com/getkirby/kirby/pull/7800)
- New Vue components for login forms: `<k-login-password-method-form>`, `<k-login-code-method-form>`, `<k-login-password-reset-method-form>`, `<k-login-email-challenge-form>`, `<k-login-totp-challenge-form>`
- New helper Vue components for the login view: `<k-login-back>`, `<k-login-code>`, `<k-login-footer>`, `<k-login-challenges>`, `<k-login-methods>`, `<k-login-remember>`, `<k-login-submit>`
- New `v-safe-html` Vue directive that only renders passed `HtmlString` objects as HTML and escapes any other passed string [#8176](https://github.com/getkirby/kirby/pull/8176). New `Kirby\Toolkit\HtmlString` class and `HtmlString` JS class that wrap and mark strings as safe HTML [#8175](https://github.com/getkirby/kirby/pull/8175). New `this.$html()` Vue helper to mark a string as safe HTML (wraps it in a `HtmlString` object) [#8175](https://github.com/getkirby/kirby/pull/8175)

#### Backend

- Sections can define their own dialogs and drawers on the backend now, as fields have already been able to [#7540](https://github.com/getkirby/kirby/pull/7540)
- New `items/files`, `items/pages` and `item/users` Panel requests endpoints to turn IDs into item data. This is supported by the new `Kirby\Panel\Controller\Request\FileItemsRequestController`, `Kirby\Panel\Controller\Request\PageItemsRequestController` and `Kirby\Panel\Controller\Request\UserItemsRequestController` classes [#7723](https://github.com/getkirby/kirby/pull/7723)
- New `$kirby->panel()` method to access `Kirby\Panel\Panel` object [#7409](https://github.com/getkirby/kirby/pull/7409)
- New `Kirby\Panel\Controller\Controller` classes that can be passed by their name as `action` argument of a Panel route (dialog, drawer, dropdown, request, search and view) and then handle the route action. This helps to move away from the anonymous closures in `config/areas/` and instead build handler classes that can be properly tested etc. [#7422](https://github.com/getkirby/kirby/pull/7422)
- New Panel UI classes:
  - `Kirby\Panel\Ui\Drawer`, `Kirby\Panel\Ui\Drawers\TextDrawer` and `Kirby\Panel\Ui\Drawers\FormDrawer`, `Kirby\Panel\Ui\Dialog`, `Kirby\Panel\Ui\Dialogs\ErrorDialog`, `Kirby\Panel\Ui\Dialogs\TextDialog`, `Kirby\Panel\Ui\Dialogs\RemoveDialog`, `Kirby\Panel\Ui\Dialogs\FormDialog`, `Kirby\Panel\Ui\View`, `Kirby\Panel\Ui\View\ErrorView` [#7441](https://github.com/getkirby/kirby/pull/7441) [#7443](https://github.com/getkirby/kirby/pull/7443) [#7468](https://github.com/getkirby/kirby/pull/7468)
  - New `Kirby\Panel\Ui\Item` class that also builds the foundation for `ModelItem` etc. New `Kirby\Panel\Ui\LanguageItem` class. [#7471](https://github.com/getkirby/kirby/pull/7471)
- Resolve a Panel UI component object in `JsonResponse::from()` but calling its `::render()` method. This allows us to return UI component instances from a Panel controller's `::load()` method. [#7442](https://github.com/getkirby/kirby/pull/7442)
- New `Kirby\Toolkit\Has18n` trait that adds a `::i18n()` helper method to the class which can be used to translate and/or template i18n strings. New `Kirby\Panel\Ui\Component::i18n()` helper method [#7406](https://github.com/getkirby/kirby/pull/7406) [#7465](https://github.com/getkirby/kirby/pull/7465)
- Exceptions are no longer intercepted by our Response classes but handled by our regular error handler. This will improve the JSON error responses drastically and expose more details in debug mode. One exception (pun intended) are NotFoundExceptions for views, which will still create a full error view with error message, to make sure that navigation to non-existing routes still works. [#7744](https://github.com/getkirby/kirby/pull/7744)
- New `Kirby\\Exception\\FormValidationException` [#7769](https://github.com/getkirby/kirby/pull/7769)
- Error responses from the backend now include the `editor` URL if the editor is set up in the config.php and debug mode is one. [#7782](https://github.com/getkirby/kirby/pull/7782)
- New protected `AppErrors::trace()` method to return a stack trace for PHP errors in JSON responses when debug mode is enabled. [#7782](https://github.com/getkirby/kirby/pull/7782)
- Auth methods can authenticate without an email, enabling token/SSO-style plugin methods that don't key on email. [#7848](https://github.com/getkirby/kirby/pull/7848)

### More

- New icons: `hashtag`, `email-unread`
- `this.$panel.t()` / `window.panel.t()` now returns the string key as a fallback if no matching i18n string exists and no actual fallback has been provided

---

## 🐛 Bug fixes

### Core

- Fixed auto-closing open snippets at the end of a nested snippet (thx to [@JojOatXGME](https://github.com/JojOatXGME)) [#7567](https://github.com/getkirby/kirby/issues/7567)
- `Kirby\Filesystem\Exif` now supports arrays for `ISOSpeedRatings` [#7569](https://github.com/getkirby/kirby/issues/7569)
- `Kirby\Form\Field\BaseField::stringTemplate()` now uses `ModelWithContent::toSafeString()` by default and introduces a new `$safe` argument, which can switch to the unsafe method. [#7687](https://github.com/getkirby/kirby/pull/7687)
- Login-failed hook is no longer fired twice. [#7848](https://github.com/getkirby/kirby/pull/7848)
- `Kirby\Text\KirbyTag::__set()` now lowercases the properties, as `::__get()` already did
- `Kirby\Toolkit\Str::date(null, null)` returns the current timestamp
- Fixed `Kirby\Toolkit\A::merge()` with empty input
- `Kirby\Image\Exif::parseTimestamp()` returns `null` for a missing timestamp now
- `Kirby\Filesystem\File::realpath()` returns `false` when the file does not exist at the path

### Panel

- Toggle field uses `<k-toggle-input>` instead `<k-input :type="toggle">` similarly to the radio and checkboxes field [#7489](https://github.com/getkirby/kirby/pull/7489)
- `<k-item>` only hides default options button when in selecting mode, but not custom used options slot [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>` is selectable by default unless explicitly defined otherwise [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>` selected to also work with UUIDs [#7751](https://github.com/getkirby/kirby/pull/7751)
- No badge for current changes on languages btn [#7749](https://github.com/getkirby/kirby/pull/7749)
- Important context data (path, referrer, query, code) is always added to the view response object again. This is important to make sure that the view URL for error views are correct. Otherwise, the frontend will create some weird side-effects, such as redirects to /panel/null. [#7744](https://github.com/getkirby/kirby/pull/7744)
- Panel dialog/drawer listeners get preserved when loading backend defined dialog/drawer [#7888](https://github.com/getkirby/kirby/pull/7888)
- Writer field: pasting text that contains an email address now turns it into an email link even when the address is surrounded by other text [#8110](https://github.com/getkirby/kirby/pull/8110)
- Writer field: when configured with not all headlines available (e.g. no `h1`), the field no longer automatically converts their markdown equivalents (e.g. it ignores `#`) [#8111](https://github.com/getkirby/kirby/pull/8111)

---

## 🚨 Breaking changes

### Core

#### General

- Removed support for PHP 8.2. Use PHP 8.3, 8.4 or 8.5 instead. [#7372](https://github.com/getkirby/kirby/pull/7372)
- Template data must not include variables named `$slot` or `$slots`. [#7599](https://github.com/getkirby/kirby/pull/7599)
- Custom validators cannot overwrite default validators from the `Kirby\Toolkit\V` class any longer. [#7674](https://github.com/getkirby/kirby/pull/7674)
- Removed `Kirby\Cms\Api` class. Use `Kirby\Api\Api` class instead. [#7532](https://github.com/getkirby/kirby/pull/7532)
- `Kirby\Data\Txt::encodeValue()` changes
  - `true` and `false` are now encoded into 'true' and 'false' instead of '1' and '0'
  - An empty array is now encoded as empty string instead of `[]`
- `Kirby\Cms\System::php()` has been removed [#7816](https://github.com/getkirby/kirby/pull/7816)

#### Configuration

- Changed the default YAML handler to Symfony YAML which sometimes enforces a stricter syntax than our previous Spyc handler. You can switch back to Spyc with the config option `'yaml.handler' => 'spyc'` [#7530](https://github.com/getkirby/kirby/pull/7530)

#### Helpers

- New global helper functions: `push()`, `endpush()` and `stack()`. [#7867](https://github.com/getkirby/kirby/pull/7867)

#### Field methods

- Calling non-existing field methods is throwing a `Kirby\Exception\BadMethodCallException`. [#7082](https://github.com/getkirby/kirby/pull/7082)
- Removed `Kirby\Content\Field::$aliases`, `Kirby\Cms\Core::fieldMethods()` and `Kirby\Cms\Core::fieldMethodsAliases()`. [#7082](https://github.com/getkirby/kirby/pull/7082)

#### UUIDs

- `Kirby\Uuid\Uuid::for()` does not resolve any permalinks anymore. Use `Kirby\Uuid\Permalink::from()` instead. [#7545](https://github.com/getkirby/kirby/pull/7545)
- `Kirby\Uuid\Uuid::for()` cannot be called any longer with a string. Use `Kirby\Uuid\Uuid::from(string $uuid)` or `Kirby\Uuid\Permalink::from(string $permalink)` instead. [#7544](https://github.com/getkirby/kirby/pull/7544)
- Removed deprecated `Uuid::url()`. Use `Uuid::toPermalink()` instead.

#### Forms

- The `Kirby\Form\Fields::validate()` and `Kirby\Form\Form::validate()` methods now throw the more specific `Kirby\Exception\FormValidationException`. [#7769](https://github.com/getkirby/kirby/pull/7769)
- All mixin props no longer define a default value. The default value is now defined by the getter when the property value is null.
- All setter Methods from field classes and field mixins have been removed. Set class properties directly instead.
- All translatable properties in `Kirby\Form\FieldClass` mixins are no longer translating the value in the setter but in the getter. This is a cleaner approach, where we store the property as untouched as possible and then compute any changes in the getter if needed. This way, we can always access the originally passed value for the property and we can also reduce the amount of computing that is done when the instance is created. The following props are affected: `after`, `before`, `empty`, `help`, `label`, `placeholder`
- Lower-casing of the name property of the FieldClass has been moved from the setter to the getter.
- `FieldClass::setSiblings()` is now public and siblings are injected after the construction. Setters can no longer rely on the siblings collection. Use getters or overwrite the `::setModel` method if necessary.
- `FieldClass::setModel()` is now public and the model is injected after the construction. Setters can no longer rely on the model. Use getters or overwrite the `::setModel` method if necessary.
- Field classes no longer define the value in the constructor. Use `FIeldClass::fill()` instead to provide an initial value.
- `NumberField::toNumber()` will now always return null instead of an empty string in case of empty values.
- `RangeField::toNumber()` will now always return null instead of an empty string in case of empty values.
- Several fields are now implemented as class. When extending an array-based field, either switch your field to a class as well or extend the deprecated `legacy-` field for the moment, e.g. `text` → `legacy-text` .
- The `checkboxes`, 'color', `multiselect`, `radio`, `select`, `tags` and `toggles` fields do no longer remove invalid values on submit or fill, but use the option validator to warn if a value is invalid. This is more in line with what other input fields do in Kirby and has massive performance benefits. It also means that you can deliberately store a non-existing option if you skip validation, which also might be useful in some cases.
- The `api` and `query` options for the `checkboxes`, 'color', `multiselect`, `radio`, `select`, `tags` and `toggles` fields are no longer available. Queries and API calls to fetch options have now to be declared directly in the `options` property, e.g.

```yaml
fields:
  myRadio:
    type: radio
    options:
      type: query
      query: some.query

# or

fields:
  myRadio:
    type: radio
    options:
      type: api
      url: /some/options/api
```

- The `spellcheck` in the text field attribute is no longer set by default.
- FieldClass based fields need to define API routes in `::api()`, not `::routes()`
- `files`, `pages` and `users` field:
  - Backend sends only UUIDs/IDs to these fields and the fields only send UUIDs/IDs back (instead of full item objects with display data etc.)
  - Same communication happens between the field and picker dialog

#### Blueprints

- Removed property: `Kirby\Blueprint\Blueprint::$fileTemplates` [#7829](https://github.com/getkirby/kirby/pull/7829)
- Removed protected `Kirby\Blueprint\Blueprint` methods: [#7829](https://github.com/getkirby/kirby/pull/7829)
  - `::acceptedFileTemplatesFromFields()`
  - `::acceptedFileTemplatesFromFieldsets()`
  - `::acceptedFileTemplatesFromFieldUploads()`

### Panel

#### Configuration

- `panel.favicon` option: Use `href` instead of `url` attribute. Use `rel` attribute instead of passing string as key.

#### Blueprints

- Color field options `text => value` notation has been removed. Please rewrite your options as `value => text`. [#7534](https://github.com/getkirby/kirby/pull/7534)

#### Frontend

- All plugins that have compiled their Vue Single File Components with Vue 2 have to recompile their SFC for Vue 3 to work with Kirby 6. Consider also the [Vue 3 migration guide](https://v3-migration.vuejs.org/breaking-changes/).
- All plugin JS files get loaded as module.
- Renamed components
  - `<k-dropdown-content>` has been renamed to `<k-dropdown>`. The `align` prop has been removed. Use `align-x` instead. [#7535](https://github.com/getkirby/kirby/pull/7535)
  - `<k-fiber-dialog>` is now `<k-state-dialog>`
  - `<k-fiber-drawer>` is now `<k-state-drawer>`
- Removed components
  - Deprecated `<k-bubble>`, `<k-bubbles>` and `<k-bubbles-field-preview>` components have been removed. Use `<k-tag>`, `<k-tags>` and `<k-tags-field-preview>` instead. [#7533](https://github.com/getkirby/kirby/pull/7533)
  - Removed deprecated `<k-settings-view-button>` and `<k-status-view-button>`.
- Changes in components
  - Radio input does not support `reset` option anymore. User toggles input instead. [#7385](https://github.com/getkirby/kirby/pull/7385)
  - `<k-panel-menu>` now requires to pass props explicitly instead of using `$panel.menu` itself. [#7381](https://github.com/getkirby/kirby/pull/7381)
  - Removed deprecated `model` prop from model views. Use the top-level props instead. [#7463](https://github.com/getkirby/kirby/pull/7463)
  - `<k-collection>` and `<k-items>`: `@select` events now passes an array of selected IDs, not a single item object [#7516](https://github.com/getkirby/kirby/pull/7516)
    - `<k-structure-drawer>`: `prev`/`next` need a boolean as value, not an object anymore [#7790](https://github.com/getkirby/kirby/pull/7790)
- View changes - Preview view: `versionId` parameter has been renamed to `mode` in view controller, buttons and Vue components [#7795](https://github.com/getkirby/kirby/pull/7795) - `k-login-view` and components have been refactored with breaking changes [#7840](http://github.com/getkirby/kirby/pull/7840)
- Request changes
  - We are getting rid of the term “Fiber” for backend requests.
  - The `window.fiber` global has been replace with `window.panelState`
  - The `X-Fiber` namespace in request headers has been replaced with `X-Panel`
  - Keys aren't prefixed with `$` anymore in request responses. Use without prefix. This also affects Panel plugins reloading the Panel by defining only specific keys to be reloaded. [#7365](https://github.com/getkirby/kirby/pull/7365)
- Helpers
  - Removed `$helper.isVueComponent()` JS helper [#7518](https://github.com/getkirby/kirby/pull/7518)
  - Removed `$helper.link.preview()` helper. Use `items/*` request endpoints instead. [#7725](https://github.com/getkirby/kirby/pull/7725)
- Removed deprecated `panel.dialog.openComponent()` method [#7518](https://github.com/getkirby/kirby/pull/7518)
- `panel.notification.error()` no longer resolves nested errors in the state. Always throw Exceptions instead to create first-level error responses. [#7782](https://github.com/getkirby/kirby/pull/7782)
- `image`, `gallery` and `video` block previews receive file UUID instead of full file item data
- Removed deprecated v3 CSS properties [#7825](https://github.com/getkirby/kirby/pull/7825)

#### Backend

- Changed Classes - `Kirby\Panel\Controller\Dialog\PageCreateDialogController` has been fully refactored and now is initiated with a parent model (page/site) as well as a section (name) [#7466](https://github.com/getkirby/kirby/pull/7466)
- Moved Classes
  - `Kirby\Panel\ChangesDialog` → `Kirby\Panel\Ui\Dialogs\ChangesDialog`
  - `Kirby\Panel\PageCreateDialog` → `Kirby\Panel\Ui\Dialogs\PageCreateDialog`
  - `Kirby\Panel\UserTotpEnableDialog.` → `Kirby\Panel\Ui\Dialogs\UserTotpEnableDialog`
  - `Kirby\Panel\UserTotpDisableDialog` → `Kirby\Panel\Ui\Dialogs\UserTotpDisableDialog`
  - `Kirby\Panel\Home::isPanelUrl()` → `Kirby\Panel\Panel::isPanelUrl()` [#7394](https://github.com/getkirby/kirby/pull/7394)
  - `Kirby\Panel\Home::panelPath()` → `Kirby\Panel\Panel::path()` [#7394](https://github.com/getkirby/kirby/pull/7394)
  - `Kirby\Panel\Panel::area()` → `Kirby\Panel\Areas::area()` [#7391](https://github.com/getkirby/kirby/pull/7391)
  - `Kirby\Panel\Panel::buttons()` → `Kirby\Panel\Areas::buttons()` [#7391](https://github.com/getkirby/kirby/pull/7391)
  - `Kirby\Panel\Panel::firewall()` & `Kirby\Panel\Panel::hasAccess()` → `Kirby\Panel\Panel::access()->area()` [#7383](https://github.com/getkirby/kirby/pull/7383)
  - `Kirby\Panel\Panel::isFiberRequest()` → `Kirby\Panel\Panel::isStateRequest()`
  - `Kirby\Panel\Ui\Buttons\LanguagesDropdown` → `Kirby\Panel\Ui\Buttons\LanguagesButton` [#7427](https://github.com/getkirby/kirby/pull/7427)
  - `Kirby\Panel\Ui\Buttons` → `Kirby\Panel\Ui\Button` and all its classes [#7459](https://github.com/getkirby/kirby/pull/7459)
  - `Kirby\Panel\Ui\FilePreviews` → `Kirby\Panel\Ui\FilePreview` and all its classes [#7459](https://github.com/getkirby/kirby/pull/7459)
- Removed Classes
  - `Kirby\Panel\View` and `Kirby\Panel\Document` → `Kirby\Panel\Response\ViewResponse` and `Kirby\Panel\Response\ViewDocumentResponse` take over most of their functionality. [#7407](https://github.com/getkirby/kirby/pull/7407#pullrequestreview-3038636556)
  - `Kirby\Panel\Controller\Search` → `Kirby\Panel\Controller\Search` classes [#7423](https://github.com/getkirby/kirby/pull/7423)
  - `Kirby\Panel\Controller\PageTree` → `Kirby\Panel\Controller\Request\PageTreeRequestController` or `Kirby\Panel\Controller\Request\PageTreeParentsRequestController` [#7437](https://github.com/getkirby/kirby/pull/7437/)
  - `Kirby\Panel\Ui\Dialogs\ChangeDialog` → `Kirby\Panel\Controller\Dialog\ChangesDialogController` [#7444](https://github.com/getkirby/kirby/pull/7444)
  - `Kirby\Panel\Ui\Dialogs\UserTotpEnableDialog` → `Kirby\Panel\Controller\Dialog\UserTotpEnableDialogController` [#7445](https://github.com/getkirby/kirby/pull/7445)
  - `Kirby\Panel\Ui\Dialogs\UserTotpDisableDialog` → `Kirby\Panel\Controller\Dialog\UserTotpDisableDialogController` [#7445](https://github.com/getkirby/kirby/pull/7445)
  - `Kirby\Panel\Model::isDisabledDropdownOption()` [#7425](https://github.com/getkirby/kirby/pull/7425)
  - `Kirby\Panel\Field::dialog()` → `Kirby\Panel\Controller\Dialog\FieldDialogController` [#7454](https://github.com/getkirby/kirby/pull/7454)
  - `Kirby\Panel\Field::drawer()` → `Kirby\Panel\Controller\Drawer\FieldDrawerController` [#7454](https://github.com/getkirby/kirby/pull/7454)
  - `Kirby\Panel\Ui\Dialogs\PageCreateDialog` → `Kirby\Panel\Controller\Dialog\PageCreateDialogController` [#7446](https://github.com/getkirby/kirby/pull/7446)
- Removed Methods
  - Removed `::option()` and `::options()` from `Kirby\Panel\Ui\Buttons\LanguagesButton` → `Kirby\Panel\Controller\Dropdown\LanguagesDropdownController` [#7427](https://github.com/getkirby/kirby/pull/7427)
  - Removed `:: toPrevNextLink()` and deprecated `::content()` method from `Kirby\Panel\Model`, `Kirby\Panel\Site`, `Kirby\Panel\Page`, `Kirby\Panel\File` and `Kirby\Panel\User`. [#7480](https://github.com/getkirby/kirby/pull/7480)
- Changed Methods
  - The argument for the `Kirby\Panel\Ui\Buttons\ViewButtons` constructor has changed. [#7462](https://github.com/getkirby/kirby/pull/7462)
  - `Kirby\Panel\Ui\Button\ViewButtons::view()` now accepts a `Kirby\Panel\Controller\View\ModelViewController` as `$view` argument, instead of a `Kirby\Panel\Model`. [#7480](https://github.com/getkirby/kirby/pull/7480)
  - Files field: `fileResponse` and `toFiles` methods have been removed. Use `toItem` and `toFormValues` instead. [#7528](https://github.com/getkirby/kirby/pull/7528)
  - Pages field: `pageResponse` and `toFiles` methods have been removed. Use `toItem` and `toFormValues` instead. [#7528](https://github.com/getkirby/kirby/pull/7528)
  - Users field: `userResponse` and `toFiles` methods have been removed. Use `toItem` and `toFormValues` instead. [#7528](https://github.com/getkirby/kirby/pull/7528)
  - `$versionId` parameter for `Kirby\Panel\Ui\Button\VersionsButton::__construct()` has been renamed to `$mode`. [#7548](https://github.com/getkirby/kirby/pull/7548)
- Restructured `Kirby\Panel` namespace: [#7386](https://github.com/getkirby/kirby/pull/7386)
  - `Kirby\Panel\Panel` class: removed`::routesForViews()`, `::routesForSearches()`, `::routesForDialogs()`, `::routesForDrawers()`, `::routesForDropdowns()`, `::routesForRequests()`. Use instead: `Kirby\Panel\Routes\DialogRoutes`, `Kirby\Panel\Routes\DrawerRoutes`, `Kirby\Panel\Routes\DropdownRoutes`, `Kirby\Panel\Routes\RequestRoutes`, `Kirby\Panel\Routes\SearchRoutes`, `Kirby\Panel\Routes\ViewRoutes`.
  - For reponses, use `Kirby\Panel\Responses\DialogResponse`, `Kirby\Panel\ Responses\DrawerResponse`, `Kirby\Panel\ Responses\DropdownResponse`, `Kirby\Panel\Responses\RequestResponse`, `Kirby\Panel\ Responses\SearchResponse`.
  - Removed `Kirby\Panel\Dialog`, `Kirby\Panel\Drawer`, `Kirby\Panel\Dropdown`, `Kirby\Panel\Json`, `Kirby\Panel\Request`
  - Removed methods from `Kirby\Panel\View`: `::apply()`, `::applyGlobals()`, `::applyOnly()`, `::data()`, `::globals()`, `::searches()`. Use `Kirby\Panel\State` instead.
  - `Document::response()` expects a `State` object as argument
  - Converted methods in `Kirby\Panel\Panel` from static to non-static [#7409](https://github.com/getkirby/kirby/pull/7409)
  - `Kirby\Panel\Home` and its methods are non-static [#7394](https://github.com/getkirby/kirby/pull/7394)
  - Rewrote `Kirby\Panel\Menu` class [#7406](https://github.com/getkirby/kirby/pull/7406)
    - Using `Areas` and `Area` object throughout the Panel classes instead of arrays [#7406](https://github.com/getkirby/kirby/pull/7406)
  - `Kirby\Panel\Panel::areas()` returns `Kirby\Panel\Areas` object instead of an array. [#7391](https://github.com/getkirby/kirby/pull/7391)
  - Non-static `Kirby\Panel\Panel::router()`. Instead of `Panel::router($path)` use `$panel0>router()?->call($path)` [#7407](https://github.com/getkirby/kirby/pull/7407)
  - `Kirby\Panel\Areas` is a collection of `Kirby\Panel\Area` objects [#7406](https://github.com/getkirby/kirby/pull/7406)
- Panel Routes
  - Exception is thrown when trying to register field dialogs or drawers without a closure [#7512](https://github.com/getkirby/kirby/pull/7512)

### Auth

- Custom auth challenges extending `Kirby\Cms\Auth\Challenge` have to be adapted to use `Kirby\Auth\Challenge` [#8044](https://github.com/getkirby/kirby/pull/8044)
- Auth challenge classes have been redesigned. Existing custom auth challenges must be rewritten and adopt the new `Kirby\Auth\Challenge` base class.
- Everywhere `Kirby\Cms\Auth\Status` was returned or expected as a parameter, `Kirby\Auth\Status` is now used.
- `Kirby\Auth\Status`: `$auth->status()` returns `Kirby\Auth\Status`; `is(State)` and `clone()` have been removed.
- Auth session format changed: `kirby.challenge.code` → `kirby.challenge.data`. In-flight challenges across the Kirby upgrade won't verify.
- `::validatePassword()` returns `User|null`
- `::verifyChallenge(mixed $input)` returns `User|null`
- `User::changeTotp()` renamed to `User::changeSecret('totp', ...)`
- `Kirby\Cms\Find::user()` throws `UserNotFoundException`
- We removed the translation strings `login.code.label.login` and `login.code.label.password-reset`.
- `panel.plugin()`: the `login` extension type has been removed. Instead, register your custom login UI as a normal `component` and reference it in your custom auth `Method` or `Challenge` class in the `::form()` method. [#8045](https://github.com/getkirby/kirby/pull/8045)
- Panel: TOTP enable/disable dialogs removed (`UserTotpEnableDialogController`, `UserTotpDisableDialogController` and `<k-totp-dialog>`). Use `UserTotpDrawerController` and `<k-user-totp-drawer>` instead.
- Panel login route shape changed to multi-step `login/method/...` and `login/challenge/...`
- Panel components removed/renamed: `<k-login-form>`, `<k-login-code-form>` and the deprecated aliases `<k-login>` / `<k-login-code>` removed. [#8045](https://github.com/getkirby/kirby/pull/8045)
- `panel.plugins.login` extension point removed. Plugins that overrode the whole login form via this hook break and should provide a custom auth method (incl. `::form()`) instead.

### Other core changes

- Turned required methods for `Kirby\Query\Visitor\Visitor` into abstract methods that get enforced when extending the `Visitor` class
- `Kirby\Http\VolatileHeaders::append()` requires an array as third parameter
- `qr()` helper only accepts a site, page or file object or a URL string as parameter.
- `Kirby\Cms\Fieldset::name()` falls back to a labelized type
- `Kirby\Cms\Fields` extends `Kirby\Cms\Collection`, not `Kirby\Toolkit\Collection`
- `Kirby\Cms\HasSiblings` relies on `Kirby\Cms\Collection`, not `Kirby\Toolkit\Collection`
- `Kirby\Form\Field` gained a new `::id()` method, overriding `id` properties/methods from array notations.
- Added abstract `Kirby\Cms\ModelWithContent::apiUrl()` method
- `Kirby\Form\Field\BaseField` now implements `Stringable`
- `Kirby\Cms\App::models()` and `Kirby\Uuid\Uuid::index()` generators are now keyed by id
- `Kirby\Cms\LazyCollection::getIterator()` skips items that failed hydration
- `Kirby\Session\Session` will not serialize objects anymore

#### Changed return types

|                                      | Before      | Now                |
| ------------------------------------ | ----------- | ------------------ |
| `Kirby\Http\Request::domain()`       | string      | string\|null       |
| `Kirby\Image\Exif::parseTimestamp()` | string      | string\|null       |
| `Kirby\Cms\Site::modified()`         | int\|string | int\|string\|false |
| `Kirby\Filesystem\Dir::modified()`   | int\|string | int\|string\|false |
| `Kirby\Filesystem\File::realpath()`  | string      | string\|false      |
| `Kirby\Toolkit\V::value()`           | bool\|array | true\|array        |

#### Methods that newly throw

|                                        | Before          | Now                                                             |
| -------------------------------------- | --------------- | --------------------------------------------------------------- |
| `Kirby\Toolkit\Dom::query()`           | returns `false` | throws if query invalid                                         |
| `Kirby\Cache\Value::toJson()`          |                 | throws for invalid JSON                                         |
| `Kirby\Data\Json::encode()`            |                 | throws for invalid JSON                                         |
| `Kirby\Filesystem\File::toJson()`      |                 | throws for invalid JSON                                         |
| `Kirby\Http\Uri::toJson()`             |                 | throws for invalid JSON                                         |
| `Kirby\Toolkit\Collection::toJson()`   |                 | throws for invalid JSON                                         |
| `Kirby\Toolkit\Obj::toJson()`          |                 | throws for invalid JSON                                         |
| `Kirby\Filesystem\File::sha1()`        | returns `false` | throws on missing file                                          |
| `Kirby\Content\Version::contentFile()` |                 | throws when called with a storage other than `PlainTextStorage` |

---

## ☠️ Deprecated

### Core

- The Spyc YAML handler has been deprecated and will be removed in a future release. [#7530](https://github.com/getkirby/kirby/pull/7530)
- `legacy` query runner has been deprecated and removed as default. Use `default` (`Kirby\Query\Runners\DefaultRunner`) instead. [#7791](https://github.com/getkirby/kirby/pull/7791)
- `Kirby\Query\Argument`, `Kirby\Query\Arguments`, `Kirby\Query\Expression`, `Kirby\Query\Segment`, `Kirby\Query\Segments` have been deprecated and will be removed. [#7791](https://github.com/getkirby/kirby/pull/7791)
- Moved `Kirby\Cms\Blueprint`, `Kirby\Cms\FileBlueprint`, `Kirby\Cms\PageBlueprint`, `Kirby\Cms\Section`, `Kirby\Cms\SiteBlueprint` and `Kirby\Cms\UserBlueprint` to new `Kirby\Blueprint` namespace. Aliases for the old names have been added but are deprecated and will be removed in a future major version. [#7787](https://github.com/getkirby/kirby/pull/7787)
- `Kirby\Cms\Auth::ipHash()` has been removed. Use `$visitor->ip(hash: true)` instead.
- `Kirby\Cms\Auth` → use `Kirby\Auth\Auth` (kept working via alias).
- `Kirby\Cms\Auth\Status` → use `Kirby\Auth\Status` (old class kept as a stub).
- `Kirby\Cms\Auth::login2fa()` → use `Kirby\Auth\Auth::authenticate()`.
- `Kirby\Cms\Auth::enabledChallenges()` → `Kirby\Auth\Auth::challenges()->enabled()`.
- `Kirby\Cms\Auth::isBlocked()` / `::log()` / `::logfile()` / `::track()` → the equivalents on `Kirby\Auth\Auth::limits()`, e.g. `$auth->limits()->isBlocked()`.
- `Kirby\Cms\System::loginMethods()` → `$kirby->auth()->methods()->enabled()`
- `Kirby\Cms\UserActions::changeTotp()` / `Kirby\Cms\UserRules::changeTotp()` → `Kirby\Cms\changeSecret('totp', …)`.
- `Kirby\Session\Sessions::cookieDomain()` and `::cookieName()`: use `Sessions::cookie()->domain()` and `::cookie()->name()` instead. [#8230](https://github.com/getkirby/kirby/pull/8230)

### Panel

#### Frontend

- `<k-models-dialog>`, `<k-pages-dialog>`, `<k-files-dialog>` and `<k-users-dialog>` have been deprecated. Use `<k-model-picker-dialog>`, `<k-page-picker-dialog>`, `<k-file-picker-dialog>`, `<k-user-picker-dialog>` instead.

#### Backend

- All `legacy-*` fields will be removed in an upcoming major version. Please move to class-based fields instead and extend the respective field class.
- `Kirby\Panel\Model`, `Kirby\Panel\Site`, `Kirby\Panel\Page`, `Kirby\Panel\File` and `Kirby\Panel\User`:
  - `::dropdown()` methods have been deprecated. Use the respective `Kirby\Panel\Controller\DropdownController` instead. [#7425](https://github.com/getkirby/kirby/pull/7425)
  - `::breadcrumb()`, `::buttons()`, `::prevNext()`, `::props()`, `::versions()` and `::view()` methods have been deprecated. Use the respective `Kirby\Panel\Controller\View` classes instead. [#7480](https://github.com/getkirby/kirby/pull/7480)
- The global `page/create` dialog endpoint has been deprecated. Use the specific dialog endpoint of a pages section instead. [#7466](https://github.com/getkirby/kirby/pull/7466)
- `Kirby\Field\FieldOptions` is a deprecated alias for `Kirby\Form\FieldOptions`. Use `Kirby\Form\FieldOptions`instead.
- `Kirby\Cms\Picker`, `Kirby\Cms\PagePicker`, `Kirby\Cms\FilePicker` and `Kirby\Cms\UserPicker` have been deprecated. Use `Kirby\Panel\Controller\Dialog\ModelPickerDialogController`, `Kirby\Panel\Controller\Dialog\PagePickerDialogController`, `Kirby\Panel\Controller\Dialog\FilePickerDialogController` and `Kirby\Panel\Controller\Dialog\UserPickerDialogController` instead.

---

## ♻️ Refactored

### Core

- Moved default field methods into new `Kirby\Content\FieldMethods` trait use by `Kirby\Content\Field` [#7082](https://github.com/getkirby/kirby/pull/7082)
- Implemented default validators as regular class methods of `Kirby\Toolkit\V` [#7608](https://github.com/getkirby/kirby/pull/7608)
- Use `json_validate` for `V::json()` [#7538](https://github.com/getkirby/kirby/pull/7538)
- Moved permalink logic to new `Kirby\Uuid\Permalink` class [#7545](https://github.com/getkirby/kirby/pull/7545)
- New `Kirby\Uuid\Uuid::from(string $uuid)` method for creating an Uuid object from a UUID string. `Kirby\Uuid\Uuid::for()` remains to create a Uuid object for a model object. [#7544](https://github.com/getkirby/kirby/pull/7544)
- New `Kirby\Blueprint\AcceptRules` class to host the code from the Blueprint class, which is specifically checking for accepted files. This could later be extended to also check for accepted subpages. [#7829](https://github.com/getkirby/kirby/pull/7829)
- New `Kirby\Blueprint\Blueprint::acceptRules()` method. [#7829](https://github.com/getkirby/kirby/pull/7829)
- PHP type hints have been added to all class constants [#7536](https://github.com/getkirby/kirby/pull/7536)

### Forms

Form fields have been fully refactored as PHP classes instead of the previous array definitions:

- All fields are now concrete PHP classes built on shared abstract base classes, e.g. `Kirby\Form\BaseField`
- Using named props instead of `$props` arrays in all field classes
  - New `FieldClass::factory()` method, which is used in fields to create instances from `$props` array
- `Kirby\Form\Field` now also accepts classes extending `Kirby\Form\BaseClass` in the factory method.
- `Kirby\Form\FieldClass` extends `Kirby\Form\Field\BaseClass` itself
- Removed `default` from field props passed to Panel [#7789](https://github.com/getkirby/kirby/pull/7789)
- Moved `Kirby\Field\FieldOptions` class to `Kirby\Form\FieldOptions`
- The slug field does now get the 'slug' translation string as default label [#7846](https://github.com/getkirby/kirby/pull/7846)

#### New field Foundation classes

All form fields can extends a small set of abstract base classes:

- `Kirby\Form\Field\BaseField` as general foundation for all fields.
- `Kirby\Form\Field\DateTimeField` for all date and time fields.
- `Kirby\Form\Field\InputField` for all fields with a value.
- `Kirby\Form\Field\OptionField` for fields with a single option value.
- `Kirby\Form\Field\OptionsField` for fields with multiple options value.
- `Kirby\Form\Field\StringField` for text, textarea and potentially more string value fields.
- `Kirby\Form\Field\ModelPickerField` for all picker fields.

#### New field classes

All form fields are now real classes instead of array definitions:

`Kirby\Form\Field\CheckboxesField`, `Kirby\Form\Field\ColorField`, `Kirby\Form\Field\DateField`, `Kirby\Form\Field\EmailField`, `Kirby\Form\Field\FilePickerField`, `Kirby\Form\Field\GapField`, `Kirby\Form\Field\HeadlineField`, `Kirby\Form\Field\HiddenField`, `Kirby\Form\Field\InfoField`, `Kirby\Form\Field\LineField`, `Kirby\Form\Field\LinkField`, `Kirby\Form\Field\ListField`, `Kirby\Form\Field\MultiselectField`, `Kirby\Form\Field\NumberField`, `Kirby\Form\Field\ObjectField`, `Kirby\Form\Field\PagePickerField`, `Kirby\Form\Field\PasswordField`, `Kirby\Form\Field\RadioField`, `Kirby\Form\Field\RangeField`, `Kirby\Form\Field\SelectField`, `Kirby\Form\Field\SlugField`, `Kirby\Form\Field\StuctureField`, `Kirby\Form\Field\TagsField`, `Kirby\Form\Field\TelField`, `Kirby\Form\Field\TextField`, `Kirby\Form\Field\TextareaField`, `Kirby\Form\Field\TimeField`, `Kirby\Form\Field\ToggleField`, `Kirby\Form\Field\TogglesField`, `Kirby\Form\Field\UrlField`, `Kirby\Form\Field\UserPickerField` and `Kirby\Form\Field\WriterField`

### New field mixins

Shared behavior has been extracted into reusable mixins, such as:

`Kirby\Form\Mixin\Autocomplete`, `Kirby\Form\Mixin\Batch`, `Kirby\Form\Mixin\Columns`, `Kirby\Form\Mixin\Converter`, `Kirby\Form\Mixin\Counter`, `Kirby\Form\Mixin\DefaultValue`, `Kirby\Form\Mixin\Disabled`, `Kirby\Form\Mixin\Font`, `Kirby\Form\Mixin\Layout`, `Kirby\Form\Mixin\Maxlength`, `Kirby\Form\Mixin\Minlength`, `Kirby\Form\Mixin\Name`, `Kirby\Form\Mixin\Pattern`, `Kirby\Form\Mixin\Pretty`, `Kirby\Form\Mixin\Required`, `Kirby\Form\Mixin\Separator`, `Kirby\Form\Mixin\Siblings`, `Kirby\Form\Mixin\Spellcheck`, `Kirby\Form\Mixin\Sortable`, `Kirby\Form\Mixin\Text`, `Kirby\Form\Mixin\Theme`, `Kirby\Form\Mixin\Duplicate`, `Kirby\Form\Mixin\Fields`, `Kirby\Form\Mixin\Limit`, `Kirby\Form\Mixin\Options`, `Kirby\Form\Mixin\Prepend`, `Kirby\Form\Mixin\SortBy`, `Kirby\Form\Mixin\TableColumns` and `Kirby\Form\Mixin\Upload`

### Panel

#### Frontend

- Files, pages and users field previews now support IDs alongside item objects as value and will fetch the item data for these IDs automatically. [#7723](https://github.com/getkirby/kirby/pull/7723)
- `k-image-frame` can receive a file ID/UUID via new `file` prop [#7756](https://github.com/getkirby/kirby/pull/7756)
- Removed the `panel.vue.compiler` option. It isn't needed with Vue 3 anymore. [#7788](https://github.com/getkirby/kirby/pull/7788)
- New `k-panel-notifications` [#7797](https://github.com/getkirby/kirby/pull/7797)
- `k-button` apply all `aria-` attributes [#7801](https://github.com/getkirby/kirby/pull/7801)
- DOM structure of `k-item` has been changes. An additional wrapper `.k-item-box` div was added. [#7361](https://github.com/getkirby/kirby/pull/7361)
- New k-login-back [#7840](https://github.com/getkirby/kirby/pull/7840)
- Remove `light-dark()` polyfill [#7902](https://github.com/getkirby/kirby/pull/7902)
- `<k-breadcrumb>` has a new responsive behavior (backend by `<k-collapsible>`) [#7901](https://github.com/getkirby/kirby/pull/7901)
- `files`, `pages` and `users` fields use new picker dialogs
- `files`, `pages` and `users` fields dynamically fetch item data from new `items` field API endpoint

#### Backend

- Refactored the Panel namespace as non-static classes [#7386](https://github.com/getkirby/kirby/pull/7386) [#7394](https://github.com/getkirby/kirby/pull/7394) [#7394](https://github.com/getkirby/kirby/pull/7394)
  - New classes: `Kirby\Panel\Areas` , `Kirby\Panel\Area`, `Kirby\Pane\Access`, `Kirby\Panel\Router` class [#7391](https://github.com/getkirby/kirby/pull/7391) [#7406](https://github.com/getkirby/kirby/pull/7406) [#7383](https://github.com/getkirby/kirby/pull/7383) [#7407](https://github.com/getkirby/kirby/pull/7407)
- Refactored all Panel routes to be powered by controller classes (`Kirby\Panel\Controller`):
  - Refactored all Panel dialogs, drawers, drop-down, requests and views (from `kirby/config/areas`) as controllers [#7480](https://github.com/getkirby/kirby/pull/7480) [#7482](https://github.com/getkirby/kirby/pull/7482) [#7475](https://github.com/getkirby/kirby/pull/7475) [#7474](https://github.com/getkirby/kirby/pull/7474) [#7471](https://github.com/getkirby/kirby/pull/7471) [#7469](https://github.com/getkirby/kirby/pull/7469) [#7479](https://github.com/getkirby/kirby/pull/7479) [#7439](https://github.com/getkirby/kirby/pull/7439) [#7455](https://github.com/getkirby/kirby/pull/7455) [#7453](https://github.com/getkirby/kirby/pull/7453) [#7451](https://github.com/getkirby/kirby/pull/7451) [#7440](https://github.com/getkirby/kirby/pull/7440) [#7448](https://github.com/getkirby/kirby/pull/7448) [#7452](https://github.com/getkirby/kirby/pull/7452) [#7425](https://github.com/getkirby/kirby/pull/7425) [#7427](https://github.com/getkirby/kirby/pull/7427) [#7437](https://github.com/getkirby/kirby/pull/7437/)
  - Refactored existing classes as Panel controller classes:
    - Refactored field dialogs and drawers as dialog controller and drawer controller (using a shared `FieldController` trait) [#7454](https://github.com/getkirby/kirby/pull/7454)
- Aligned the namespace names within `Kirby\Panel\Ui` [#7459](https://github.com/getkirby/kirby/pull/7459)
  - `Kirby\Panel\Ui\Buttons\ViewButtons` now supports passing an array of `Kirby\Panel\Ui\Buttons\ViewButton` objects [#7462](https://github.com/getkirby/kirby/pull/7462)
  - `Panel\Ui\Stat` optional `$model` property [#7420](https://github.com/getkirby/kirby/pull/7420)
- Use `ModelItem` classes for picker dialogs [#7753](https://github.com/getkirby/kirby/pull/7753)
- New `create` dialog endpoint for pages sections [#7466](https://github.com/getkirby/kirby/pull/7466)
- The `Kirby\Panel\Field` class uses the new classes and improvements above to replace its logic. [#7846](https://github.com/getkirby/kirby/pull/7846)

##### New special Panel field classes

- New `Kirby\Panel\Form\Field\FilePositionField`, `Kirby\Panel\Form\Field\PagePositionField`, `Kirby\Panel\Form\Field\RoleField`, `Kirby\Panel\Form\Field\TemplateField`, `Kirby\Panel\Form\Field\TitleField` and `Kirby\Panel\Form\Field\TranslationField` classes [#7846](https://github.com/getkirby/kirby/pull/7846)

### Tests

- Use stubs instead of mocks in tests [#7804](https://github.com/getkirby/kirby/pull/7804)
- Better locale set/reset [#7805](https://github.com/getkirby/kirby/pull/7805)

### TypeScript migration

- Migrated `this.$panel` and all its modules to TypeScript [#8115](https://github.com/getkirby/kirby/pull/8115) [#8056](https://github.com/getkirby/kirby/pull/8056) [#8076](https://github.com/getkirby/kirby/pull/8076) [#8079](https://github.com/getkirby/kirby/pull/8079)
- Refactored the Panel `api` JavaScript as a class in TypeScript [#8075](https://github.com/getkirby/kirby/pull/8075) [#8121](https://github.com/getkirby/kirby/pull/8121)
- Migrated `preserveDataAttrs` and `preserveListeners` to TypeScript [#8118](https://github.com/getkirby/kirby/pull/8118)
- Migrated the Editor to TypeScript [#8111](https://github.com/getkirby/kirby/pull/8111) [#8112](https://github.com/getkirby/kirby/pull/8112) [#8101](https://github.com/getkirby/kirby/pull/8101) [#8102](https://github.com/getkirby/kirby/pull/8102) [#8105](https://github.com/getkirby/kirby/pull/8105) [#8106](https://github.com/getkirby/kirby/pull/8106) [#8107](https://github.com/getkirby/kirby/pull/8107) [#8108](https://github.com/getkirby/kirby/pull/8108) [#8110](https://github.com/getkirby/kirby/pull/8110) [#8114](https://github.com/getkirby/kirby/pull/8114)
- `panel.system.csrf` and `panel.csrf.title` are always strings now (empty strings when not set) [#8119](https://github.com/getkirby/kirby/pull/8119)

### Auth

- `Kirby\Cms\Auth` class split into the new `Kirby\Auth` namespace:
  - New classes: `Kirby\Auth\Limits`, `Kirby\Auth\Csrf`, `Kirby\Auth\Methods` and individual `Kirby\Auth\Method` classes, `Kirby\Auth\User`, `Kirby\Auth\Challenges` and individual `Kirby\Auth\Challenge` classes, `Kirby\Auth\Pending`, `Kirby\Auth\Status` class and `Kirby\Auth\State` enum
  - New exceptions: `Kirby\Auth\Exception\RateLimitException`, `Kirby\Auth\Exception\LoginNotPermittedException`, `Kirby\Exception\UserNotFoundException` and `Kirby\Auth\Exception\ChallengeTimeoutException`
- Auth challenges have been changed from static classes to instance-based objects
- Panel login decomposed from two fixed forms into small composable components and a thin `LoginView.vue` shell driven by the backend.
- New `Kirby\Cms\User::changeSecret()` / `Kirby\Cms\UserRules::changeSecret()` generalise the old TOTP-specific secret writing.
- New Panel routes: `/login/(type)/(name)`, e.g. `/login/method/password` [#8045](https://github.com/getkirby/kirby/pull/8045)

### Session

- New `Kirby\Session\Token` class [#8228](https://github.com/getkirby/kirby/pull/8228)
- Removed `Session::__call()` and replaced it with non-magic methods [#8229](https://github.com/getkirby/kirby/pull/8229)
- New `Kirby\Session\Cookie` and `Kirby\Session\Header` classes [#8230](https://github.com/getkirby/kirby/pull/8230)
- `Session` package tweaks [#8177](https://github.com/getkirby/kirby/pull/8177)
- DRY-ed exceptions thrown in the `Session` package

### More

- Use trait constant for `IsFile` detection [#8135](https://github.com/getkirby/kirby/pull/8135)

---

## 🧹 Housekeeping

- Upgraded CI setup [#7738](https://github.com/getkirby/kirby/pull/7738)
  - Upgraded to PHPUnit 12 [#7681](https://github.com/getkirby/kirby/pull/7681)
  - Removed `phpmd` from our CI [#7536](https://github.com/getkirby/kirby/pull/7536)
  - Using ParaTest to run PHPUnit tests in parallel [#7803](https://github.com/getkirby/kirby/pull/7803)
  - Cache PHPUnit results
  - Raised Psalm to error level 4 [#7932](https://github.com/getkirby/kirby/pull/7932) [#8170](https://github.com/getkirby/kirby/pull/8170) [#8223](https://github.com/getkirby/kirby/pull/8223) [#8224](https://github.com/getkirby/kirby/pull/8224) [#8225](https://github.com/getkirby/kirby/pull/8225) [#8227](https://github.com/getkirby/kirby/pull/8227)
- Improved testing setup
  - Drastically improved the speed of PHPUnit tests
  - Frontend component tests via Vue Test Utils [#7972](https://github.com/getkirby/kirby/pull/7972)
  - Started tracking frontend unit test coverage [#8188](https://github.com/getkirby/kirby/pull/8188)
  - Added `Kirby\Panel\TestCase::setRequest()` helper method [#7440](https://github.com/getkirby/kirby/pull/7440)
- Upgraded to Vite 8 [#8037](https://github.com/getkirby/kirby/pull/8037)
- Simplified PHP class docblocks [#7944](https://github.com/getkirby/kirby/pull/7944) and added docblocks to frontend files [#8220](https://github.com/getkirby/kirby/pull/8220)
