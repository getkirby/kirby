# Most doable feature requests

Open requests from the [Kirby feedback board](https://feedback.getkirby.com/), compared with the Kirby 6.0.0 code on 2026-11-24.

## How to read this list

- Coverage: All 456 open posts on the board were read by title and excerpt. Completed, archived and merged posts are excluded. The candidates below were then checked in the code. Comments were only read for 13, 55, 131, 164, 201, 206, 212, 240, 252, 299, 451, 479, 608, 689 and 712.
- Votes: Upvotes from the board. Related posts are listed together with separate counts, because the same people may have voted on both.
- Doability: 1 (hard) to 5 (easy). An estimate based on reading the code. Nothing was prototyped.
- Tested: Marked where the current behavior was confirmed by running the code.
- Status: Unassigned unless noted.

## Best candidates

Doability 4 or 5, sorted by votes.

| Request | Votes | Doability | Assessment |
|---|---|---|---|
| [Translation placeholders in all Panel fields (451)](https://feedback.getkirby.com/451) | 84 | 4 | Tested: keys work in labels, but not in option texts of select and radio fields. [Kirby\Option\Option](src/Option/Option.php#L66) wraps them as plain text. The post also lists smaller cases, so start with options. |
| [Cachebuster in core (164)](https://feedback.getkirby.com/164) | 71 | 4 | The `css` and `js` [components](config/components.php#L39) return the URL unchanged. Commenters would accept a simple version number in the config. |
| [Shorter block IDs (240)](https://feedback.getkirby.com/240) | 41 | 4 | Generated with `Str::uuid()` in a few places, mainly [Kirby\Form\Field\BlocksField](src/Form/Field/BlocksField.php#L90) and [Kirby\Form\Field\LayoutField](src/Form/Field/LayoutField.php#L89). Existing IDs can stay. |
| [`$files->zip()` (288)](https://feedback.getkirby.com/288) | 30 | 4 | `ZipArchive` is already used in [Kirby\Filesystem\F](src/Filesystem/F.php#L1026). Needs a decision on where archives are stored. |
| [Readable layout type (201)](https://feedback.getkirby.com/201) and [row information (225)](https://feedback.getkirby.com/225) | 19, 5 | 5 | Tested: `$layout->columns()->pluck('width')` already works. A small method on [Kirby\Cms\Layout](src/Cms/Layout.php) would cover most of it. 201 also asks for named layouts. |
| [Page title: maxlength (281)](https://feedback.getkirby.com/281) | 19 | 4 | The [create dialog](src/Panel/Controller/Dialog/PageCreateDialogController.php#L128) already passes title options on to the field. The change title dialog and the validation are missing. |
| [Hide settings button if all options are disabled (204)](https://feedback.getkirby.com/204) | 15 | 4 | [Kirby\Panel\Ui\Button\SettingsButton](src/Panel/Ui/Button/SettingsButton.php) only checks the lock. |
| [Drawer animation (190)](https://feedback.getkirby.com/190) | 14 | 5 | No animation found in the drawers. CSS only, should respect reduced motion. |
| [svg() with attributes (417)](https://feedback.getkirby.com/417) | 14 | 4 | [Kirby\Cms\Html](src/Cms/Html.php#L137) returns the file content as is. Needs safe attribute merging. |
| [Remove a page translation (317)](https://feedback.getkirby.com/317) | 12 | 4 | [Kirby\Content\Version](src/Content/Version.php#L141) can delete per language. Needs a dialog, a permission and a state in the language dropdown. |
| [PHP blueprints in site/blueprints (572)](https://feedback.getkirby.com/572) | 12 | 4 | [Kirby\Blueprint\Blueprint](src/Blueprint/Blueprint.php#L295) only looks for yml files. The PHP data handler exists. |
| [Files section: rename Add to Upload (448)](https://feedback.getkirby.com/448) | 12 | 5 | One string in [FileListField.vue](panel/src/components/Forms/Field/FileListField.vue#L18). |
| [Date field: queries for min and max (269)](https://feedback.getkirby.com/269) and [textarea length (209)](https://feedback.getkirby.com/209) | 11, 3 | 4 | Tested for 269: a query returns null. Can reuse the pattern from [Kirby\Form\Mixin\DefaultValue](src/Form/Mixin/DefaultValue.php#L34). |
| [Writer: limit link types (601)](https://feedback.getkirby.com/601) | 9 | 4 | The [link dialog](panel/src/components/Dialogs/LinkDialog.vue) accepts custom fields. Server-side validation would be extra work. |
| [Video block: aspect ratio (284)](https://feedback.getkirby.com/284) | 9 | 5 | The image block has the field, [video.yml](config/blocks/video/video.yml) does not. Also needs the snippet. |
| [More collection methods (218)](https://feedback.getkirby.com/218) | 8 | 5 | Tested: `reduce()` is missing in [Kirby\Toolkit\Collection](src/Toolkit/Collection.php). |
| [Tabs: dynamic titles (361)](https://feedback.getkirby.com/361) | 8 | 4 | Tested: queries are printed as is. [Kirby\Blueprint\Tab](src/Blueprint/Tab.php#L98) has access to the model. |
| [Fallback language for detection (253)](https://feedback.getkirby.com/253) | 7 | 5 | Mostly the final return of `detectedLanguage()` in [Kirby\Cms\App](src/Cms/App.php#L642). Check why [PR #3792](https://github.com/getkirby/kirby/pull/3792) was closed. |
| [Textarea link: open in new window (726)](https://feedback.getkirby.com/726) | 7 | 4 | Missing in the [toolbar dialog](panel/src/components/Forms/Toolbar/LinkDialog.vue). KirbyText supports `target`, plain Markdown does not. |
| [Hide row number in tables (460)](https://feedback.getkirby.com/460) and [in structures (524)](https://feedback.getkirby.com/524) | 7, 3 | 4 | [Table.vue](panel/src/components/Layout/Table.vue#L330) handles `index: false`, but sortable tables still keep the column. 460 also asks to hide the header. |
| [Clearer CSRF message (674)](https://feedback.getkirby.com/674) | 5 | 5 | The [message](i18n/translations/en.json#L109) is only "Invalid CSRF token". |
| [Default role for new users (685)](https://feedback.getkirby.com/685) | 5 | 4 | The [dialog](src/Panel/Controller/Dialog/UserCreateDialogController.php#L32) falls back to the first available role. |
| [Custom icon for empty state (144)](https://feedback.getkirby.com/144) | 5 | 4 | [Kirby\Form\Mixin\EmptyState](src/Form/Mixin/EmptyState.php) only knows text. An array already means translations. |
| [Tags: copy values (199)](https://feedback.getkirby.com/199) | 5 | 4 | Contained frontend work. |
| [Pages section: hide controls (597)](https://feedback.getkirby.com/597) | 4 | 4 | One option on [Kirby\Form\Field\PageListField](src/Form/Field/PageListField.php). |

## High demand, more design work

| Request | Votes | Doability | Main concern |
|---|---|---|---|
| [More extensive `when` conditions (13)](https://feedback.getkirby.com/13) | 322 | 3 | OR logic is feasible. Full queries are harder, because [Kirby\Form\Mixin\When](src/Form/Mixin/When.php) and [field.ts](panel/src/helpers/field.ts) must agree. Arrays already mean an exact match. |
| [Layout: min/max blocks (180)](https://feedback.getkirby.com/180), [limit blocks to columns (297)](https://feedback.getkirby.com/297) and [conditional blocks (624)](https://feedback.getkirby.com/624) | 96, 63, 8 | 2 | Needs a new layout syntax, validation and drag rules. Design together. |
| [Settings for each layout column (260)](https://feedback.getkirby.com/260) | 75 | 2 | [Kirby\Cms\LayoutColumn](src/Cms/LayoutColumn.php) stores no attributes, so the data format changes. |
| [Sort tables via column headers (438)](https://feedback.getkirby.com/438) | 47 | 3 | Must work across pagination. An earlier attempt, [PR #4519](https://github.com/getkirby/kirby/pull/4519), was closed. |
| [Link field: query (574)](https://feedback.getkirby.com/574) and [search (600)](https://feedback.getkirby.com/600) | 37, 18 | 3 | The [page tree](src/Panel/Controller/Request/PageTreeRequestController.php) has no filter and is shared with the move dialog. |
| [Auto-rename existing files (188)](https://feedback.getkirby.com/188) and [replace on upload (634)](https://feedback.getkirby.com/634) | 27, 16 | 3 | Replacing exists in [Kirby\Cms\FileActions](src/Cms/FileActions.php#L458). Needs conflict handling and one dialog for both. |
| [More structure methods (161)](https://feedback.getkirby.com/161) | 27 | 3 | [Kirby\Cms\Structure](src/Cms/Structure.php) has no write methods. Needs an API decision. |
| [Layout: hide toggle (227)](https://feedback.getkirby.com/227) | 20 | 3 | Layout rows have no hidden state yet. Templates must respect it. |
| [Reset a translated field (252)](https://feedback.getkirby.com/252) | 19 | 3 | Must store null instead of an empty string. Combine with 317. |
| [Sortable files flipped (270)](https://feedback.getkirby.com/270) and [pages (396)](https://feedback.getkirby.com/396) | 17, 3 | 3 | `flip` disables sorting in [Kirby\Form\Field\ModelListField](src/Form/Field/ModelListField.php#L590). |
| [Changes dialog: save or revert all (148)](https://feedback.getkirby.com/148) | 14 | 3 | The [endpoints](config/api/routes/changes.php) work per model. Needs error and lock handling. |
| [Language variables permissions (606)](https://feedback.getkirby.com/606) | 11 | 3 | Status: Planned. [Kirby\Cms\Permissions](src/Cms/Permissions.php#L62) has no separate entry yet. |
| [groupBy with split (142)](https://feedback.getkirby.com/142) | 7 | 3 | One item would need to appear in several groups. |

## Low votes, small effort

- [Remove `indent_size` from .editorconfig (453)](https://feedback.getkirby.com/453), 2 votes, doability 5
- [Writer: shortcut for links (749)](https://feedback.getkirby.com/749), 1 vote, doability 4. Check conflicts with other shortcuts.
- [Shortcut for inserting blocks (656)](https://feedback.getkirby.com/656), 1 vote, doability 4
- [Structure: columns with custom values (285)](https://feedback.getkirby.com/285), 2 votes, doability 3
- [Object field: inline editing (531)](https://feedback.getkirby.com/531) and [group layout (657)](https://feedback.getkirby.com/657), 2 and 1 votes, doability 3
- [Subfolders for block blueprints (718)](https://feedback.getkirby.com/718), 1 vote, doability 3

## Already fixed

Open posts that can probably be closed. Definitely means confirmed by running the code, by a merged pull request or by the author of the post. Very likely means the code covers it, but it was not tried in the Panel or the post may expect slightly more.

| Request | Votes | Confidence | Evidence |
|---|---|---|---|
| [More granular control of creatable roles (479)](https://feedback.getkirby.com/479) | 13 | Definitely | Tested: `options.create` in a user blueprint limits who can create that role, see [Kirby\Cms\Roles](src/Cms/Roles.php#L65). The syntax differs from the proposal, and a comment asks for a default role (see 685). |
| [Create page: uuid slug (688)](https://feedback.getkirby.com/688) | 7 | Definitely | Tested: with `slug: "{{ page.uuid.id }}"` the [create dialog](src/Panel/Controller/Dialog/PageCreateDialogController.php#L276) uses the UUID as slug. |
| [Multiple Panel stylesheets or from a route (363)](https://feedback.getkirby.com/363) | 5 | Definitely | Tested: [Kirby\Panel\Assets](src/Panel/Assets.php#L81) accepts absolute URLs in an array. Relative routes are still ignored. |
| [Blueprint: extend entirely (25)](https://feedback.getkirby.com/25) | 3 | Definitely | Tested: title, options, tabs and fields are all inherited. |
| [Writer: rank of custom marks (755)](https://feedback.getkirby.com/755) | 1 | Definitely | [PR #8267](https://github.com/getkirby/kirby/pull/8267) links to the post and is merged. |
| [Redirect any home page (712)](https://feedback.getkirby.com/712) | 0 | Definitely | Tested in a single-language setup. The author confirmed in the comments that the cause was a custom route. |
| [Press Enter to confirm dialogs (403)](https://feedback.getkirby.com/403) | 7 | Very likely | Dialogs without inputs focus the [submit button](panel/src/helpers/focus.ts#L43). The 6.0 release notes mention [#8882](https://github.com/getkirby/kirby/pull/8882) for this. |
| [Handling of /media, /assets and other routes (355)](https://feedback.getkirby.com/355) | 4 | Very likely | Tested: `media` is rejected, and `api`, `assets` and `panel` are in the same [check](src/Guards/PageValidators.php#L350). Custom routes are not covered. |
| [Save structure field on click outside (545)](https://feedback.getkirby.com/545) | 2 | Very likely | The [drawer](panel/src/components/Forms/Field/StructureField.vue#L460) saves on every input. |
| [Structure: `auto` for column width (48)](https://feedback.getkirby.com/48) | 2 | Very likely | [Table.vue](panel/src/components/Layout/Table.vue#L468) passes any value without a fraction on as CSS. |
| [Files field: `default` option (68)](https://feedback.getkirby.com/68) | 1 | Very likely | Tested: a filename as default is resolved. |
| [Logic in blueprints (464)](https://feedback.getkirby.com/464) | 1 | Very likely | Tested: comparisons with a ternary cover the example, including the prefix. |
| [Unregister core blocks (763)](https://feedback.getkirby.com/763) | 1 | Very likely | Tested: `blocks.fieldsets` in the config replaces the default set, see [Kirby\Cms\Fieldsets](src/Cms/Fieldsets.php#L103). The blocks are still registered. |

## Partly fixed

Keep these open or narrow them down.

- [Writer: rel attribute (299)](https://feedback.getkirby.com/299), 33 votes: The [link mark](panel/src/components/Forms/Writer/Marks/Link.ts) no longer sets a `rel` default. Control per link is missing.
- [Custom page status (159)](https://feedback.getkirby.com/159), 28 votes: Statuses can have their own icon and theme in 6.0. New statuses are not possible.
- [Panel notifications: stacking and theme (639)](https://feedback.getkirby.com/639), 7 votes: [Themes](panel/src/panel/notification.ts#L15) exist, stacking does not.
- [Better selection controls (55)](https://feedback.getkirby.com/55), 6 votes: Pickers now use [radios or checkboxes](panel/src/components/Collection/Item.vue#L59) depending on the mode. The visual points need a look.
- [Frontmatter support (689)](https://feedback.getkirby.com/689), 4 votes: A [handler](src/Data/Frontmatter.php) exists, but md files still use the txt format.
- [File type support API (497)](https://feedback.getkirby.com/497), 4 votes: Plugins can add file previews. Other features per file type were not checked.
- [Blueprint: shortcut to disable all options (33)](https://feedback.getkirby.com/33), 3 votes: Tested: `options: false` works, the variant per role does not.
- [Create option for files sections (212)](https://feedback.getkirby.com/212), 2 votes: `create: false` shipped in 5.2. Choosing a template only for uploads is missing.
- [Expandable drawers (581)](https://feedback.getkirby.com/581), 2 votes: Drawers have a `size` option. Users cannot resize them.
- [Languages area: more options (734)](https://feedback.getkirby.com/734), 1 vote: [Permissions](src/Cms/Permissions.php#L61) cover creating and deleting languages, but not language variables (see 606).

## Recommended order

1. Clean up the board: close the fixed posts and narrow down the partly fixed ones
2. Quick wins: 448, 284, 218, 674 and 201 with 225
3. Best ratio of votes to effort: 451 and 164
4. Multi-language package: 317 with 252, then 253
5. Larger bet: 13, starting with OR conditions

## Sources

- [Kirby feedback board](https://feedback.getkirby.com/) via the Nolt MCP server
- Pull requests and release notes in [getkirby/kirby](https://github.com/getkirby/kirby)
