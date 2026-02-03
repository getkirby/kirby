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

### New template stacks

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
- New `k-validation-issues` component to list various issues in fields after a form has been submitted. This will be used in our new error dialogs, but can also be used as a stand-alone component in other parts of the panel. [#7775](https://github.com/getkirby/kirby/pull/7775)
    <img width="594" height="395" alt="image" src="https://github.com/user-attachments/assets/94122230-af87-4404-a5bd-dc896d892446" />    
- New `k-request-error-dialog` component [#7782](https://github.com/getkirby/kirby/pull/7782)  
    <img width="686" height="829" alt="image" src="https://github.com/user-attachments/assets/20b390a4-feb1-4a1e-9fc4-c7cbd04ed3a5" />    
- New `<k-validation-error-dialog>` to improve the readability of field validation problems. [#7785](https://github.com/getkirby/kirby/pull/7785)
    <img width="695" height="336" alt="image" src="https://github.com/user-attachments/assets/65404617-6957-4c6e-a30e-4fe1e2575786" />
    
### More

- New `picker` option for `files`, `pages` and `users` field to customize the picker dialogs (e.g. `size`, `layout`) [#7687](https://github.com/getkirby/kirby/pull/7687)

## ✨ Enhancements

### Core

- Move field methods to `Kirby\Content\Field` to improve IDE support for field methods [#7082](https://github.com/getkirby/kirby/pull/7082)

### Panel

#### Frontend

- Panel: Migrated from Vue 2 to Vue 3 [#7104](https://github.com/getkirby/kirby/pull/7104)
- Values of disabled Panel fields can be selected [#7581](https://github.com/getkirby/kirby/pull/7581)
- Added a dropzone to dialogs and drawers that becomes active when a `@drop` listener is added to the component [#7520](https://github.com/getkirby/kirby/pull/7520)
- `k-item` has a new `selected` prop to control it's selection checked state and `seelctmode` prop to define whether to select single or multiple items [#7516](https://github.com/getkirby/kirby/pull/7516)
- `k-collection`/`k-items` can receive a list of `selected` items to control selection status from parent [#7516](https://github.com/getkirby/kirby/pull/7516)
- New `k-models-picker-dialog` components (`k-pages-picker-dialog`, `k-files-picker-dialog`, `k-users-picker-dialog`) [#7529](https://github.com/getkirby/kirby/pull/7529)
- New `k-video-frame` component [#7755](https://github.com/getkirby/kirby/pull/7755)

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

---

## 🐛 Bug fixes

### Core

- Fixed auto-closing open snippets at the end of a nested snippet (thx to [@JojOatXGME](https://github.com/JojOatXGME)) [#7567](https://github.com/getkirby/kirby/issues/7567)
- `Kirby\Filesystem\Exif` now supports arrays for `ISOSpeedRatings` [#7569](https://github.com/getkirby/kirby/issues/7569)
- `Kirby\Form\Field\BaseField::stringTemplate()` now uses `ModelWithContent::toSafeString()` by default and introduces a new `$safe` argument, which can switch to the unsafe method. [#7687](https://github.com/getkirby/kirby/pull/7687)

### Panel

- Toggle field uses `<k-toggle-input>` instead `<k-input :type="toggle">` similarly to the radio and checkboxes field [#7489](https://github.com/getkirby/kirby/pull/7489)
- `<k-item>` only hides default options button when in selecting mode, but not custom used options slot [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>` is selectable by default unless explicitly defined otherwise [#7516](https://github.com/getkirby/kirby/pull/7516)
- `<k-item>` selected to also work with UUIDs [#7751](https://github.com/getkirby/kirby/pull/7751)
- No badge for current changes on languages btn [#7749](https://github.com/getkirby/kirby/pull/7749)
- Important context data (path, referrer, query, code) is always added to the view response object again. This is important to make sure that the view URL for error views are correct. Otherwise, the frontend will create some weird side-effects, such as redirects to /panel/null. [#7744](https://github.com/getkirby/kirby/pull/7744)
- Panel dialog/drawer listeners get preserved when loading backend defined dialog/drawer [#7888](https://github.com/getkirby/kirby/pull/7888)

---

## 🚨 Breaking changes

### Core

#### General

- Removed support for PHP 8.2. Use PHP 8.3, 8.4 or 8.5 instead. [#7372](https://github.com/getkirby/kirby/pull/7372)
- Template data must not include variables named `$slot` or `$slots`. [#7599](https://github.com/getkirby/kirby/pull/7599)
- Custom validators cannot overwrite default validators from the `Kirby\Toolkit\V` class any longer. [#7674](https://github.com/getkirby/kirby/pull/7674)
- Removed `Kirby\Cms\Api` class. Use `Kirby\Api\Api` class instead. [#7532](https://github.com/getkirby/kirby/pull/7532)

#### Configuration

- Changed the default YAML handler to Symfony YAML which sometimes enforces a stricter syntax than our previous Spyc handler. You can switch back to Spyc with the config option `'yaml.handler' => 'spyc'` [#7530](https://github.com/getkirby/kirby/pull/7530)

#### Field methods

- Calling non-existing field methods is throwing a `Kirby\Exception\BadMethodCallException`. [#7082](https://github.com/getkirby/kirby/pull/7082)
- Removed `Kirby\Content\Field::$aliases`, `Kirby\Cms\Core::fieldMethods()` and `Kirby\Cms\Core::fieldMethodsAliases()`. [#7082](https://github.com/getkirby/kirby/pull/7082)

#### UUIDs

- `Kirby\Uuid\Uuid::for()` does not resolve any permalinks anymore. Use `Kirby\Uuid\Permalink::from()` instead. [#7545](https://github.com/getkirby/kirby/pull/7545)
- `Kirby\Uuid\Uuid::for()` cannot be called any longer with a string. Use `Kirby\Uuid\Uuid::from(string $uuid)` or `Kirby\Uuid\Permalink::from(string $permalink)` instead. [#7544](https://github.com/getkirby/kirby/pull/7544)
- Remove deprecated `Uuid::url()`. Use `Uuid::toPermalink()` instead.

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
- Request changes
    - We are getting rid of the term “Fiber” for backend requests.
    - The `window.fiber` global has been replace with `window.panelState`
    - The `X-Fiber` namespace in request headers has been replaced with `X-Panel`
    - Keys aren't prefixed with `$` anymore in request responses. Use without prefix. This also affects Panel plugins reloading the Panel by defining only specific keys to be reloaded. [#7365](https://github.com/getkirby/kirby/pull/7365)
- Helpers
    - Removed `$helper.isVueComponent()` JS helper [#7518](https://github.com/getkirby/kirby/pull/7518)
    - Removed `$helper.link.preview()` helper. Use `items/*` request endpoints instead. [#7725](https://github.com/getkirby/kirby/pull/7725)
- Removed deprecated `panel.dialog.openComponent()` method [#7518](https://github.com/getkirby/kirby/pull/7518)

#### Backend

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
    - `Kirby\Panel\Panel`: Removed`::routesForViews()`, `::routesForSearches()`, `::routesForDialogs()`, `::routesForDrawers()`, `::routesForDropdowns()`, `::routesForRequests()`. Use instead: `Kirby\Panel\Routes\DialogRoutes`, `Kirby\Panel\Routes\DrawerRoutes`, `Kirby\Panel\Routes\DropdownRoutes`, `Kirby\Panel\Routes\RequestRoutes`, `Kirby\Panel\Routes\SearchRoutes`, `Kirby\Panel\Routes\ViewRoutes`.
    - For reponses, use `Kirby\Panel\Responses\DialogResponse`, `Kirby\Panel\ Responses\DrawerResponse`, `Kirby\Panel\ Responses\DropdownResponse`, `Kirby\Panel\Responses\RequestResponse`, `Kirby\Panel\ Responses\SearchResponse`.
    - Removed `Kirby\Panel\Dialog`, `Kirby\Panel\Drawer`, `Kirby\Panel\Dropdown`, `Kirby\Panel\Json`, `Kirby\Panel\Request`
    - Removed methods from `Kirby\Panel\View`: `::apply()`, `::applyGlobals()`, `::applyOnly()`, `::data()`, `::globals()`, `::searches()`. Use `Kirby\Panel\State`  instead.
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

---

## ☠️ Deprecated

### Core

- The Spyc YAML handler has been deprecated and will be removed in a future release. [#7530](https://github.com/getkirby/kirby/pull/7530)

### Panel

#### Backend

- `Kirby\Panel\File::dropdown()`, `Kirby\Panel\Page::dropdown()` and `Kirby\Panel\User::dropdown()` have been deprecated. Use the respective `Kirby\Panel\Controller\DropdownController` instead. [#7425](https://github.com/getkirby/kirby/pull/7425)
- `::breadcrumb()`, `::buttons()`, `::prevNext()`, `::props()`, `::versions()` and `::view()` methods of `Kirby\Panel\Model`, `Kirby\Panel\Site`, `Kirby\Panel\Page`, `Kirby\Panel\File` and `Kirby\Panel\User` have been deprecated. Use the respective `Kirby\Panel\Controller\View` classes instead. [#7480](https://github.com/getkirby/kirby/pull/7480)

---

## ♻️ Refactored

### Core

- Moved default field methods into new `Kirby\Content\FieldMethods` trait use by `Kirby\Content\Field` [#7082](https://github.com/getkirby/kirby/pull/7082)
- Implemented default validators as regular class methods of `Kirby\Toolkit\V` [#7608](https://github.com/getkirby/kirby/pull/7608)
- PHP type hints have been added to all class constants [#7536](https://github.com/getkirby/kirby/pull/7536)
- Moved permalink logic to new `Kirby\Uuid\Permalink` class [#7545](https://github.com/getkirby/kirby/pull/7545)
- New `Kirby\Uuid\Uuid::from(string $uuid)` method for creating an Uuid object from a UUID string. `Kirby\Uuid\Uuid::for()` remains to create a Uuid object for a model object. [#7544](https://github.com/getkirby/kirby/pull/7544)
- Use `json_validate` for `V::json()` [#7538](https://github.com/getkirby/kirby/pull/7538)

### Panel

#### Frontend

- Files, pages and users field previews now support IDs alongside item objects as value and will fetch the item data for these IDs automatically. [#7723](https://github.com/getkirby/kirby/pull/7723)

#### Backend

- Refactored the Panel namespace as non-static classes [#7386](https://github.com/getkirby/kirby/pull/7386) [#7394](https://github.com/getkirby/kirby/pull/7394) [#7394](https://github.com/getkirby/kirby/pull/7394)
    - New classes: `Kirby\Panel\Areas` , `Kirby\Panel\Area`, `Kirby\Pane\Access`, `Kirby\Panel\Router` class [#7391](https://github.com/getkirby/kirby/pull/7391) [#7406](https://github.com/getkirby/kirby/pull/7406) [#7383](https://github.com/getkirby/kirby/pull/7383)  [#7407](https://github.com/getkirby/kirby/pull/7407)
- Refactored all Panel routes to be powered by controller classes (`Kirby\Panel\Controller`):
    - Refactored all Panel dialogs, drawers, drop-down, requests and views (from `kirby/config/areas`) as controllers [#7480](https://github.com/getkirby/kirby/pull/7480) [#7482](https://github.com/getkirby/kirby/pull/7482) [#7475](https://github.com/getkirby/kirby/pull/7475) [#7474](https://github.com/getkirby/kirby/pull/7474) [#7471](https://github.com/getkirby/kirby/pull/7471) [#7469](https://github.com/getkirby/kirby/pull/7469) [#7479](https://github.com/getkirby/kirby/pull/7479)  [#7439](https://github.com/getkirby/kirby/pull/7439) [#7455](https://github.com/getkirby/kirby/pull/7455) [#7453](https://github.com/getkirby/kirby/pull/7453) [#7451](https://github.com/getkirby/kirby/pull/7451) [#7440](https://github.com/getkirby/kirby/pull/7440) [#7448](https://github.com/getkirby/kirby/pull/7448) [#7452](https://github.com/getkirby/kirby/pull/7452) [#7425](https://github.com/getkirby/kirby/pull/7425) [#7427](https://github.com/getkirby/kirby/pull/7427)  [#7437](https://github.com/getkirby/kirby/pull/7437/)
    - Refactored existing classes as Panel controller classes:
        - Refactored field dialogs and drawers as dialog controller and drawer controller (using a shared `FieldController` trait) [#7454](https://github.com/getkirby/kirby/pull/7454)
- Aligned the namespace names within `Kirby\Panel\Ui` [#7459](https://github.com/getkirby/kirby/pull/7459)
    - `Kirby\Panel\Ui\Buttons\ViewButtons` now supports passing an array of `Kirby\Panel\Ui\Buttons\ViewButton` objects [#7462](https://github.com/getkirby/kirby/pull/7462)
    - `Panel\Ui\Stat` optional `$model` property [#7420](https://github.com/getkirby/kirby/pull/7420)

---

## 🧹 Housekeeping

- Upgraded CI setup [#7738](https://github.com/getkirby/kirby/pull/7738)
    - Upgraded to PHPUnit 12 [#7681](https://github.com/getkirby/kirby/pull/7681)
    - Removed `phpmd` from our CI [#7536](https://github.com/getkirby/kirby/pull/7536)
- Added `Kirby\Panel\TestCase::setRequest()` helper method [#7440](https://github.com/getkirby/kirby/pull/7440)
- Using ParaTest to run PHPUnit tests in parallel [#7803](https://github.com/getkirby/kirby/pull/7803)
- PHPUnit improvements
    - Cache PHPUnit results
    - Only create coverage results for PHP 8.4 (as we also only upload those)
