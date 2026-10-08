# Kirby 6.0.0 Beta Changelog

Kirby 6 and this document are work in progress. The changelog will be updated with every pre-release for Kirby 6.

> [!IMPORTANT]
> Don't use Kirby 6 in production yet.

## 🎉 Features

### Preview view

Live editor mode that lets you work on your content right next to a live preview of your page (https://feedback.getkirby.com/736)

- Preview view syncs navigation in the browser(s)
- Open the preview in a second browser window and still see a live preview of your changes
- Preview your site in different viewport widths in the preview view
- New `panel.preview.sizes` option to define custom viewport sizes

https://github.com/user-attachments/assets/b9bde4cc-ce03-410d-8cd7-e470142e51e0

### New buttons field [#7828](https://github.com/getkirby/kirby/pull/7828)

New `buttons` field to display (a list of) buttons to open a link, dialog or drawer

<img width="954" height="712" alt="image" src="https://github.com/user-attachments/assets/1676c25b-2614-4f2c-8563-216906079ae3" />

```yaml
fields:
  links:
    type: buttons
    buttons:
      - text: Button A
        link: https://getkirby.com
      - text: Button B
        icon: star
        link: https://getkirby.com
```

### Security & authentication

#### Passkeys

Kirby now supports passkeys (WebAuthn) for logging into the Panel. Users authenticate with their device's built-in biometrics or a hardware security key instead of typing a password. They register and manage their passkeys in their account's security settings. https://feedback.getkirby.com/579 [#8245](https://github.com/getkirby/kirby/pull/8245)

<img width="434" height="501" alt="image" src="https://github.com/user-attachments/assets/e3d7b7c5-84bc-4d3c-b2d7-99cabf5e555f" />

**As login method:** users sign in with their passkey, no password required.

```php
// site/config/config.php
return [
	'auth' => [
		'methods' => ['password', 'webauthn']
	]
];
```

<img width="450" height="238" alt="image" src="https://github.com/user-attachments/assets/84ffec9a-f0a4-45d2-b7bc-e72fdee49aa7" />

**As second factor:** users log in with their password, then confirm with a passkey.

```php
// site/config/config.php
return [
	'auth' => [
		'methods' => [
			'password' => ['2fa' => true]
		],
		'challenges' => ['webauthn', 'totp']
	]
];
```

<img width="457" height="295" alt="image" src="https://github.com/user-attachments/assets/04c08a66-cd65-4764-a662-f1d1d8895f42" />

#### Two-factor authentication for every user

Every user can now set up a second factor for their own account (passkey, authenticator app or code via email) from their account's security settings, without an admin having to enforce two-factor authentication for the whole installation. [#8285](https://github.com/getkirby/kirby/pull/8285)

The `auth.methods.password.2fa` option is opt-in by default now: if it isn't set, only users who have set up a second factor get challenged. Set it to `true` to require a second factor from every user:

```php
// site/config/config.php
return [
	'auth' => [
		'methods' => [
			'password' => ['2fa' => true]
		]
	]
];
```

#### More

- User security drawer in the Panel: a single place on the user/account view to handle security-related matters, e.g. email, password, two-factor (TOTP) management. [#7848](https://github.com/getkirby/kirby/pull/7848)
- New `auth.passwords` option to define a password policy (e.g. custom min/max length, custom regex etc.) https://feedback.getkirby.com/697 [#8211](https://github.com/getkirby/kirby/pull/8211)
- Switch between challenges mid-login: when a challenge is pending, users can switch the active challenge (e.g. authenticator-app TOTP vs. emailed code) without restarting [#7848](https://github.com/getkirby/kirby/pull/7848)
- Auth methods extension: plugins can ship their own login method, just like custom challenges. [#7848](https://github.com/getkirby/kirby/pull/7848)
- Custom forms for auth methods and challenges: each method/challenge can declare its own Panel form via `Kirby\Auth\Method::form()` or `Kirby\Auth\Challenge::form()`, which is what makes the login UI fully extensible end-to-end. [#7848](https://github.com/getkirby/kirby/pull/7848)

<img width="948" height="1120" alt="image" src="https://github.com/user-attachments/assets/d2f96a7d-5bc7-4902-8ffb-68589511cbda" />

### Simplified blueprint definition

Deeply nested tabs, columns and sections made blueprints hard to read. In Kirby 6, fields can be defined once at the root level of a blueprint and referenced anywhere in the layout. This reduces nesting and makes it easier to change layouts later. Defining fields inline still works as before. [#7899](https://github.com/getkirby/kirby/pull/7899)

```yaml
title: Article

fields:
  subheading:
    type: text
  cover:
    type: filepicker
  text:
    type: blocks
  date:
    type: date
  author:
    type: userpicker
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

The same works with tabs:

```yaml
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

### Sections become fields

In Kirby 6, fields fully replace sections. Everything you used sections for is now available as a field. Blueprints only need fields, and lists of pages or files can use field features such as `when` conditions and `width`. [#8449](https://github.com/getkirby/kirby/pull/8449)

- New `pagelist` field replaces the `pages` section
- New `filelist` field replaces the `files` section
- `info` and `stats` fields replace their sections
- Existing blueprints keep working: sections are converted into their field equivalents automatically, and the fields of a `fields` section become part of the surrounding column or tab.

```yaml
fields:
  articles:
    type: pagelist
    layout: table
    status: listed
    templates: article
    search: true
    batch: true

  gallery:
    type: filelist
    layout: cards
    template: image
    search: true
    batch: true
```

The picker fields have canonical names now: `pagepicker`, `filepicker` and `userpicker`. `pages`, `files` and `users` keep working as deprecated aliases. [#8449](https://github.com/getkirby/kirby/pull/8449) Their new `picker` option customizes the picker dialog (e.g. `size`, `layout`). [#7687](https://github.com/getkirby/kirby/pull/7687)

### Guards

A single place that answers whether an action on a site, page, file, user or language is possible at all, allowed for the current user and valid for the given input. Every model has a new `$model->guards()` method for this. [#8364](https://github.com/getkirby/kirby/pull/8364)

Guards combine three layers:

- **Abilities:** Is the action possible at all? (e.g. the home page can never be deleted)
- **Permissions:** Is the current user allowed to run it? Resolved from the model's blueprint first, the user role second and the default last.
- **Validators:** Is the given input valid?

```php
// available: checks abilities and permissions without any input,
// e.g. to decide whether to show a button
$page->guards()->isAvailable('delete');     // returns bool
$page->guards()->ensureAvailable('delete'); // throws

// executable: also runs the validators,
// e.g. right before running the action
$page->guards()->isExecutable('changeTitle', $title);     // returns bool
$page->guards()->ensureExecutable('changeTitle', $title); // throws

// each layer can also be checked on its own with `::may()` (bool) or `::ensure()` (throws)
$page->guards()->abilities()->may('changeTitle');
$page->guards()->permissions()->ensure('changeTitle');
$page->guards()->validators()->may('changeTitle', 'Some title');

// all blueprint option actions and whether they are available,
// replaces `$page->permissions()->toArray()`
$page->guards()->availability();
```

Since guards can answer whether an action is available before any input exists, the Panel uses them to enable or disable buttons and fields correctly. [#8475](https://github.com/getkirby/kirby/pull/8475)

### Content negotiation for content representations

Content representations (e.g. `site/templates/article.md.php`) can now also be requested via the `Accept` header instead of the URL extension. A request with `Accept: text/markdown, text/html` gets the Markdown representation of the page. https://feedback.getkirby.com/741 [#8277](https://github.com/getkirby/kirby/pull/8277)

### Template stacks

Snippets and templates can push output onto a stack, which gets rendered anywhere else in your templates, e.g. a CSS `<link>` tag pushed from a block snippet and rendered inside `<head>`. Pushing and rendering are independent of each other, so their order doesn't matter. [#7867](https://github.com/getkirby/kirby/pull/7867)

```php
// render the stack, e.g. in header.php
<head>
  <?php stack('head') ?>
</head>

// push to the stack from any snippet or template
<?php push('head') ?>
<link rel="stylesheet" href="/assets/css/foo.css">
<?php endpush() ?>

// direct push without output buffering
<?php push('head', '<link rel="stylesheet" href="/assets/css/foo.css">') ?>

// unique push: identical content is only added once
<?php push('head', '<link rel="stylesheet" href="/assets/css/foo.css">', unique: true) ?>

// return the stack instead of echoing it
<?php $head = stack('head', return: true) ?>

// custom glue (default: line break)
<?php stack('head', glue: '') ?>
```

### Better error handling in the panel

- New `<k-error-trace>` component to display PHP stack traces. We will use it for PHP error stacks in error dialogs, but it's universal enough to also be used in other places or for JS traces. [#7774](https://github.com/getkirby/kirby/pull/7774)
  <img width="543" height="463" alt="image" src="https://github.com/user-attachments/assets/aa54de2e-d011-44a6-9d0e-e2facd52accb" />
- New `<k-validation-issues>` component to list various issues in fields after a form has been submitted. This will be used in our new error dialogs, but can also be used as a stand-alone component in other parts of the panel. [#7775](https://github.com/getkirby/kirby/pull/7775)
  <img width="594" height="395" alt="image" src="https://github.com/user-attachments/assets/94122230-af87-4404-a5bd-dc896d892446" />
- New `<k-request-error-dialog>` component [#7782](https://github.com/getkirby/kirby/pull/7782)
  <img width="686" height="829" alt="image" src="https://github.com/user-attachments/assets/20b390a4-feb1-4a1e-9fc4-c7cbd04ed3a5" />
- New `<k-validation-error-dialog>` to improve the readability of field validation problems. [#7785](https://github.com/getkirby/kirby/pull/7785)
  <img width="695" height="336" alt="image" src="https://github.com/user-attachments/assets/65404617-6957-4c6e-a30e-4fe1e2575786" />
- Blocked actions explain themselves, e.g. "The home page cannot be deleted" instead of a generic "You are not allowed to do this". Errors tell apart what is impossible for everyone from what the current user is not allowed to do, and several error messages that were English-only are now translated. [#8364](https://github.com/getkirby/kirby/pull/8364)

---

## ✨ Enhancements

### For editors

- Field labels get highlighted when field has unsaved changes [#7794](https://github.com/getkirby/kirby/pull/7794)
  <img width="679" height="360" alt="image" src="https://github.com/user-attachments/assets/644a3dcf-9e1b-4aad-a526-aadb25b5954e" />
  <img width="670" height="364" alt="image" src="https://github.com/user-attachments/assets/08fff2f9-c77b-4c57-bfec-2626df04ce29" />
- Values of disabled Panel fields can be selected [#7581](https://github.com/getkirby/kirby/pull/7581)
- Better drag sorting in list layouts, e.g. in page and file lists [#7349](https://github.com/getkirby/kirby/issues/7349)
- Blocks field: `image` and `gallery` block previews now load properly sized thumbs that also respect the `cover` and `ratio` settings [#7756](https://github.com/getkirby/kirby/pull/7756)
- Page picker: the picker dialog shows a badge for selected children [#7687](https://github.com/getkirby/kirby/pull/7687)
- File picker: upload files by drag'n'drop onto the dialog https://feedback.getkirby.com/729 [#7888](https://github.com/getkirby/kirby/pull/7888)
- New Panel login UI/UX: the login view has been refactored to be more driven by the backend. It now features a method picker, a challenge switcher and centralized loading/error handling. [#7848](https://github.com/getkirby/kirby/pull/7848)
- TOTP management in one drawer: enable/disable TOTP in a single drawer with QR code and a clickable `otpauth://` setup-key link. Only users themselves can set up TOTP for their account and confirm the removal with a current code. Admins can remove TOTP for other users after confirming with their own password. [#7848](https://github.com/getkirby/kirby/pull/7848) [#8268](https://github.com/getkirby/kirby/pull/8268)
  <img width="854" height="1378" alt="image" src="https://github.com/user-attachments/assets/d61cd122-df21-4828-8b0d-dd18320ea4df" />
- A field that fails to be created or to resolve its props shows an error in its place instead of breaking the whole view [#8443](https://github.com/getkirby/kirby/pull/8443)
- Date and time fields show an example of their `display` format as placeholder, so the expected notation is visible before typing [#8322](https://github.com/getkirby/kirby/pull/8322)
- Date field: month names and their abbreviations are recognized in English and in the current Panel language [#8327](https://github.com/getkirby/kirby/pull/8327) [#8328](https://github.com/getkirby/kirby/pull/8328)
- The loading spinner in the topbar only shows up after a short delay, so it no longer flashes for quick requests [#8331](https://github.com/getkirby/kirby/pull/8331)
- The "browser not supported" page names the browser versions the Panel needs [#8450](https://github.com/getkirby/kirby/pull/8450)

### For site developers

- Improved IDE autocompletion for field methods [#7082](https://github.com/getkirby/kirby/pull/7082)
- Panel only shows missing blueprint info when debug mode is active https://feedback.getkirby.com/392
- On installation, all required PHP extensions are checked and installation is blocked if one is missing. The Panel system view shows an alert if a required PHP extension is missing. [#7812](https://github.com/getkirby/kirby/pull/7812)
- `$model->update()` and `Kirby\Form\Form::submit()` accept Kirby objects and collections as field values, e.g. a `$file` or `$files` for a picker field [#8536](https://github.com/getkirby/kirby/pull/8536)

  ```php
  $page->update([
  	'cover'   => $file,
  	'related' => $page->siblings()->listed(),
  	'authors' => $kirby->users()->role('editor'),
  	'items'   => $page->items()->toStructure(),
  	'text'    => $page->text()->toBlocks(),
  	'gallery' => $other->gallery(),
  	'date'    => new DateTime(),
  ]);
  ```

- Kirby queries support logical not and unary minus, e.g. `!user.isAdmin ? "guest" : "admin"`, `-page.price` or `-(1 + 2)` [#8309](https://github.com/getkirby/kirby/pull/8309) [#8578](https://github.com/getkirby/kirby/pull/8578)
- The `display` format of date and time fields supports escaped literals [#8322](https://github.com/getkirby/kirby/pull/8322)

  ```yaml
  fields:
    meeting:
      type: date
      display: D. MMMM [in the year] YYYY
      time:
        display: HH[h]mm
  ```

- New MIME type mappings. `ico` and `psd` also map to their registered types now. [#8284](https://github.com/getkirby/kirby/pull/8284)

  | Extension     | MIME type                                             |
  | ------------- | ----------------------------------------------------- |
  | `7z`          | `application/x-7z-compressed`                         |
  | `aac`         | `audio/aac`                                           |
  | `apng`        | `image/apng`                                          |
  | `atom`        | `application/atom+xml`                                |
  | `epub`        | `application/epub+zip`                                |
  | `ods`         | `application/vnd.oasis.opendocument.spreadsheet`      |
  | `oga`         | `audio/ogg`                                           |
  | `rar`         | `application/vnd.rar`, `application/x-rar-compressed` |
  | `svgz`        | `image/svg+xml`                                       |
  | `vtt`         | `text/vtt`                                            |
  | `wasm`        | `application/wasm`                                    |
  | `weba`        | `audio/webm`                                          |
  | `webmanifest` | `application/manifest+json`                           |

- `Kirby\Toolkit\Date::optional()` accepts `DateTimeInterface` objects and UNIX timestamps

### For plugin developers

#### Core

- Custom fields are implemented as PHP classes based on `Kirby\Form\Field` or any of the `Kirby\Form\Field\*` classes and registered by their class name. You can learn more about this in the "Refactored" section.
- New `Kirby\Cms\Model` base class, extended by `Kirby\Cms\ModelWithContent` and `Kirby\Cms\Language`, for unified type checks across all models [#8369](https://github.com/getkirby/kirby/pull/8369)
- New `Kirby\Cms\User::ensure()` method that always returns a user and falls back to an anonymous `nobody` user if nobody is logged in [#8369](https://github.com/getkirby/kirby/pull/8369)
- New `Kirby\Exception\AbilityException` [#8369](https://github.com/getkirby/kirby/pull/8369)
- New `$model->toSafeHtmlString()` method, similar to `$model->toSafeString()`, but the result is marked as trusted HTML for the Panel [#8344](https://github.com/getkirby/kirby/pull/8344)

  ```php
  $page->toSafeHtmlString('<strong>{{ page.title }}</strong>');
  ```

- New `$defaults` argument for `Kirby\Form\Form::fill()` [#8400](https://github.com/getkirby/kirby/pull/8400)
- New `Kirby\Form\Field::error()` factory to create an error field in place of a field that failed [#8443](https://github.com/getkirby/kirby/pull/8443)
- `Kirby\Cms\Files::delete()` and `Kirby\Cms\Pages::delete()` accept UUIDs and still only delete items in the collection [#8315](https://github.com/getkirby/kirby/pull/8315)
- New `Kirby\Blueprint\Tab` and `Kirby\Blueprint\Tabs` classes to work with tabs and their props in blueprints [#8449](https://github.com/getkirby/kirby/pull/8449)
- New `Kirby\Auth\Service\Webauthn` class [#8245](https://github.com/getkirby/kirby/pull/8245)
- Updated `Kirby\Toolkit\Html::$inlineList` and `Kirby\Toolkit\Html::$voidList` [#8286](https://github.com/getkirby/kirby/pull/8286)

#### Frontend

- Migrated from Vue 2 to Vue 3 [#7104](https://github.com/getkirby/kirby/pull/7104)
- Dialogs and drawers: Added dropzone that becomes active when a `@drop` listener is added on the component [#7520](https://github.com/getkirby/kirby/pull/7520)
- New `<k-video-frame>` component [#7755](https://github.com/getkirby/kirby/pull/7755)
- The `<k-table>` component has a new `responsive` prop, which is set to `true` by default. Switching the responsive mode off will no longer hide columns that are not marked with `data-mobile="true"`. This gives more control over custom responsive behaviors for tables and also helps to improve the usability in narrow widths. [#7770](https://github.com/getkirby/kirby/pull/7770)
- `<k-item>` has a new `selected` prop to control its selection state and a `selectmode` prop to select single or multiple items [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-collection>`/`<k-items>` can receive a list of `selected` items to control selection status from parent [#7516](https://github.com/getkirby/kirby/pull/7516)
- New `<k-model-picker-dialog>` and `<k-page-picker-dialog>` components [#7529](https://github.com/getkirby/kirby/pull/7529)
- New `<k-collapsible>` component that can wrap a list of elements with a default and fallback slot and provide the necessary data how many elements fit in the current container width to render the element as a responsive list of visible and hidden elements (the latter represented by the fallback slot content). [#7901](https://github.com/getkirby/kirby/pull/7901)
- New `<k-model-form>` component [#8449](https://github.com/getkirby/kirby/pull/8449)
- New `RequestError.dialog()` method to create all props for the request error dialog according to the details from the error object. [#7782](https://github.com/getkirby/kirby/pull/7782)
- All errors are now converted to Error objects in `panel.notification.error()` [#7782](https://github.com/getkirby/kirby/pull/7782)
- New `this.$panel.observers` JS module
- The Panel URL is available inside the Panel as `this.$panel.urls.panel` [#7800](https://github.com/getkirby/kirby/pull/7800)
- New Vue components for login forms: `<k-login-password-method-form>`, `<k-login-code-method-form>`, `<k-login-password-reset-method-form>`, `<k-login-email-challenge-form>`, `<k-login-totp-challenge-form>`
- New helper Vue components for the login view: `<k-login-back>`, `<k-login-code>`, `<k-login-footer>`, `<k-login-challenges>`, `<k-login-methods>`, `<k-login-remember>`, `<k-login-submit>` [#7840](https://github.com/getkirby/kirby/pull/7840)
- Trusted HTML [#8175](https://github.com/getkirby/kirby/pull/8175) [#8176](https://github.com/getkirby/kirby/pull/8176) [#8344](https://github.com/getkirby/kirby/pull/8344) [#8346](https://github.com/getkirby/kirby/pull/8346) [#8347](https://github.com/getkirby/kirby/pull/8347) [#8348](https://github.com/getkirby/kirby/pull/8348)
  - New `Kirby\Toolkit\HtmlString` class and `HtmlString` JS class that wrap and mark strings as safe HTML. `HtmlString` values (also in arrays) reach the frontend from every Panel JSON response (views, dialogs, drawers, dropdowns, requests) and from API responses to Panel requests.
  - New `v-safe-html` Vue directive that only renders passed `HtmlString` objects as HTML and escapes any other passed string
  - New `this.$html()` Vue helper (shorthand `this.$h()`) to mark a string as safe HTML (wraps it in a `HtmlString` object)
  - New `this.$th()` helper for translations that contain markup: it marks the translation's own HTML as trusted, but escapes everything interpolated into it. `v-safe-html="$th('some.key', { name })"` is safe even when `name` contains HTML.
  - `<k-text>` has a new `text` prop: plain strings are escaped, trusted HTML is rendered as-is

    ```html
    <!-- escaped -->
    <k-text :text="value" />

    <!-- rendered as HTML -->
    <k-text :text="$h(value)" />

    <!-- translation rendered as HTML, interpolated data is escaped -->
    <k-text :text="$th('my.plugin.hint', { name })" />
    ```

- New `this.$helper.items()` to request preview data for one or more models by ID. Calls made in the same tick are batched into a single request, so components can ask for what they need without coordinating. [#8341](https://github.com/getkirby/kirby/pull/8341)

  ```js
  // a single item
  const file = await this.$helper.items("items/files", "file://abc");

  // a list of items, resolved in the order you asked for them
  const pages = await this.$helper.items("items/pages", [
  	"page://a",
  	"page://b",
  ]);

  // with a query as third argument
  const file = await this.$helper.items("items/files", "file://abc", {
  	layout: "auto",
  	image: JSON.stringify({ ratio: "16/9", cover: true }),
  });
  ```

- `<k-image-frame>` loads images lazily and decodes them asynchronously. Lazy-loaded images use `sizes="auto"` if no `sizes` are specified. Set `:lazy="false"` for images that are visible right away, e.g. an avatar in the header. [#8341](https://github.com/getkirby/kirby/pull/8341) [#8438](https://github.com/getkirby/kirby/pull/8438)
- New `$library.dayjs.parse(input, { pattern, strict, type })` to parse user input against a display pattern [#8328](https://github.com/getkirby/kirby/pull/8328)
- `$library.dayjs()` follows the locale of the current Panel language [#8327](https://github.com/getkirby/kirby/pull/8327)

#### Backend

- New `items/files`, `items/pages` and `items/users` Panel request endpoints to turn IDs into item data, backed by the new `Kirby\Panel\Controller\Request\FileItemsRequestController`, `Kirby\Panel\Controller\Request\PageItemsRequestController` and `Kirby\Panel\Controller\Request\UserItemsRequestController` classes [#7723](https://github.com/getkirby/kirby/pull/7723)
- New `$kirby->panel()` method to access `Kirby\Panel\Panel` object [#7409](https://github.com/getkirby/kirby/pull/7409)
- New `Kirby\Panel\Controller\Controller` classes that can be passed by their name as `action` argument of a Panel route (dialog, drawer, dropdown, request, search and view) and then handle the route action. This helps to move away from the anonymous closures in `config/areas/` and instead build handler classes that can be properly tested etc. [#7422](https://github.com/getkirby/kirby/pull/7422)
- New Panel UI classes: `Kirby\Panel\Ui\Dialog`, `Kirby\Panel\Ui\Dialog\ErrorDialog`, `Kirby\Panel\Ui\Dialog\FormDialog`, `Kirby\Panel\Ui\Dialog\RemoveDialog`, `Kirby\Panel\Ui\Dialog\TextDialog`, `Kirby\Panel\Ui\Drawer`, `Kirby\Panel\Ui\Drawer\FormDrawer`, `Kirby\Panel\Ui\Drawer\TextDrawer`, `Kirby\Panel\Ui\View`, `Kirby\Panel\Ui\View\ErrorView`, `Kirby\Panel\Ui\Item` (foundation for `Kirby\Panel\Ui\Item\ModelItem` etc.) and `Kirby\Panel\Ui\Item\LanguageItem` [#7441](https://github.com/getkirby/kirby/pull/7441) [#7443](https://github.com/getkirby/kirby/pull/7443) [#7468](https://github.com/getkirby/kirby/pull/7468) [#7471](https://github.com/getkirby/kirby/pull/7471)
- `Kirby\Panel\Response\JsonResponse::from()` resolves Panel UI component objects by calling their `Kirby\Panel\Ui\Component::render()` method, so `Kirby\Panel\Controller\Controller::load()` can return UI component instances [#7442](https://github.com/getkirby/kirby/pull/7442)
- New `Kirby\Toolkit\HasI18n` trait that adds a `Kirby\Toolkit\HasI18n::i18n()` helper method to the class which can be used to translate and/or template i18n strings. New `Kirby\Panel\Ui\Component::i18n()` helper method [#7406](https://github.com/getkirby/kirby/pull/7406) [#7465](https://github.com/getkirby/kirby/pull/7465)
- Exceptions are no longer intercepted by our Response classes but handled by our regular error handler. This will improve the JSON error responses drastically and expose more details in debug mode. One exception (pun intended) are NotFoundExceptions for views, which will still create a full error view with error message, to make sure that navigation to non-existing routes still works. [#7744](https://github.com/getkirby/kirby/pull/7744)
- New `Kirby\Exception\FormValidationException` [#7769](https://github.com/getkirby/kirby/pull/7769)
- Error responses from the backend now include the `editor` URL if the editor is set up in the config.php and debug mode is on. [#7782](https://github.com/getkirby/kirby/pull/7782)
- New protected `Kirby\Cms\AppErrors::trace()` method to return a stack trace for PHP errors in JSON responses when debug mode is enabled. [#7782](https://github.com/getkirby/kirby/pull/7782)
- Auth methods can authenticate without an email, enabling token/SSO-style plugin methods that don't key on email. [#7848](https://github.com/getkirby/kirby/pull/7848)
- New `Kirby\Auth\Auth::guard()` helper to wrap an auth attempt with rate-limiting and error handling [#8260](https://github.com/getkirby/kirby/pull/8260)
- Plugins can remove any core definition from a Panel area by setting it to `false`, as was already possible for menu items and view buttons [#8357](https://github.com/getkirby/kirby/pull/8357)

  ```php
  Kirby::plugin('my/plugin', [
  	'areas' => [
  		'site' => [
  			'searches' => [
  				'pages' => false
  			]
  		]
  	]
  ]);
  ```

- New `Kirby\Panel\User::info()` method that returns the data for `<k-user-info>` [#8245](https://github.com/getkirby/kirby/pull/8245)
- New `Kirby\Panel\Controller\View\ModelViewController::fields()` method that returns all form fields of the current tabs and versions [#8449](https://github.com/getkirby/kirby/pull/8449)

### More

- New icons: `hashtag`, `email-unread`, `fingerprint`
- `this.$panel.t()` / `window.panel.t()` now returns the string key as a fallback if no matching i18n string exists and no actual fallback has been provided

---

## 🚨 Security

- Cookies are now signed with HMAC-SHA256 instead of HMAC-SHA1 [#8561](https://github.com/getkirby/kirby/pull/8561)
- `Kirby\Http\Cookie::set()` adds the `Secure` flag by default when the site is served over HTTPS [#8560](https://github.com/getkirby/kirby/pull/8560)
- Panel components escape plain strings and only render HTML that has been marked as trusted (`Kirby\Toolkit\HtmlString`), e.g. in `<k-item>`, `<k-text>`, `<k-box>` and `<k-tags>`. The block pasteboard, block selector and installation view no longer use `v-html` at all. [#8344](https://github.com/getkirby/kirby/pull/8344) [#8348](https://github.com/getkirby/kirby/pull/8348)
- Hardened security of `Kirby\Session\Session` (thx [@XananasX7](https://github.com/XananasX7))

---

## 🏎️ Performance

### Core

- Normalized blueprints are cached [#8449](https://github.com/getkirby/kirby/pull/8449)
- Block fieldsets no longer build their forms upfront. Saving a page with a `blocks` field is faster when the blueprint registers more block types than the content uses. [#8391](https://github.com/getkirby/kirby/pull/8391)
- Constructor parameter names are resolved once per class instead of once per reflection [#8390](https://github.com/getkirby/kirby/pull/8390)
- `Kirby\Exception\Exception` and its subclasses resolve their code and message lazily [#8459](https://github.com/getkirby/kirby/pull/8459)

### Panel

- Panel routes resolve their controller classes lazily, so building the route table no longer autoloads every area controller [#8366](https://github.com/getkirby/kirby/pull/8366)
- Typing inside a block, layout, entries or structure row no longer re-renders the surrounding field on every keystroke [#8449](https://github.com/getkirby/kirby/pull/8449)
- The blocks field only renders the toolbar of the selected block instead of one hidden toolbar per block [#8447](https://github.com/getkirby/kirby/pull/8447)
- `pagelist` and `filelist` fields with `layout: table` load faster. Their performance only depends on the number of displayed columns, no longer on the size of the blueprint. [#8439](https://github.com/getkirby/kirby/pull/8439)
- Requests for model item preview data are batched: components that ask for a file or page in the same tick share a single request instead of firing one each [#8341](https://github.com/getkirby/kirby/pull/8341)
- `panel.content.diff()` is computed once per change instead of on every call [#8340](https://github.com/getkirby/kirby/pull/8340)
- ProseMirror is no longer part of the initial Panel bundle but imported when needed [#8342](https://github.com/getkirby/kirby/pull/8342)
- The icon sprite is no longer inlined into every Panel document [#8445](https://github.com/getkirby/kirby/pull/8445)

---

## 🐛 Bug fixes

### Core

- Fixed auto-closing open snippets at the end of a nested snippet (thx to [@JojOatXGME](https://github.com/JojOatXGME)) [#7567](https://github.com/getkirby/kirby/issues/7567)
- `Kirby\Filesystem\Exif` now supports arrays for `ISOSpeedRatings` [#7569](https://github.com/getkirby/kirby/issues/7569)
- `Kirby\Form\Field::stringTemplate()` now uses `Kirby\Cms\ModelWithContent::toSafeString()` by default and introduces a new `$safe` argument, which can switch to the unsafe method. [#7687](https://github.com/getkirby/kirby/pull/7687)
- Login-failed hook is no longer fired twice. [#7848](https://github.com/getkirby/kirby/pull/7848)
- `Kirby\Text\KirbyTag::__set()` now lowercases the properties, as `Kirby\Text\KirbyTag::__get()` already did
- `Kirby\Toolkit\Str::date(null, null)` returns the current timestamp
- Fixed `Kirby\Toolkit\A::merge()` with empty input
- `Kirby\Image\Exif::parseTimestamp()` returns `null` for a missing timestamp now
- `Kirby\Filesystem\File::realpath()` returns `false` when the file does not exist at the path
- Actions run their checks against the model returned by a `before` hook, not the original one [#8364](https://github.com/getkirby/kirby/pull/8364)
- Changing a user's role or secrets no longer fails with an unexpected error when nobody is logged in [#8364](https://github.com/getkirby/kirby/pull/8364)
- Fields without a value no longer break saving [#8449](https://github.com/getkirby/kirby/pull/8449)
- `new Exception(message: '…', previous: $previous)` no longer discards `$previous` [#8459](https://github.com/getkirby/kirby/pull/8459)
- Discarded `Kirby\Cms\App` instances are no longer kept in memory by Whoops handlers, KirbyTag closures and translation loaders. An app created with `setInstance: false` no longer takes over the global translation loader. [#8339](https://github.com/getkirby/kirby/pull/8339)
- The number field stores an empty value instead of `0` for non-numeric input [#8534](https://github.com/getkirby/kirby/pull/8534)
- JSON responses with invalid UTF-8 fail with an error instead of going out as an empty `200`. JSON error responses are no longer empty when the exception message or details contain invalid UTF-8. [#8562](https://github.com/getkirby/kirby/pull/8562)
- Queries:
  - Backslashes in strings are kept, e.g. in regex patterns like `'\d+'`. Only `\'`, `\"` and `\\` are escapes. [#8580](https://github.com/getkirby/kirby/pull/8580)
  - Subtraction works without spaces, e.g. `page.num-1` [#8578](https://github.com/getkirby/kirby/pull/8578)
  - `??`, `?:`, `? :`, `&&` and `||` only evaluate the operand they need [#8576](https://github.com/getkirby/kirby/pull/8576)
  - Each query is resolved with its own `intercept()`, even if another query was created in between [#8579](https://github.com/getkirby/kirby/pull/8579)
  - Changes to `Kirby\Query\Query::$entries` (e.g. from plugins) also reach queries that were resolved before [#8579](https://github.com/getkirby/kirby/pull/8579)
  - `{{ 0 }}` resolves to `0` instead of the data array [#8577](https://github.com/getkirby/kirby/pull/8577)
  - `Kirby\Toolkit\Str::template()`: empty placeholders like `{}` or `{{ }}` stay as they are instead of failing with "Array to string conversion" [#8577](https://github.com/getkirby/kirby/pull/8577)

### Panel

- The toggle field uses `<k-toggle-input>` instead of `<k-input type="toggle">`, like the radio and checkboxes fields [#7489](https://github.com/getkirby/kirby/pull/7489)
- `<k-item>` only hides the default options button in selecting mode, not a custom options slot [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>` is selectable by default unless explicitly defined otherwise [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>`: `selected` also works with UUIDs [#7751](https://github.com/getkirby/kirby/pull/7751)
- The languages button no longer shows a badge for current changes [#7749](https://github.com/getkirby/kirby/pull/7749)
- Context data (path, referrer, query, code) is always added to the view response again, so error views get the correct URL instead of causing side effects like redirects to `/panel/null` [#7744](https://github.com/getkirby/kirby/pull/7744)
- Listeners of backend-defined dialogs and drawers are preserved when loading them [#7888](https://github.com/getkirby/kirby/pull/7888)
- Writer field: pasting text that contains an email address now turns it into an email link even when the address is surrounded by other text [#8110](https://github.com/getkirby/kirby/pull/8110)
- Writer field: Markdown shortcuts for headline levels that aren't enabled (e.g. `#` without `h1`) are no longer converted [#8111](https://github.com/getkirby/kirby/pull/8111)
- Date and time fields read the input in the field's own `display` format instead of guessing. Input that cannot be read is kept and the field is marked invalid, instead of silently clearing the field. [#8322](https://github.com/getkirby/kirby/pull/8322)
- Time fields word their `min`/`max` validation messages as times instead of dates [#8322](https://github.com/getkirby/kirby/pull/8322)
- `$library.dayjs.iso()` parses strictly [#8325](https://github.com/getkirby/kirby/pull/8325)
- `$library.dayjs.validate()` is as strict as the validation in the backend [#8329](https://github.com/getkirby/kirby/pull/8329)
- `$helper.url.buildQuery()` supports nested objects as params [#8349](https://github.com/getkirby/kirby/pull/8349)
- `$helper.string.template()` no longer HTML-escapes the placeholder key, which prevented lookups for keys containing characters like `/` or `&` [#8348](https://github.com/getkirby/kirby/pull/8348)
- The "Split" option is no longer missing from the block options on first render [#8447](https://github.com/getkirby/kirby/pull/8447)
- A table column with `value: "0"` renders `0` instead of falling back to the field value [#8439](https://github.com/getkirby/kirby/pull/8439)

---

## 🚨 Breaking changes

### Core

#### General

- Removed support for PHP 8.2. Use PHP 8.3, 8.4 or 8.5 instead. [#7372](https://github.com/getkirby/kirby/pull/7372)
- Changed the default YAML handler to Symfony YAML which sometimes enforces a stricter syntax than our previous Spyc handler. You can switch back to Spyc with the config option `'yaml.handler' => 'spyc'` [#7530](https://github.com/getkirby/kirby/pull/7530)
- New global helper functions `push()`, `endpush()` and `stack()` can collide with functions of the same name in your code [#7867](https://github.com/getkirby/kirby/pull/7867)
- Template data must not include variables named `$slot` or `$slots`. [#7599](https://github.com/getkirby/kirby/pull/7599)
- `video` KirbyTag: Removed `style` option to protect against style injections. If you need to define a custom `style` attribute, please extend as a custom KirbyTag. Thanks to Peter Levashov (@petersevera) for his responsible disclosure and suggestion.
- Custom validators cannot overwrite default validators from the `Kirby\Toolkit\V` class any longer. [#7674](https://github.com/getkirby/kirby/pull/7674)
- Removed `Kirby\Cms\Api` class. Use `Kirby\Api\Api` class instead. [#7532](https://github.com/getkirby/kirby/pull/7532)
- Removed `Kirby\Cms\System::php()` [#7816](https://github.com/getkirby/kirby/pull/7816)
- `Kirby\Cms\App::models()` and `Kirby\Uuid\Uuid::index()` generators are now keyed by id
- `Kirby\Cms\LazyCollection::getIterator()` skips items that failed hydration
- `Kirby\Cms\Fields` extends `Kirby\Cms\Collection` and `Kirby\Cms\HasSiblings` relies on `Kirby\Cms\Collection`, not `Kirby\Toolkit\Collection`
- `Kirby\Cms\Fieldset::name()` falls back to a labelized type
- Added abstract `Kirby\Cms\ModelWithContent::apiUrl()` method
- `Kirby\Data\Txt::encodeValue()` encodes `true` and `false` as `'true'` and `'false'` instead of `'1'` and `'0'`, and an empty array as empty string instead of `[]`
- `Kirby\Http\VolatileHeaders::append()` requires an array as third parameter
- `Kirby\Toolkit\Str::float()` returns `null` instead of `'0'` for non-numeric values (including `null` and `''`). Use `Kirby\Toolkit\Str::float($value) ?? '0'` if you relied on the old behavior. [#8534](https://github.com/getkirby/kirby/pull/8534)
- Removed `command`, `keygen` and `param` from `Kirby\Toolkit\Html::$voidList`, as they are obsolete and no longer part of the WHATWG list of void elements. `Kirby\Toolkit\Html::isVoid()` returns `false` for them and `Kirby\Toolkit\Html::tag()` no longer renders them self-closing. [#8286](https://github.com/getkirby/kirby/pull/8286)
- `qr()` helper only accepts a site, page or file object or a URL string as parameter.

#### Cookies & HTTP

- Cookies are signed with HMAC-SHA256 instead of HMAC-SHA1. Cookies signed by earlier versions, including the session cookie, are no longer accepted: users need to log in again after the upgrade. [#8561](https://github.com/getkirby/kirby/pull/8561)
- `Kirby\Http\Cookie::set()` sets the `secure` option to `true` on HTTPS requests by default. Pass `'secure' => false` if a cookie set on an HTTPS page must also be readable on plain HTTP pages: `Cookie::set('name', 'value', ['secure' => false])` [#8560](https://github.com/getkirby/kirby/pull/8560)
- The `$pretty` argument of `Kirby\Http\Response::json()` must be a `bool`. Pass `false` instead of `null`. [#8562](https://github.com/getkirby/kirby/pull/8562)

#### MIME types

`Kirby\Filesystem\Mime::fromExtension()` and `Kirby\Filesystem\Mime::type()` return the registered IANA type for these extensions now (e.g. RFC 6713 for gzip, RFC 9239 for JavaScript, RFC 7303 for XML). [#8284](https://github.com/getkirby/kirby/pull/8284)

| Extension    | Before                     | Now                                             |
| ------------ | -------------------------- | ----------------------------------------------- |
| `exe`        | `application/octet-stream` | `application/vnd.microsoft.portable-executable` |
| `gz`, `gzip` | `application/x-gzip`       | `application/gzip`                              |
| `js`         | `application/javascript`   | `text/javascript`                               |
| `ppt`        | `application/powerpoint`   | `application/vnd.ms-powerpoint`                 |
| `psd`        | `application/x-photoshop`  | `image/vnd.adobe.photoshop`                     |
| `tgz`        | `application/x-tar`        | `application/gzip`                              |
| `wbxml`      | `application/wbxml`        | `application/vnd.wap.wbxml`                     |
| `xl`, `xls`  | `application/excel`        | `application/vnd.ms-excel`                      |
| `xml`        | `text/xml`                 | `application/xml`                               |
| `xsl`        | `text/xml`                 | `application/xslt+xml`                          |
| `zip`        | `application/x-zip`        | `application/zip`                               |

`Kirby\Filesystem\Mime::toExtension()` returns different results for MIME types whose secondary mapping was removed:

| MIME type                       | Before | Now     |
| ------------------------------- | ------ | ------- |
| `application/x-gzip-compressed` | `tgz`  | `false` |
| `application/octet-stream`      | `csv`  | `exe`   |

#### Field methods

- Calling non-existing field methods throws a `Kirby\Exception\BadMethodCallException`. [#7082](https://github.com/getkirby/kirby/pull/7082)
- Removed `Kirby\Content\Field::$aliases`, `Kirby\Cms\Core::fieldMethods()` and `Kirby\Cms\Core::fieldMethodsAliases()`. [#7082](https://github.com/getkirby/kirby/pull/7082)

#### UUIDs

- `Kirby\Uuid\Uuid::for()` does not resolve any permalinks anymore. Use `Kirby\Uuid\Permalink::from()` instead. [#7545](https://github.com/getkirby/kirby/pull/7545)
- `Kirby\Uuid\Uuid::for()` cannot be called any longer with a string. Use `Kirby\Uuid\Uuid::from(string $uuid)` or `Kirby\Uuid\Permalink::from(string $permalink)` instead. [#7544](https://github.com/getkirby/kirby/pull/7544)
- Removed deprecated `Kirby\Uuid\Uuid::url()`. Use `Kirby\Uuid\Uuid::toPermalink()` instead.

#### Queries

- Removed the legacy query runner. If your config sets `query.runner` to `legacy`, remove that option. Queries always use the AST-based `Kirby\Query\Runners\DefaultRunner` now. [#7791](https://github.com/getkirby/kirby/pull/7791) [#8554](https://github.com/getkirby/kirby/pull/8554)
- Removed `Kirby\Query\Argument`, `Kirby\Query\Arguments`, `Kirby\Query\Expression`, `Kirby\Query\Segment` and `Kirby\Query\Segments`. Use `Kirby\Query\Query::resolve()` or a custom `Kirby\Query\Runners\Runner` instead. [#8554](https://github.com/getkirby/kirby/pull/8554)
- `Kirby\Query\Visitors\Visitor`: required methods are abstract now and get enforced when extending the class. `Kirby\Query\Visitors\Visitor::coalescence()`, `Kirby\Query\Visitors\Visitor::logical()` and `Kirby\Query\Visitors\Visitor::ternary()` receive the right operand/branches as `Closure`. [#8576](https://github.com/getkirby/kirby/pull/8576)

#### Sessions

- Merged `Kirby\Session\AutoSession` into `Kirby\Session\Sessions` [#8248](https://github.com/getkirby/kirby/pull/8248)
- `Kirby\Session\Sessions::get()` no longer accepts a `$token`. Use `Kirby\Session\Sessions::find()` instead. [#8248](https://github.com/getkirby/kirby/pull/8248)
- `new Kirby\Session\Sessions()` no longer accepts an `$options` argument. Use `Kirby\Session\Sessions::factory()` instead. [#8248](https://github.com/getkirby/kirby/pull/8248)
- `Kirby\Session\Session` no longer serializes objects

#### Exceptions

- Passing a single array argument to `Kirby\Exception\Exception` is no longer supported. Use named arguments instead. The string shorthand `new Exception('Some message')` is unchanged. [#8459](https://github.com/getkirby/kirby/pull/8459)
- Removed `Kirby\Exception\Exception::isTranslated()` [#8459](https://github.com/getkirby/kirby/pull/8459)

#### Permissions

- Several i18n error keys and strings have changed [#8364](https://github.com/getkirby/kirby/pull/8364)
- `Kirby\Cms\Permissions::for()` allows `null` as default [#8369](https://github.com/getkirby/kirby/pull/8369)
- Removed `Kirby\Cms\ModelPermissions::canFromCache()`. Use `$model->guards()->isAvailable()` instead. [#8481](https://github.com/getkirby/kirby/pull/8481)

#### Forms

**Custom fields**

- Custom fields can no longer be defined as arrays. Create a class based on `Kirby\Form\Field` (or one of the `Kirby\Form\Field\*` classes) and register it by its class name. To extend a core field, extend its class (e.g. `Kirby\Form\Field\TextField`). [#8395](https://github.com/getkirby/kirby/pull/8395) [#8396](https://github.com/getkirby/kirby/pull/8396)
- Removed `Kirby\Form\FieldClass` and the `legacy-*` fields. `Kirby\Form\Field` has been fully rewritten as base class for all fields. It has a new `Kirby\Form\Field::id()` method and implements `Stringable`. [#8395](https://github.com/getkirby/kirby/pull/8395) [#8399](https://github.com/getkirby/kirby/pull/8399)
- Removed the `Kirby\Form\Mixin\Value` mixin in favor of the new `Kirby\Form\Field\ValueField` base class. Custom fields with a value must extend `Kirby\Form\Field\ValueField` (or `Kirby\Form\Field\InputField`), as the now final `Kirby\Form\Field::hasValue()` checks for it. The deprecated `Kirby\Form\Mixin\Value::data()` and `Kirby\Form\Mixin\Value::save()` methods are replaced by `Kirby\Form\Field\ValueField::toStoredValue()` and `Kirby\Form\Field::hasValue()`. [#8400](https://github.com/getkirby/kirby/pull/8400) [#8401](https://github.com/getkirby/kirby/pull/8401)
- Fields with subfields must implement the new `Kirby\Form\Interface\ProvidesNestedForm` interface to be found by nested lookups like `Kirby\Form\Fields::findByKeyRecursive('parent+child')` [#8401](https://github.com/getkirby/kirby/pull/8401)
- Field constructors only declare their own parameters and forward inherited ones via `...$args`. The positional order has changed, so pass field props as named arguments. [#8492](https://github.com/getkirby/kirby/pull/8492)
- Field props are stored as passed: all setters of field classes and mixins have been removed (set the properties directly), and defaults, translations (`after`, `before`, `empty`, `help`, `label`, `placeholder`) and the lower-casing of the name are applied in the getters instead. This keeps the originally passed values accessible and reduces the work done on instantiation.
- The model and siblings of a field are injected after the construction via the public `Kirby\Form\Field::setModel()` and `Kirby\Form\Field::setSiblings()` methods. Constructors and setters can no longer rely on them. Use getters instead.
- The value is no longer passed to the constructor. Use `Kirby\Form\Field\ValueField::fill()` to provide an initial value.
- Fields define their API routes in `Kirby\Form\Field::api()`, not in a `routes()` method
- Removed `Kirby\Cms\Picker`, `Kirby\Cms\PagePicker`, `Kirby\Cms\FilePicker` and `Kirby\Cms\UserPicker`. Use `Kirby\Panel\Controller\Dialog\ModelPickerDialogController`, `Kirby\Panel\Controller\Dialog\PagePickerDialogController`, `Kirby\Panel\Controller\Dialog\FilePickerDialogController` and `Kirby\Panel\Controller\Dialog\UserPickerDialogController` instead. [#8395](https://github.com/getkirby/kirby/pull/8395)

**Form class**

- Removed deprecated `Kirby\Form\Form` methods [#8400](https://github.com/getkirby/kirby/pull/8400)
  - `Kirby\Form\Form::content()` → `Kirby\Form\Form::toStoredValues()`
  - `Kirby\Form\Form::data()` and `Kirby\Form\Form::strings()` → `Kirby\Form\Form::toStoredValues()` together with `Kirby\Form\Form::fill(defaults: true)`
  - `Kirby\Form\Form::values()` → `Kirby\Form\Form::toFormValues()`
- `Kirby\Form\Form::for()` and `new Kirby\Form\Form()` require named arguments. A `$props` array is no longer accepted. [#8400](https://github.com/getkirby/kirby/pull/8400)
- `Kirby\Form\Fields::validate()` and `Kirby\Form\Form::validate()` throw the more specific `Kirby\Exception\FormValidationException`. [#7769](https://github.com/getkirby/kirby/pull/7769)

**Field behavior**

- The `checkboxes`, `color`, `multiselect`, `radio`, `select`, `tags` and `toggles` fields no longer remove invalid values on submit or fill, but use the option validator to warn about them. This is in line with other input fields and has massive performance benefits. It also means that you can deliberately store a non-existing option if you skip validation.
- The `api` and `query` options of these fields are no longer available. Queries and API calls to fetch options have to be declared directly in the `options` property:

  ```yaml
  fields:
    myRadio:
      type: radio
      options:
        type: query
        query: some.query

    myOtherRadio:
      type: radio
      options:
        type: api
        url: /some/options/api
  ```

- `Kirby\Form\Field\NumberField::toNumber()` and `Kirby\Form\Field\RangeField::toNumber()` return `null` instead of an empty string for empty values
- The text field no longer sets the `spellcheck` attribute by default
- The `filepicker`, `pagepicker` and `userpicker` fields only exchange UUIDs/IDs with the backend and their picker dialog, instead of full item objects with display data

#### Blueprints

- Removed blueprint presets (`preset: page`, `preset: pages`, `preset: files`). Define the layout directly with fields like `pagelist` and `filelist` instead. [#8386](https://github.com/getkirby/kirby/pull/8386)
- `Kirby\Blueprint\Blueprint::factory()` requires the `$model` as first argument. Some blueprint props are translated lazily, so prefer `Kirby\Blueprint\Blueprint::factory()` over `Kirby\Blueprint\Blueprint::load()`.
- `Kirby\Blueprint\Blueprint::tab()` returns a `Kirby\Blueprint\Tab` object and `Kirby\Blueprint\Blueprint::tabs()` returns a `Kirby\Blueprint\Tabs` collection. The normalized tabs no longer contain their link, which is built by the `Kirby\Blueprint\Tab` class instead. [#8449](https://github.com/getkirby/kirby/pull/8449)
- The `$inSection` argument of `Kirby\Blueprint\AcceptRules::fileTemplates()`, `Kirby\Blueprint\Blueprint::acceptedFileTemplates()` and `Kirby\Cms\File::blueprints()` has been renamed to `$inField` [#8406](https://github.com/getkirby/kirby/pull/8406)
- Removed the `Kirby\Blueprint\Blueprint::$fileTemplates` property and the protected methods `Kirby\Blueprint\Blueprint::acceptedFileTemplatesFromFields()`, `Kirby\Blueprint\Blueprint::acceptedFileTemplatesFromFieldsets()` and `Kirby\Blueprint\Blueprint::acceptedFileTemplatesFromFieldUploads()` [#7829](https://github.com/getkirby/kirby/pull/7829)

#### Changed return types

|                                         | Before              | Now                      |
| --------------------------------------- | ------------------- | ------------------------ |
| `Kirby\Http\Request::domain()`          | string              | string\|null             |
| `Kirby\Image\Exif::parseTimestamp()`    | string              | string\|null             |
| `Kirby\Cms\Site::modified()`            | int\|string         | int\|string\|false       |
| `Kirby\Filesystem\Dir::modified()`      | int\|string         | int\|string\|false       |
| `Kirby\Filesystem\File::realpath()`     | string              | string\|false            |
| `Kirby\Toolkit\V::value()`              | bool\|array         | true\|array              |
| `Kirby\Cms\LicenseStatus::info()`       | string              | HtmlString               |
| `Kirby\Form\Mixin\Help::help()`         | string\|null        | HtmlString\|null         |
| `Kirby\Form\Mixin\Text::text()`         | string\|null        | HtmlString\|null         |
| `Kirby\Form\Field\ToggleField::text()`  | array\|string\|null | array\|HtmlString\|null  |
| `Kirby\Panel\Ui\Item::text()`           | string              | string\|HtmlString       |
| `Kirby\Panel\Ui\Item::info()`           | string\|null        | string\|HtmlString\|null |
| `Kirby\Panel\Ui\Item\ModelItem::text()` | string              | HtmlString               |
| `Kirby\Panel\Ui\Item\ModelItem::info()` | string\|null        | HtmlString\|null         |
| `Kirby\Panel\Lab\Doc::kt()`             | string              | HtmlString               |
| `Kirby\Toolkit\HasI18n::i18n()`         | string\|null        | string\|HtmlString\|null |

`HtmlString` refers to `Kirby\Toolkit\HtmlString` [#8344](https://github.com/getkirby/kirby/pull/8344). Cast it with `(string)` if you need the raw string, and compare arrays that contain it with `==`/`assertEquals()` instead of `===`/`assertSame()`. The array returned by `Kirby\Form\Field\ToggleField::text()` contains `Kirby\Toolkit\HtmlString` entries, and `Kirby\Toolkit\HasI18n::i18n()` passes a `Kirby\Toolkit\HtmlString` `$key` through unchanged.

#### Changed exceptions

[#8277](https://github.com/getkirby/kirby/pull/8277) [#8364](https://github.com/getkirby/kirby/pull/8364)

| Case                                                                                            | Before                                | Now                                        |
| ----------------------------------------------------------------------------------------------- | ------------------------------------- | ------------------------------------------ |
| Actions that are impossible for everyone                                                        | `Kirby\Exception\PermissionException` | `Kirby\Exception\AbilityException` (new)   |
| Deleting an avatar that does not exist                                                          | `Kirby\Exception\NotFoundException`   | `Kirby\Exception\AbilityException`         |
| Changing a file or page to an invalid template                                                  | `Kirby\Exception\LogicException`      | `Kirby\Exception\InvalidArgumentException` |
| `Kirby\Cms\App::resolve()` when the home page is missing, which indicates a broken installation | `Kirby\Exception\NotFoundException`   | `Kirby\Exception\LogicException`           |

#### Methods that newly throw

|                                        | Before          | Now                                                                           |
| -------------------------------------- | --------------- | ----------------------------------------------------------------------------- |
| `Kirby\Toolkit\Dom::query()`           | returns `false` | throws if query invalid                                                       |
| `Kirby\Cache\Value::toJson()`          |                 | throws for invalid JSON                                                       |
| `Kirby\Data\Json::encode()`            |                 | throws for invalid JSON                                                       |
| `Kirby\Filesystem\File::toJson()`      |                 | throws for invalid JSON                                                       |
| `Kirby\Http\Uri::toJson()`             |                 | throws for invalid JSON                                                       |
| `Kirby\Toolkit\Collection::toJson()`   |                 | throws for invalid JSON                                                       |
| `Kirby\Toolkit\Obj::toJson()`          |                 | throws for invalid JSON                                                       |
| `Kirby\Filesystem\File::sha1()`        | returns `false` | throws on missing file                                                        |
| `Kirby\Content\Version::contentFile()` |                 | throws when called with a storage other than `Kirby\Content\PlainTextStorage` |
| `Kirby\Filesystem\Dir::realpath()`     |                 | throws when `$dir` itself is passed as `$in`                                  |

### Sections

Sections have been replaced by fields (see "Sections become fields" above). Blueprints with `fields`, `files`, `info`, `pages` and `stats` sections keep working, as they are converted to fields automatically. Everything else around sections has been removed. [#8449](https://github.com/getkirby/kirby/pull/8449)

- Any other section type, including custom sections from plugins, is no longer supported in blueprints
- Removed the `sections` plugin extension, along with `Kirby\Cms\App::extendSections()`, `Kirby\Cms\Core::sections()`, `Kirby\Cms\Core::sectionMixins()`, `Kirby\Cms\Loader::sections()` and `Kirby\Cms\Loader::section()`
- Removed `Kirby\Blueprint\Section`, the `Kirby\Cms\Section` alias, `Kirby\Blueprint\Blueprint::section()`, `Kirby\Blueprint\Blueprint::sections()` and `Kirby\Api\Api::sectionApi()`
- Removed all `…/sections/(:any)` and `…/sections/(:any)/(:all?)` API routes for the site, pages, users and their files
- Removed all section dialog and drawer routes and their Panel controller classes
- The blueprints API endpoints (`/api/site/blueprints`, `/api/pages/(:any)/blueprints` and `/api/users/(:any)/blueprints`) accept a `field` query parameter instead of `section`. The `section` argument of `panel.api.pages.blueprints()` and `panel.api.users.blueprints()` has been renamed to `field` as well.
- Removed components: `<k-sections>`, `<k-fields-section>`, `<k-files-section>`, `<k-info-section>`, `<k-models-section>`, `<k-pages-section>` and `<k-stats-section>`, as well as the `k-section-field` adapter and the `section` plugin mixin. Use `<k-model-form>` instead of `<k-sections>` and `<k-fields-section>`.
- The `endpoints` object of field components no longer contains a `section` endpoint
- The `section.loaded` event has been replaced by `field.loaded`, emitted by `pagelist` and `filelist` fields [#8415](https://github.com/getkirby/kirby/pull/8415)
- Renamed i18n keys
  - `error.section.files.*` → `error.field.filelist.*`
  - `error.section.pages.*` → `error.field.pagelist.*`
  - `error.page.move.noSections` → `error.page.move.noBlueprints`
- Removed i18n keys: `error.section.notLoaded`, `error.section.type.invalid`

### Panel

#### General

- The Panel needs Chrome 124, Edge 124, Firefox 125, Safari 17.5 or newer, i.e. browsers released from roughly mid-2024 onwards. Kirby follows Baseline "widely available": we only rely on web platform features that have been in every major browser for at least 30 months. [#8450](https://github.com/getkirby/kirby/pull/8450)
- `panel.favicon` option: Use `href` instead of `url` attribute. Use `rel` attribute instead of passing string as key.
- Color field options `text => value` notation has been removed. Please rewrite your options as `value => text`. [#7534](https://github.com/getkirby/kirby/pull/7534)

#### Frontend

- All plugins that have compiled their Vue Single File Components with Vue 2 have to recompile their SFC for Vue 3 to work with Kirby 6. Consider also the [Vue 3 migration guide](https://v3-migration.vuejs.org/breaking-changes/).
- All plugin JS files get loaded as module.
- The icon sprite is no longer part of the Panel document. Custom icons registered by plugins keep working, but markup that references the sprite by hand (`<use xlink:href="#icon-…">`) needs the sprite's URL now: `window.panel.urls.icons + "#icon-edit"` [#8445](https://github.com/getkirby/kirby/pull/8445)
- The Panel no longer sets a `data-overlay` attribute. Use `:has(.k-overlay[open])` instead. [#8343](https://github.com/getkirby/kirby/pull/8343)
- Renamed components
  - `<k-dropdown-content>` → `<k-dropdown>`. The `align` prop has been removed. Use `align-x` instead. [#7535](https://github.com/getkirby/kirby/pull/7535)
  - `<k-fiber-dialog>` → `<k-state-dialog>`
  - `<k-fiber-drawer>` → `<k-state-drawer>`
- Removed deprecated components
  - `<k-bubble>`, `<k-bubbles>` and `<k-bubbles-field-preview>`. Use `<k-tag>`, `<k-tags>` and `<k-tags-field-preview>` instead. [#7533](https://github.com/getkirby/kirby/pull/7533)
  - `<k-settings-view-button>` and `<k-status-view-button>`
- Changes in components
  - Radio input does not support the `reset` option anymore. Use the toggles input instead. [#7385](https://github.com/getkirby/kirby/pull/7385)
  - `<k-panel-menu>` requires its props to be passed explicitly instead of using `$panel.menu` itself. [#7381](https://github.com/getkirby/kirby/pull/7381)
  - Removed deprecated `model` prop from model views. Use the top-level props instead. [#7463](https://github.com/getkirby/kirby/pull/7463)
  - `<k-collection>` and `<k-items>`: `@select` events pass an array of selected IDs, not a single item object [#7516](https://github.com/getkirby/kirby/pull/7516)
  - `<k-structure-drawer>`: `prev`/`next` need a boolean as value, not an object anymore [#7790](https://github.com/getkirby/kirby/pull/7790)
  - `<k-item>`: `text` and `info` are escaped unless they are trusted HTML. Plugins that pass pre-escaped or raw HTML strings will see the markup as visible characters. Wrap the value with `$h()` (frontend) or in a `Kirby\Toolkit\HtmlString` / `$model->toSafeHtmlString()` (backend). [#8344](https://github.com/getkirby/kirby/pull/8344)
  - `<k-tags>` / `<k-picklist-input>`: an option `text` is only rendered as HTML if it is trusted HTML. Plain strings are escaped, and the search highlighting escapes untrusted text before adding its `<b>` marker. [#8344](https://github.com/getkirby/kirby/pull/8344)
  - `<k-box>`: the `html` slot binding has been removed. `<slot v-bind="{ text, html }">` is now `<slot v-bind="{ text }">`, where `text` is the resolved (possibly trusted) content. [#8344](https://github.com/getkirby/kirby/pull/8344)
  - `<k-models-field-preview>`: the `html` prop no longer defaults to `true`. Model items already carry trusted HTML, so the raw rendering is preserved without the flag. [#8344](https://github.com/getkirby/kirby/pull/8344)
  - `<k-installation-view>`: the DOM has been fully restructured [#7876](https://github.com/getkirby/kirby/pull/7876)
  - `<k-progress>`: the `<progress>` element is now wrapped in a `<label>` [#8330](https://github.com/getkirby/kirby/pull/8330)
  - The `filepicker`, `pagepicker` and `userpicker` fields use their full name in CSS classes: `.k-filepicker-field`, `.k-pagepicker-field`, `.k-userpicker-field`
  - `image`, `gallery` and `video` block previews receive the file UUID instead of full file item data
- View changes
  - Preview view: `versionId` parameter has been renamed to `mode` in view controller, buttons and Vue components [#7795](https://github.com/getkirby/kirby/pull/7795)
  - `k-login-view` and components have been refactored with breaking changes [#7840](https://github.com/getkirby/kirby/pull/7840)
- Request changes: the term “Fiber” is gone for backend requests
  - The `window.fiber` global has been replaced with `window.panelState`
  - The `X-Fiber` namespace in request headers has been replaced with `X-Panel`
  - Keys aren't prefixed with `$` anymore in request responses. Use without prefix. This also affects Panel plugins reloading the Panel by defining only specific keys to be reloaded. [#7365](https://github.com/getkirby/kirby/pull/7365)
- Helpers
  - Removed `$helper.isVueComponent()` [#7518](https://github.com/getkirby/kirby/pull/7518)
  - Removed `$helper.link.preview()`. Use `<k-pages-field-preview>` or `<k-files-field-preview>` to render a model preview, or `$helper.items("items/pages", id)` / `$helper.items("items/files", id)` if you need the raw data. [#7725](https://github.com/getkirby/kirby/pull/7725) [#8341](https://github.com/getkirby/kirby/pull/8341)
  - `$helper.string.sanitizeHTML()` is asynchronous now [#8342](https://github.com/getkirby/kirby/pull/8342)
  - Removed `$library.dayjs.merge()`
- Removed deprecated `panel.dialog.openComponent()` method [#7518](https://github.com/getkirby/kirby/pull/7518)
- `panel.notification.error()` no longer resolves nested errors in the state. Always throw Exceptions instead to create first-level error responses. [#7782](https://github.com/getkirby/kirby/pull/7782)
- Removed deprecated v3 CSS properties [#7825](https://github.com/getkirby/kirby/pull/7825)

#### Backend

Moved and replaced classes and methods:

| Before                                                                                                                                                                                      | Now                                                                                                                                                                                                                           | PR                                                                                                                                                             |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Kirby\Panel\ChangesDialog`                                                                                                                                                                 | `Kirby\Panel\Controller\Dialog\ChangesDialogController`                                                                                                                                                                       | [#7444](https://github.com/getkirby/kirby/pull/7444)                                                                                                           |
| `Kirby\Panel\PageCreateDialog`                                                                                                                                                              | `Kirby\Panel\Controller\Dialog\PageCreateDialogController`                                                                                                                                                                    | [#7446](https://github.com/getkirby/kirby/pull/7446) [#7466](https://github.com/getkirby/kirby/pull/7466) [#8449](https://github.com/getkirby/kirby/pull/8449) |
| `Kirby\Panel\View`                                                                                                                                                                          | `Kirby\Panel\Response\ViewResponse` (most of its functionality)                                                                                                                                                               | [#7407](https://github.com/getkirby/kirby/pull/7407)                                                                                                           |
| `Kirby\Panel\Document`                                                                                                                                                                      | `Kirby\Panel\Response\ViewDocumentResponse` (most of its functionality)                                                                                                                                                       | [#7407](https://github.com/getkirby/kirby/pull/7407)                                                                                                           |
| `Kirby\Panel\Dialog`                                                                                                                                                                        | `Kirby\Panel\Response\DialogResponse`                                                                                                                                                                                         | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Drawer`                                                                                                                                                                        | `Kirby\Panel\Response\DrawerResponse`                                                                                                                                                                                         | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Dropdown`                                                                                                                                                                      | `Kirby\Panel\Response\DropdownResponse`                                                                                                                                                                                       | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Json`                                                                                                                                                                          | `Kirby\Panel\Response\JsonResponse`                                                                                                                                                                                           | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Request`                                                                                                                                                                       | `Kirby\Panel\Response\RequestResponse`                                                                                                                                                                                        | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Search`                                                                                                                                                                        | `Kirby\Panel\Response\SearchResponse`                                                                                                                                                                                         | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\View::apply()`, `Kirby\Panel\View::applyGlobals()`, `Kirby\Panel\View::applyOnly()`, `Kirby\Panel\View::data()`, `Kirby\Panel\View::globals()`, `Kirby\Panel\View::searches()` | `Kirby\Panel\State`                                                                                                                                                                                                           | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Controller\Search`                                                                                                                                                             | `Kirby\Panel\Controller\Search\FilesSearchController`, `Kirby\Panel\Controller\Search\ModelsSearchController`, `Kirby\Panel\Controller\Search\PagesSearchController` or `Kirby\Panel\Controller\Search\UsersSearchController` | [#7423](https://github.com/getkirby/kirby/pull/7423)                                                                                                           |
| `Kirby\Panel\Controller\PageTree`                                                                                                                                                           | `Kirby\Panel\Controller\Request\PageTreeRequestController` or `Kirby\Panel\Controller\Request\PageTreeParentsRequestController`                                                                                               | [#7437](https://github.com/getkirby/kirby/pull/7437)                                                                                                           |
| `Kirby\Panel\Field::dialog()`                                                                                                                                                               | `Kirby\Panel\Controller\Dialog\FieldDialogController`                                                                                                                                                                         | [#7454](https://github.com/getkirby/kirby/pull/7454)                                                                                                           |
| `Kirby\Panel\Field::drawer()`                                                                                                                                                               | `Kirby\Panel\Controller\Drawer\FieldDrawerController`                                                                                                                                                                         | [#7454](https://github.com/getkirby/kirby/pull/7454)                                                                                                           |
| `Kirby\Panel\Ui\Buttons\*`                                                                                                                                                                  | `Kirby\Panel\Ui\Button\*`                                                                                                                                                                                                     | [#7459](https://github.com/getkirby/kirby/pull/7459)                                                                                                           |
| `Kirby\Panel\Ui\FilePreviews\*`                                                                                                                                                             | `Kirby\Panel\Ui\FilePreview\*`                                                                                                                                                                                                | [#7459](https://github.com/getkirby/kirby/pull/7459)                                                                                                           |
| `Kirby\Panel\Ui\Buttons\LanguagesDropdown`                                                                                                                                                  | `Kirby\Panel\Ui\Button\LanguagesButton`                                                                                                                                                                                       | [#7427](https://github.com/getkirby/kirby/pull/7427)                                                                                                           |
| `Kirby\Panel\Ui\Buttons\LanguagesDropdown::option()`, `Kirby\Panel\Ui\Buttons\LanguagesDropdown::options()`                                                                                 | `Kirby\Panel\Controller\Dropdown\LanguagesDropdownController`                                                                                                                                                                 | [#7427](https://github.com/getkirby/kirby/pull/7427)                                                                                                           |
| `Kirby\Panel\Home::isPanelUrl()`                                                                                                                                                            | `Kirby\Panel\Panel::isPanelUrl()`                                                                                                                                                                                             | [#7394](https://github.com/getkirby/kirby/pull/7394)                                                                                                           |
| `Kirby\Panel\Home::panelPath()`                                                                                                                                                             | `Kirby\Panel\Panel::path()`                                                                                                                                                                                                   | [#7394](https://github.com/getkirby/kirby/pull/7394)                                                                                                           |
| `Kirby\Panel\Panel::area()`                                                                                                                                                                 | `Kirby\Panel\Areas::area()`                                                                                                                                                                                                   | [#7391](https://github.com/getkirby/kirby/pull/7391)                                                                                                           |
| `Kirby\Panel\Panel::buttons()`                                                                                                                                                              | `Kirby\Panel\Areas::buttons()`                                                                                                                                                                                                | [#7391](https://github.com/getkirby/kirby/pull/7391)                                                                                                           |
| `Kirby\Panel\Panel::firewall()`, `Kirby\Panel\Panel::hasAccess()`                                                                                                                           | `Kirby\Panel\Panel::access()->area()`                                                                                                                                                                                         | [#7383](https://github.com/getkirby/kirby/pull/7383)                                                                                                           |
| `Kirby\Panel\Panel::isFiberRequest()`                                                                                                                                                       | `Kirby\Panel\Panel::isStateRequest()`                                                                                                                                                                                         |                                                                                                                                                                |
| `Kirby\Panel\Panel::routesForViews()`                                                                                                                                                       | `Kirby\Panel\Routes\ViewRoutes`                                                                                                                                                                                               | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Panel::routesForSearches()`                                                                                                                                                    | `Kirby\Panel\Routes\SearchRoutes`                                                                                                                                                                                             | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Panel::routesForDialogs()`                                                                                                                                                     | `Kirby\Panel\Routes\DialogRoutes`                                                                                                                                                                                             | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Panel::routesForDrawers()`                                                                                                                                                     | `Kirby\Panel\Routes\DrawerRoutes`                                                                                                                                                                                             | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Panel::routesForDropdowns()`                                                                                                                                                   | `Kirby\Panel\Routes\DropdownRoutes`                                                                                                                                                                                           | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |
| `Kirby\Panel\Panel::routesForRequests()`                                                                                                                                                    | `Kirby\Panel\Routes\RequestRoutes`                                                                                                                                                                                            | [#7386](https://github.com/getkirby/kirby/pull/7386)                                                                                                           |

- Removed methods
  - `Kirby\Panel\Model::isDisabledDropdownOption()` [#7425](https://github.com/getkirby/kirby/pull/7425)
  - `Kirby\Panel\Model::toPrevNextLink()` and the deprecated `Kirby\Panel\Model::content()` (also on `Kirby\Panel\Site`, `Kirby\Panel\Page`, `Kirby\Panel\File` and `Kirby\Panel\User`) [#7480](https://github.com/getkirby/kirby/pull/7480)
  - `fileResponse()`/`toFiles()`, `pageResponse()`/`toPages()` and `userResponse()`/`toUsers()` of the former `files`, `pages` and `users` field definitions. Use `Kirby\Form\Field\ModelPickerField::toItem()` and `Kirby\Form\Field\ModelPickerField::toFormValue()` instead. [#7528](https://github.com/getkirby/kirby/pull/7528)
- Changed methods
  - `Kirby\Panel\Controller\Dialog\PageCreateDialogController` is initiated with a parent model (page/site) and the available blueprints [#7466](https://github.com/getkirby/kirby/pull/7466) [#8449](https://github.com/getkirby/kirby/pull/8449)
  - `Kirby\Panel\Panel` and `Kirby\Panel\Home` and their methods are non-static. Instead of `Kirby\Panel\Panel::router($path)`, use `$panel->router()?->call($path)`. [#7394](https://github.com/getkirby/kirby/pull/7394) [#7407](https://github.com/getkirby/kirby/pull/7407) [#7409](https://github.com/getkirby/kirby/pull/7409)
  - `Kirby\Panel\Panel::areas()` returns a `Kirby\Panel\Areas` collection of `Kirby\Panel\Area` objects instead of an array. The `Kirby\Panel\Menu` class has been rewritten to use them. [#7391](https://github.com/getkirby/kirby/pull/7391) [#7406](https://github.com/getkirby/kirby/pull/7406)
  - The argument of the `Kirby\Panel\Ui\Button\ViewButtons` constructor has changed [#7462](https://github.com/getkirby/kirby/pull/7462)
  - `Kirby\Panel\Ui\Button\ViewButtons::view()` accepts a `Kirby\Panel\Controller\View\ModelViewController` as `$view` argument, instead of a `Kirby\Panel\Model` [#7480](https://github.com/getkirby/kirby/pull/7480)
  - `Kirby\Panel\Ui\Button\VersionsButton::__construct()`: the `$versionId` parameter has been renamed to `$mode` [#7548](https://github.com/getkirby/kirby/pull/7548)
  - `Kirby\Panel\Assets::icons()` returns the URL of the icon sprite instead of its markup [#8445](https://github.com/getkirby/kirby/pull/8445)
- Registering field dialogs or drawers without a closure throws an exception [#7512](https://github.com/getkirby/kirby/pull/7512)

### Auth

- Custom auth challenges must be rewritten based on the redesigned `Kirby\Auth\Challenge` class (instead of `Kirby\Cms\Auth\Challenge`) [#8044](https://github.com/getkirby/kirby/pull/8044)
- `Kirby\Auth\Status` replaces `Kirby\Cms\Auth\Status` everywhere, e.g. as return value of `$auth->status()`. `Kirby\Cms\Auth\Status::is()` and `Kirby\Cms\Auth\Status::clone()` have no equivalent in the new class.
- `Kirby\Auth\Auth::validatePassword()` and `Kirby\Auth\Auth::verifyChallenge()` return `Kirby\Cms\User|null`
- `Kirby\Cms\Find::user()` throws `Kirby\Exception\UserNotFoundException`
- Removed `Kirby\Cms\Auth::ipHash()`. Use `$visitor->ip(hash: true)` instead.
- Auth session format changed: `kirby.challenge.code` → `kirby.challenge.data`. In-flight challenges across the Kirby upgrade won't verify.
- Removed the translation strings `login.code.label.login` and `login.code.label.password-reset`
- Removed the `login` extension type of `panel.plugin()` (`panel.plugins.login`). Register your custom login UI as a normal `component` instead and reference it in the `Kirby\Auth\Method::form()` or `Kirby\Auth\Challenge::form()` method of your custom auth method or challenge. [#8045](https://github.com/getkirby/kirby/pull/8045)
- Removed `<k-login-form>`, `<k-login-code-form>` and the deprecated aliases `<k-login>` and `<k-login-code>` [#8045](https://github.com/getkirby/kirby/pull/8045)
- The Panel login routes are multi-step now: `login/method/...` and `login/challenge/...`
- The TOTP enable/disable dialogs (`Kirby\Panel\UserTotpEnableDialog`, `Kirby\Panel\UserTotpDisableDialog` and `<k-totp-dialog>`) have been replaced by `Kirby\Panel\Controller\Drawer\UserTotpDrawerController` and `<k-user-totp-drawer>` [#7445](https://github.com/getkirby/kirby/pull/7445)

---

## ☠️ Deprecated

Deprecated classes, methods, components and options keep working for now, but will be removed in a future major version.

### Core

#### General

| Deprecated                                                                                                            | Use instead                                                                             | PR                                                   |
| --------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------------------- |
| Spyc YAML handler (`'yaml.handler' => 'spyc'`)                                                                        | Symfony YAML (default)                                                                  | [#7530](https://github.com/getkirby/kirby/pull/7530) |
| `Kirby\Cms\Blueprint`                                                                                                 | `Kirby\Blueprint\Blueprint`                                                             | [#7787](https://github.com/getkirby/kirby/pull/7787) |
| `Kirby\Cms\FileBlueprint`                                                                                             | `Kirby\Blueprint\FileBlueprint`                                                         | [#7787](https://github.com/getkirby/kirby/pull/7787) |
| `Kirby\Cms\PageBlueprint`                                                                                             | `Kirby\Blueprint\PageBlueprint`                                                         | [#7787](https://github.com/getkirby/kirby/pull/7787) |
| `Kirby\Cms\SiteBlueprint`                                                                                             | `Kirby\Blueprint\SiteBlueprint`                                                         | [#7787](https://github.com/getkirby/kirby/pull/7787) |
| `Kirby\Cms\UserBlueprint`                                                                                             | `Kirby\Blueprint\UserBlueprint`                                                         | [#7787](https://github.com/getkirby/kirby/pull/7787) |
| `files`, `pages` and `users` field types                                                                              | `filepicker`, `pagepicker` and `userpicker` (aliases removed in the next major release) | [#8449](https://github.com/getkirby/kirby/pull/8449) |
| `Kirby\Cms\PageRules`, `Kirby\Cms\FileRules`, `Kirby\Cms\UserRules`, `Kirby\Cms\SiteRules`, `Kirby\Cms\LanguageRules` | `$model->guards()`                                                                      | [#8364](https://github.com/getkirby/kirby/pull/8364) |
| `$model->permissions()` and the permission classes behind it                                                          | `$model->guards()`                                                                      | [#8364](https://github.com/getkirby/kirby/pull/8364) |
| `Kirby\Field\FieldOptions`                                                                                            | `Kirby\Form\FieldOptions`                                                               |                                                      |

#### Auth

| Deprecated                                                          | Use instead                                                                                      | PR  |
| ------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ | --- |
| `Kirby\Cms\Auth`                                                    | `Kirby\Auth\Auth`                                                                                |     |
| `Kirby\Cms\Auth\Status`                                             | `Kirby\Auth\Status`                                                                              |     |
| `Kirby\Auth\Auth::login2fa()`                                       | `Kirby\Auth\Auth::authenticate()`                                                                |     |
| `Kirby\Auth\Auth::enabledChallenges()`                              | `Kirby\Auth\Challenges::enabled()` via `$kirby->auth()->challenges()->enabled()`                 |     |
| `Kirby\Auth\Auth::isBlocked()`                                      | `Kirby\Auth\Limits::isBlocked()` via `$kirby->auth()->limits()->isBlocked()`                     |     |
| `Kirby\Auth\Auth::log()`                                            | `Kirby\Auth\Limits::log()` via `$kirby->auth()->limits()->log()`                                 |     |
| `Kirby\Auth\Auth::logfile()`                                        | `Kirby\Auth\Limits::file()` via `$kirby->auth()->limits()->file()`                               |     |
| `Kirby\Auth\Auth::track()`                                          | `Kirby\Auth\Limits::track()` via `$kirby->auth()->limits()->track()`                             |     |
| `Kirby\Cms\System::loginMethods()`                                  | `Kirby\Auth\Methods::enabled()` via `$kirby->auth()->methods()->enabled()`                       |     |
| `Kirby\Cms\System::is2FA()`                                         | `Kirby\Auth\Methods::hasAnyRequiring2FA()` via `$kirby->auth()->methods()->hasAnyRequiring2FA()` |     |
| `Kirby\Cms\System::is2FAWithTOTP()`                                 | `Kirby\Auth\Methods::hasAnyRequiring2FA()` together with `Kirby\Auth\Challenges::enabled()`      |     |
| `Kirby\Cms\User::changeTotp()`, `Kirby\Cms\UserRules::changeTotp()` | `Kirby\Cms\User::changeSecret('totp', …)`                                                        |     |

#### Sessions

| Deprecated                                  | Use instead                                            | PR                                                   |
| ------------------------------------------- | ------------------------------------------------------ | ---------------------------------------------------- |
| `Kirby\Session\SessionData`                 | `Kirby\Session\Data`                                   | [#8247](https://github.com/getkirby/kirby/pull/8247) |
| `Kirby\Session\SessionStore`                | `Kirby\Session\Store`                                  | [#8247](https://github.com/getkirby/kirby/pull/8247) |
| `Kirby\Session\FileSessionStore`            | `Kirby\Session\FileStore`                              | [#8247](https://github.com/getkirby/kirby/pull/8247) |
| `Kirby\Cms\App::sessionHandler()`           | `Kirby\Cms\App::sessions()`                            | [#8248](https://github.com/getkirby/kirby/pull/8248) |
| `Kirby\Session\Sessions::createManually()`  | `Kirby\Session\Sessions::create(['mode' => 'manual'])` | [#8248](https://github.com/getkirby/kirby/pull/8248) |
| `Kirby\Session\Sessions::getManually()`     | `Kirby\Session\Sessions::find()`                       | [#8248](https://github.com/getkirby/kirby/pull/8248) |
| `Kirby\Session\Sessions::currentDetected()` | `Kirby\Session\Sessions::detect()`                     | [#8248](https://github.com/getkirby/kirby/pull/8248) |
| `Kirby\Session\Sessions::updateCache()`     | `Kirby\Session\Sessions::track()`                      | [#8248](https://github.com/getkirby/kirby/pull/8248) |
| `Kirby\Session\Sessions::cookieDomain()`    | `Kirby\Session\Sessions::cookie()->domain()`           | [#8230](https://github.com/getkirby/kirby/pull/8230) |
| `Kirby\Session\Sessions::cookieName()`      | `Kirby\Session\Sessions::cookie()->name()`             | [#8230](https://github.com/getkirby/kirby/pull/8230) |

### Panel

#### Frontend

| Deprecated                                                                                      | Use instead                                                                      | PR                                                   |
| ----------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- | ---------------------------------------------------- |
| `<k-models-dialog>`, `<k-files-dialog>`, `<k-users-dialog>`                                     | `<k-model-picker-dialog>`                                                        |                                                      |
| `<k-pages-dialog>`                                                                              | `<k-page-picker-dialog>`                                                         |                                                      |
| `<k-text :html="foo" />`                                                                        | `<k-text :text="$h(foo)" />` or `<k-text :text="foo" />` to escape the value     | [#8344](https://github.com/getkirby/kirby/pull/8344) |
| `<k-box :html="true" :text="foo" />`                                                            | `<k-box :text="$h(foo)" />`                                                      | [#8344](https://github.com/getkirby/kirby/pull/8344) |
| `<k-tag :html="true" :text="foo" />`                                                            | `<k-tag :text="$h(foo)" />`                                                      | [#8344](https://github.com/getkirby/kirby/pull/8344) |
| `html` prop of `<k-tag-field-preview>`, `<k-tags-field-preview>` and `<k-models-field-preview>` | not needed: values and option texts already carry trusted HTML where appropriate | [#8344](https://github.com/getkirby/kirby/pull/8344) |
| `$library.dayjs.interpret()`                                                                    | `$library.dayjs.parse()`                                                         | [#8328](https://github.com/getkirby/kirby/pull/8328) |

#### Backend

| Deprecated                            | Use instead                                                                                                             | PR                                                   |
| ------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------- |
| `Kirby\Panel\File::dropdown()`        | `Kirby\Panel\Controller\Dropdown\FileSettingsDropdownController`                                                        | [#7425](https://github.com/getkirby/kirby/pull/7425) |
| `Kirby\Panel\Page::dropdown()`        | `Kirby\Panel\Controller\Dropdown\PageSettingsDropdownController`                                                        | [#7425](https://github.com/getkirby/kirby/pull/7425) |
| `Kirby\Panel\User::dropdown()`        | `Kirby\Panel\Controller\Dropdown\UserSettingsDropdownController`                                                        | [#7425](https://github.com/getkirby/kirby/pull/7425) |
| `Kirby\Panel\Model::breadcrumb()`     | `Kirby\Panel\Controller\View\ModelViewController::breadcrumb()`                                                         | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| `Kirby\Panel\Model::buttons()`        | `Kirby\Panel\Controller\View\ModelViewController::buttons()`                                                            | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| `Kirby\Panel\Model::prevNext()`       | `Kirby\Panel\Controller\View\ModelViewController::prev()` and `Kirby\Panel\Controller\View\ModelViewController::next()` | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| `Kirby\Panel\Model::props()`          | `Kirby\Panel\Controller\View\ModelViewController::props()`                                                              | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| `Kirby\Panel\Model::versions()`       | `Kirby\Panel\Controller\View\ModelViewController::versions()`                                                           | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| `Kirby\Panel\Model::view()`           | `Kirby\Panel\Controller\View\ModelViewController::load()`                                                               | [#7480](https://github.com/getkirby/kirby/pull/7480) |
| Global `pages/create` dialog endpoint | `create` dialog endpoint of a `pagelist` field                                                                          | [#7466](https://github.com/getkirby/kirby/pull/7466) |

---

## ♻️ Refactored

### Core

- Moved default field methods into new `Kirby\Content\FieldMethods` trait used by `Kirby\Content\Field` [#7082](https://github.com/getkirby/kirby/pull/7082)
- Implemented default validators as regular class methods of `Kirby\Toolkit\V` [#7608](https://github.com/getkirby/kirby/pull/7608)
- Use `json_validate` for `Kirby\Toolkit\V::json()` [#7538](https://github.com/getkirby/kirby/pull/7538)
- Moved permalink logic to new `Kirby\Uuid\Permalink` class [#7545](https://github.com/getkirby/kirby/pull/7545)
- New `Kirby\Uuid\Uuid::from(string $uuid)` method for creating an Uuid object from a UUID string. `Kirby\Uuid\Uuid::for()` remains to create a Uuid object for a model object. [#7544](https://github.com/getkirby/kirby/pull/7544)
- New `Kirby\Blueprint\AcceptRules` class and `Kirby\Blueprint\Blueprint::acceptRules()` method for the code that checks for accepted files. This could later be extended to also check for accepted subpages. [#7829](https://github.com/getkirby/kirby/pull/7829)
- Moved all blueprint normalization logic into a new `Kirby\Blueprint\Normalizer` class [#8387](https://github.com/getkirby/kirby/pull/8387)
- The default site blueprint uses a `pagelist` field instead of a `pages` section [#8405](https://github.com/getkirby/kirby/pull/8405) [#8449](https://github.com/getkirby/kirby/pull/8449)
- New `Kirby\Cms\App\Resolver` class, which handles all the logic of `Kirby\Cms\App::resolve()` [#8277](https://github.com/getkirby/kirby/pull/8277)
- `Kirby\Cms\ModelWithContent::errors()` only collects errors from fields [#8449](https://github.com/getkirby/kirby/pull/8449)
- `Kirby\Exception\Exception::$prefix` is a class constant now [#8459](https://github.com/getkirby/kirby/pull/8459)
- Query package: switched from `private` to `protected` methods; fixed the types of the `Kirby\Query\Query::filterQuery()` param and return value [#8464](https://github.com/getkirby/kirby/pull/8464)
- New `Kirby\Reflection\Props` class and `#[Kirby\Reflection\Attributes\Derived]` attribute [#8457](https://github.com/getkirby/kirby/pull/8457)
- PHP type hints have been added to all class constants [#7536](https://github.com/getkirby/kirby/pull/7536)

### Forms

Form fields have been fully refactored as PHP classes instead of the previous array definitions [#8399](https://github.com/getkirby/kirby/pull/8399):

- All fields use named props instead of `$props` arrays. `Kirby\Form\Field::factory()` creates field instances from a `$props` array.
- Removed `default` from field props passed to Panel [#7789](https://github.com/getkirby/kirby/pull/7789)
- Moved `Kirby\Field\FieldOptions` class to `Kirby\Form\FieldOptions`
- The slug field gets the 'slug' translation string as default label [#7846](https://github.com/getkirby/kirby/pull/7846)

#### Field foundation classes

All fields extend `Kirby\Form\Field` or one of these abstract classes:

- `Kirby\Form\Field\DisplayField` for all fields without a value (e.g. `headline`, `info`, `stats`)
- `Kirby\Form\Field\ValueField` for all fields with a value
- `Kirby\Form\Field\InputField` for all fields with user input
- `Kirby\Form\Field\DateTimeField` for all date and time fields
- `Kirby\Form\Field\OptionField` for fields with a single option value
- `Kirby\Form\Field\OptionsField` for fields with multiple options value
- `Kirby\Form\Field\StringField` for text, textarea and potentially more string value fields
- `Kirby\Form\Field\ProseMirrorField` for the `list` and `writer` fields [#8493](https://github.com/getkirby/kirby/pull/8493)
- `Kirby\Form\Field\ModelPickerField` for all picker fields
- `Kirby\Form\Field\ModelListField` for the `filelist` and `pagelist` fields [#8449](https://github.com/getkirby/kirby/pull/8449)

#### Field classes

Every core field type has its own class:

`Kirby\Form\Field\BlocksField`, `Kirby\Form\Field\ButtonsField`, `Kirby\Form\Field\CheckboxesField`, `Kirby\Form\Field\ColorField`, `Kirby\Form\Field\DateField`, `Kirby\Form\Field\EmailField`, `Kirby\Form\Field\EntriesField`, `Kirby\Form\Field\FileListField`, `Kirby\Form\Field\FilePickerField`, `Kirby\Form\Field\GapField`, `Kirby\Form\Field\HeadlineField`, `Kirby\Form\Field\HiddenField`, `Kirby\Form\Field\InfoField`, `Kirby\Form\Field\LayoutField`, `Kirby\Form\Field\LineField`, `Kirby\Form\Field\LinkField`, `Kirby\Form\Field\ListField`, `Kirby\Form\Field\MultiselectField`, `Kirby\Form\Field\NumberField`, `Kirby\Form\Field\ObjectField`, `Kirby\Form\Field\PageListField`, `Kirby\Form\Field\PagePickerField`, `Kirby\Form\Field\PasswordField`, `Kirby\Form\Field\RadioField`, `Kirby\Form\Field\RangeField`, `Kirby\Form\Field\SelectField`, `Kirby\Form\Field\SlugField`, `Kirby\Form\Field\StatsField`, `Kirby\Form\Field\StructureField`, `Kirby\Form\Field\TagsField`, `Kirby\Form\Field\TelField`, `Kirby\Form\Field\TextField`, `Kirby\Form\Field\TextareaField`, `Kirby\Form\Field\TimeField`, `Kirby\Form\Field\ToggleField`, `Kirby\Form\Field\TogglesField`, `Kirby\Form\Field\UrlField`, `Kirby\Form\Field\UserPickerField` and `Kirby\Form\Field\WriterField`

#### Field mixins

Shared behavior has been extracted into reusable mixins, such as:

`Kirby\Form\Mixin\Autocomplete`, `Kirby\Form\Mixin\Batch`, `Kirby\Form\Mixin\Columns`, `Kirby\Form\Mixin\Converter`, `Kirby\Form\Mixin\Counter`, `Kirby\Form\Mixin\DefaultValue`, `Kirby\Form\Mixin\Disabled`, `Kirby\Form\Mixin\Duplicate`, `Kirby\Form\Mixin\Fields`, `Kirby\Form\Mixin\Font`, `Kirby\Form\Mixin\ItemLayout`, `Kirby\Form\Mixin\ItemSize`, `Kirby\Form\Mixin\Limit`, `Kirby\Form\Mixin\Maxlength`, `Kirby\Form\Mixin\Minlength`, `Kirby\Form\Mixin\Name`, `Kirby\Form\Mixin\Options`, `Kirby\Form\Mixin\Pattern`, `Kirby\Form\Mixin\Prepend`, `Kirby\Form\Mixin\Pretty`, `Kirby\Form\Mixin\Required`, `Kirby\Form\Mixin\Separator`, `Kirby\Form\Mixin\Siblings`, `Kirby\Form\Mixin\Sortable`, `Kirby\Form\Mixin\SortBy`, `Kirby\Form\Mixin\Spellcheck`, `Kirby\Form\Mixin\TableColumns`, `Kirby\Form\Mixin\Text`, `Kirby\Form\Mixin\Theme` and `Kirby\Form\Mixin\Upload` [#8457](https://github.com/getkirby/kirby/pull/8457)

### Panel

#### Frontend

- Files, pages and users field previews now support IDs alongside item objects as value and will fetch the item data for these IDs automatically. [#7723](https://github.com/getkirby/kirby/pull/7723)
- Picker fields use the new picker dialogs and fetch their item data dynamically from the new `items` field API endpoint
- `<k-image-frame>` can receive a file ID/UUID via new `file` prop [#7756](https://github.com/getkirby/kirby/pull/7756)
- Removed the `panel.vue.compiler` option. It isn't needed with Vue 3 anymore. [#7788](https://github.com/getkirby/kirby/pull/7788)
- New `<k-panel-notifications>` [#7797](https://github.com/getkirby/kirby/pull/7797)
- `<k-button>` applies all `aria-*` attributes [#7801](https://github.com/getkirby/kirby/pull/7801)
- The DOM structure of `<k-item>` has changed: an additional `.k-item-box` wrapper div was added [#7361](https://github.com/getkirby/kirby/pull/7361)
- Removed `light-dark()` polyfill [#7902](https://github.com/getkirby/kirby/pull/7902)
- `<k-breadcrumb>` has a new responsive behavior (backed by `<k-collapsible>`) [#7901](https://github.com/getkirby/kirby/pull/7901)
- The `filelist` and `pagelist` field components share a base component [#8449](https://github.com/getkirby/kirby/pull/8449)

#### Backend

- Refactored the Panel namespace as non-static classes, with the new `Kirby\Panel\Areas`, `Kirby\Panel\Area`, `Kirby\Panel\Access` and `Kirby\Panel\Router` classes [#7383](https://github.com/getkirby/kirby/pull/7383) [#7386](https://github.com/getkirby/kirby/pull/7386) [#7391](https://github.com/getkirby/kirby/pull/7391) [#7394](https://github.com/getkirby/kirby/pull/7394) [#7406](https://github.com/getkirby/kirby/pull/7406) [#7407](https://github.com/getkirby/kirby/pull/7407)
- Refactored all Panel dialogs, drawers, dropdowns, requests and views (from `kirby/config/areas`) as controller classes (`Kirby\Panel\Controller`) [#7425](https://github.com/getkirby/kirby/pull/7425) [#7427](https://github.com/getkirby/kirby/pull/7427) [#7437](https://github.com/getkirby/kirby/pull/7437) [#7439](https://github.com/getkirby/kirby/pull/7439) [#7440](https://github.com/getkirby/kirby/pull/7440) [#7448](https://github.com/getkirby/kirby/pull/7448) [#7451](https://github.com/getkirby/kirby/pull/7451) [#7452](https://github.com/getkirby/kirby/pull/7452) [#7453](https://github.com/getkirby/kirby/pull/7453) [#7455](https://github.com/getkirby/kirby/pull/7455) [#7469](https://github.com/getkirby/kirby/pull/7469) [#7471](https://github.com/getkirby/kirby/pull/7471) [#7474](https://github.com/getkirby/kirby/pull/7474) [#7475](https://github.com/getkirby/kirby/pull/7475) [#7479](https://github.com/getkirby/kirby/pull/7479) [#7480](https://github.com/getkirby/kirby/pull/7480) [#7482](https://github.com/getkirby/kirby/pull/7482)
  - Field dialogs and drawers are dialog and drawer controllers (using a shared `Kirby\Panel\Controller\FieldController` trait) [#7454](https://github.com/getkirby/kirby/pull/7454)
- Aligned the namespace names within `Kirby\Panel\Ui` [#7459](https://github.com/getkirby/kirby/pull/7459)
  - `Kirby\Panel\Ui\Button\ViewButtons` supports passing an array of `Kirby\Panel\Ui\Button\ViewButton` objects [#7462](https://github.com/getkirby/kirby/pull/7462)
  - `Kirby\Panel\Ui\Stat` has an optional `$model` property [#7420](https://github.com/getkirby/kirby/pull/7420)
- Use `Kirby\Panel\Ui\Item\ModelItem` classes for picker dialogs [#7753](https://github.com/getkirby/kirby/pull/7753)
- In `layout: table`, `Kirby\Panel\Ui\Item\ModelItem` returns the item's `text` and `info` as plain, unescaped strings, because table cells render them as text rather than HTML [#8439](https://github.com/getkirby/kirby/pull/8439)
- New `create` dialog endpoint for `pagelist` fields [#7466](https://github.com/getkirby/kirby/pull/7466) [#8449](https://github.com/getkirby/kirby/pull/8449)
- The `Kirby\Panel\Field` class uses the new classes and improvements above to replace its logic. [#7846](https://github.com/getkirby/kirby/pull/7846)
- New `Kirby\Panel\Panel::assets()` method [#8445](https://github.com/getkirby/kirby/pull/8445)
- New special Panel field classes: `Kirby\Panel\Form\Field\FilePositionField`, `Kirby\Panel\Form\Field\PagePositionField`, `Kirby\Panel\Form\Field\RoleField`, `Kirby\Panel\Form\Field\TemplateField`, `Kirby\Panel\Form\Field\TitleField`, `Kirby\Panel\Form\Field\TranslationField` and `Kirby\Panel\Form\Field\UsernameField` [#7846](https://github.com/getkirby/kirby/pull/7846)

### Tests

- Use stubs instead of mocks in tests [#7804](https://github.com/getkirby/kirby/pull/7804)
- Better locale set/reset [#7805](https://github.com/getkirby/kirby/pull/7805)

### TypeScript migration

- Migrated `this.$panel` and all its modules to TypeScript [#8056](https://github.com/getkirby/kirby/pull/8056) [#8076](https://github.com/getkirby/kirby/pull/8076) [#8079](https://github.com/getkirby/kirby/pull/8079) [#8115](https://github.com/getkirby/kirby/pull/8115)
- Refactored the Panel `api` JavaScript as a class in TypeScript [#8075](https://github.com/getkirby/kirby/pull/8075) [#8121](https://github.com/getkirby/kirby/pull/8121)
- Migrated `preserveDataAttrs` and `preserveListeners` to TypeScript [#8118](https://github.com/getkirby/kirby/pull/8118)
- Migrated the Editor to TypeScript [#8101](https://github.com/getkirby/kirby/pull/8101) [#8102](https://github.com/getkirby/kirby/pull/8102) [#8105](https://github.com/getkirby/kirby/pull/8105) [#8106](https://github.com/getkirby/kirby/pull/8106) [#8107](https://github.com/getkirby/kirby/pull/8107) [#8108](https://github.com/getkirby/kirby/pull/8108) [#8110](https://github.com/getkirby/kirby/pull/8110) [#8111](https://github.com/getkirby/kirby/pull/8111) [#8112](https://github.com/getkirby/kirby/pull/8112) [#8114](https://github.com/getkirby/kirby/pull/8114)
- Migrated `$helper.writer` to TypeScript [#8321](https://github.com/getkirby/kirby/pull/8321)
- `panel.system.csrf` and `panel.csrf.title` are always strings now (empty strings when not set) [#8119](https://github.com/getkirby/kirby/pull/8119)

### Auth

- `Kirby\Cms\Auth` classes move into new `Kirby\Auth` namespace:
  - New classes: `Kirby\Auth\Auth`, `Kirby\Auth\Csrf`, `Kirby\Auth\Limits`, `Kirby\Auth\Pending`, `Kirby\Auth\Status`, `Kirby\Auth\User` and the `Kirby\Auth\State` enum
  - New auth methods: `Kirby\Auth\Methods`, `Kirby\Auth\Method`, `Kirby\Auth\Method\BasicAuthMethod`, `Kirby\Auth\Method\CodeMethod`, `Kirby\Auth\Method\PasswordMethod`, `Kirby\Auth\Method\PasswordResetMethod` and `Kirby\Auth\Method\WebauthnMethod`
  - New auth challenges: `Kirby\Auth\Challenges`, `Kirby\Auth\Challenge`, `Kirby\Auth\Challenge\EmailChallenge`, `Kirby\Auth\Challenge\TotpChallenge` and `Kirby\Auth\Challenge\WebauthnChallenge`
  - New exceptions: `Kirby\Auth\Exception\ChallengeTimeoutException`, `Kirby\Auth\Exception\LoginNotPermittedException`, `Kirby\Auth\Exception\RateLimitException` and `Kirby\Exception\UserNotFoundException`
- Auth challenges have been changed from static classes to instance-based objects
- Panel login decomposed from two fixed forms into small composable components and a thin `LoginView.vue` shell driven by the backend.
- New `Kirby\Cms\User::changeSecret()` / `Kirby\Cms\UserRules::changeSecret()` generalise the old TOTP-specific secret writing.
- New Panel routes: `/login/(type)/(name)`, e.g. `/login/method/password` [#8045](https://github.com/getkirby/kirby/pull/8045)

### Session

- New `Kirby\Session\Token` class [#8228](https://github.com/getkirby/kirby/pull/8228)
- Removed `Kirby\Session\Session::__call()` and replaced it with non-magic methods [#8229](https://github.com/getkirby/kirby/pull/8229)
- New `Kirby\Session\Cookie` and `Kirby\Session\Header` classes [#8230](https://github.com/getkirby/kirby/pull/8230)
- `Kirby\Session` package tweaks [#8177](https://github.com/getkirby/kirby/pull/8177)
- DRY-ed exceptions thrown in the `Kirby\Session` package

### More

- Use trait constant for `Kirby\Filesystem\IsFile` detection [#8135](https://github.com/getkirby/kirby/pull/8135)

---

## 🧹 Housekeeping

- Upgraded CI setup [#7738](https://github.com/getkirby/kirby/pull/7738)
  - Upgraded to PHPUnit 12 [#7681](https://github.com/getkirby/kirby/pull/7681)
  - Removed `phpmd` from our CI [#7536](https://github.com/getkirby/kirby/pull/7536)
  - Using ParaTest to run PHPUnit tests in parallel [#7803](https://github.com/getkirby/kirby/pull/7803)
  - Cache PHPUnit results
  - Raised Psalm to error level 4 [#7932](https://github.com/getkirby/kirby/pull/7932) [#8170](https://github.com/getkirby/kirby/pull/8170) [#8223](https://github.com/getkirby/kirby/pull/8223) [#8224](https://github.com/getkirby/kirby/pull/8224) [#8225](https://github.com/getkirby/kirby/pull/8225) [#8227](https://github.com/getkirby/kirby/pull/8227)
  - Added ESLint rules for accessibility in Vue components [#8330](https://github.com/getkirby/kirby/pull/8330)
- Improved testing setup
  - Drastically improved the speed of PHPUnit tests
  - Frontend component tests via Vue Test Utils [#7972](https://github.com/getkirby/kirby/pull/7972)
  - Started tracking frontend unit test coverage [#8188](https://github.com/getkirby/kirby/pull/8188)
  - Added `Kirby\Panel\TestCase::setRequest()` helper method [#7440](https://github.com/getkirby/kirby/pull/7440)
  - Memcached tests are skipped locally if Memcached is not installed [#8360](https://github.com/getkirby/kirby/pull/8360)
- Upgraded to Vite 8 [#8037](https://github.com/getkirby/kirby/pull/8037)
- Panel browser support is defined in one place: `panel/.browserslistrc` [#8450](https://github.com/getkirby/kirby/pull/8450)
- Simplified PHP class docblocks [#7944](https://github.com/getkirby/kirby/pull/7944) and added docblocks to frontend files [#8220](https://github.com/getkirby/kirby/pull/8220)
  - Docblocks use short class names where the class has been imported via `use` [#8338](https://github.com/getkirby/kirby/pull/8338)
  - Started to remove types from docblocks that only repeat the native PHP type hints [#8250](https://github.com/getkirby/kirby/pull/8250)
