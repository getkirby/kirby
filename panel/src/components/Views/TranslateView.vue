<template>
	<k-panel class="k-panel-inside k-translate-view">
		<main class="k-translate-view-grid">
			<header class="k-translate-view-header">
				<k-button-group>
					<k-button
						:link="link"
						:responsive="true"
						:title="$t('back')"
						icon="angle-left"
						size="sm"
						variant="filled"
						@click="exit"
					>
					</k-button>
					<k-button
						class="k-preview-view-title"
						:icon="$panel.isLoading ? 'loader' : 'title'"
						:dropdown="true"
						@click="$refs.tree.toggle()"
					>
						{{ title }}
					</k-button>
					<k-dropdown ref="tree" theme="dark" class="k-preview-view-tree">
						<k-page-tree :current="id" @click.stop @select="onTreeNavigate" />
					</k-dropdown>
				</k-button-group>

				<k-stack
					class="k-translate-stats"
					direction="row"
					align="center"
					justify="center"
					gap="var(--spacing-4)"
				>
					<k-button-group layout="collapsed">
						<k-button
							v-for="language in languagesOptions"
							:key="language.code"
							v-bind="language"
							size="sm"
							variant="filled"
						/>
					</k-button-group>

					<k-progress :value="process" />
					<p class="percentage">{{ process }}%</p>
				</k-stack>

				<k-button-group>
					<k-form-controls
						:editor="editor"
						:has-diff="hasDiff"
						:is-locked="isLocked"
						:is-processing="isSaving"
						:modified="modified"
						:preview="permissions.preview ? api + '/preview/changes' : false"
						@discard="onDiscard"
						@submit="onSubmit"
					/>
				</k-button-group>
			</header>
			<div class="k-translate-view-fields">
				<table class="k-translate-table">
					<thead>
						<tr>
							<th></th>
							<th class="a">
								{{ sourceLanguage.name }}
							</th>
							<th class="b">
								{{ currentLanguage.name }}
							</th>
							<th class="status"></th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="field in fields"
							:key="field.name"
							:aria-current="field.name === currentField ? 'row' : null"
							:data-has-diff="fieldHasDiff(field.name)"
							:data-is-translated="fieldIsTranslated(field.name)"
							:data-is-translatable="field.translate"
							@click="changeField(field.name)"
						>
							<th>
								{{ field.label ?? field.name }}
							</th>
							<td class="a">
								<k-field-preview
									:field="field"
									:value="translationA[field.name]"
								/>
							</td>
							<td class="b">
								<k-field-preview :field="field" :value="content[field.name]" />
							</td>
							<td class="status">
								<k-icon v-if="field.translate === false" type="lock" />
								<k-icon
									v-else-if="fieldIsTranslated(field.name)"
									type="translate"
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="k-translate-view-form">
				<template v-if="currentField">
					<details class="a" open>
						<summary>
							<k-stack direction="row" align="center">
								<h3>{{ label(sourceLanguage) }}</h3>
							</k-stack>
						</summary>
						<div>
							<k-fieldset
								:disabled="true"
								:fields="currentFields"
								:value="translationA"
							/>
						</div>
					</details>
					<details class="b" open>
						<summary>
							<k-stack direction="row" justify="space-between" align="center">
								<h3>{{ label(currentLanguage) }}</h3>
								<k-button-group
									v-if="currentFieldDefinition.translate === true"
								>
									<k-button icon="copy" size="sm" @click="copyTranslation()" />
									<k-button
										icon="remove"
										size="sm"
										@click="removeTranslation()"
									/>
									<k-button icon="url" size="sm" @click="linkTranslation()" />
								</k-button-group>
							</k-stack>
						</summary>
						<div>
							<k-fieldset
								ref="form"
								:key="$panel.view.timestamp"
								:fields="currentFields"
								:value="content"
								@input="onInput"
								@submit="onSubmit"
							/>
						</div>
					</details>
				</template>
			</div>
		</main>
		<k-panel-notifications />
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";
import FieldPreview from "./Translate/FieldPreview.vue";

export default {
	components: {
		"k-field-preview": FieldPreview
	},
	extends: ModelView,
	props: {
		fields: Object,
		title: String,
		translationA: Object,
		translationB: Object
	},
	data() {
		return {
			currentField: null
		};
	},
	computed: {
		content() {
			return this.versions.changes;
		},
		currentFieldDefinition() {
			return this.fields[this.currentField] ?? {};
		},
		currentFields() {
			const field = this.currentFieldDefinition;

			return {
				[this.currentField]: {
					...field,
					endpoints: {
						field: this.api + "/fields/" + field.name,
						model: this.api
					},
					width: "1/1",
					disabled: field.disabled || field.translate === false ? true : false
				}
			};
		},
		currentLanguage() {
			return this.$panel.language;
		},
		currentValue() {
			return {
				[this.currentField]: this.content[this.currentField]
			};
		},
		diff() {
			return this.$panel.content.diff();
		},
		editor() {
			return this.lock.user.email;
		},
		hasDiff() {
			return this.$panel.content.hasDiff();
		},
		isLocked() {
			return this.lock.isLocked;
		},
		isSaving() {
			return this.$panel.content.isProcessing;
		},
		languagesOptions() {
			return this.$panel.languages
				.filter((language) => language.default === false)
				.map((language) => {
					return {
						text: language.code.toUpperCase(),
						link: `${this.link}/translate/?language=${language.code}`,
						current: language.code === this.currentLanguage.code
					};
				});
		},
		modified() {
			return this.lock.modified;
		},
		process() {
			const countFields = Object.keys(this.fields).length;
			let translated = 0;

			for (const fieldName in this.fields) {
				if (this.fieldIsTranslated(fieldName)) {
					translated++;
				}
			}

			return Math.round((translated / countFields) * 100);
		},
		sourceLanguage() {
			return this.$panel.languages.find(
				(language) => language.default === true
			);
		}
	},
	methods: {
		async changeField(name) {
			this.currentField = name;
			await this.$nextTick();
			this.$refs.form.focus(name);
		},
		async copyTranslation() {
			await this.$panel.content.update({
				[this.currentField]: this.translationA[this.currentField]
			});

			this.reset();
		},
		fieldHasDiff(name) {
			return Object.hasOwn(this.diff, name);
		},
		fieldIsTranslated(name) {
			return (
				JSON.stringify(this.translationA[name]) !==
				JSON.stringify(this.content[name])
			);
		},
		label(language) {
			const field = this.fields[this.currentField];

			return `${field.label ?? field.name} (${language.name})`;
		},
		async linkTranslation() {
			await this.$panel.content.update({
				[this.currentField]: null
			});

			this.reset();
		},
		async onDiscard() {
			try {
				await this.$panel.content.discard({
					api: this.api,
					language: this.$panel.language.code
				});

				this.$panel.dialog.close();
				this.$panel.view.refresh();
			} catch (e) {
				this.$panel.error(e);
			} finally {
				this.reset();
			}
		},
		onTreeNavigate(page, force = false) {
			if (page.id === this.id && force === false) {
				return;
			}

			this.$refs.tree?.close();

			const id = page.id === "/" ? "site" : page.id;
			const url = this.$api.pages.url(id, "translate");

			this.currentField = null;

			this.$panel.view.open(url);
		},
		async removeTranslation() {
			await this.$panel.content.update({
				[this.currentField]: null
			});

			this.reset();
		},
		async reset() {
			const currentField = this.currentField;
			this.currentField = false;

			await this.$nextTick();
			this.currentField = currentField;
		}
	}
};
</script>

<style>
.k-translate-view {
	--color-source-language: var(--color-gray-600);
	--color-current-language: var(--color-blue-500);
	--color-gray-925: hsl(var(--color-gray-hs), 10%);
	--text-2xs: 10px;

	position: fixed;
	inset: 0;
	height: 100%;
	display: grid;
	grid-template-rows: auto 1fr;
}

.k-translate-view-header {
	container-type: inline-size;
	display: grid;
	grid-template-columns: 1fr auto 1fr;
	gap: var(--spacing-2);
	align-items: center;
	padding: var(--spacing-3);
}
.k-translate-view-header > * {
	justify-self: center;
}
.k-translate-view-header > :first-child {
	justify-self: start;
}
.k-translate-view-header > :last-child {
	justify-self: end;
}

.k-translate-view-grid {
	position: absolute;
	inset: 0;
	display: grid;
	grid-template-columns: 1fr 1fr;
	grid-template-rows: 3.5rem 1fr;
	grid-template-areas:
		"header header"
		"fields form";
	height: 100%;
}

.k-translate-view-header {
	grid-area: header;
	border-bottom: 1px solid var(--color-gray-950);
}

.k-translate-view-fields {
	grid-area: fields;
	border-right: 2px solid var(--color-gray-950);
	background: var(--color-gray-900);
	overflow: auto;
}

.k-translate-stats .k-progress {
	display: flex;
	margin-top: 2px;
	flex-shrink: 0;
	width: 6rem;
}
.k-translate-stats .percentage {
	width: 3rem;
	font-size: var(--text-xs);
	flex-shrink: 0;
	color: var(--color-text-dimmed);
}
.k-translate-stats .k-button[aria-current] {
	color: var(--color-current-language);
}

.k-translate-table {
	table-layout: fixed;
	width: 100%;
	border: 0;
}
.k-translate-table tr {
	cursor: pointer;
}
.k-translate-table th {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	font-size: var(--text-xs);
	font-family: var(--font-mono);
	color: var(--color-gray-600);
	vertical-align: top;
	padding-block: var(--spacing-4);
}
.k-translate-table tbody th {
	font-size: var(--text-2xs);
}
.k-translate-table th > .k-button {
	--button-padding: 0;
}

.k-translate-table tr th:first-of-type {
	width: 9rem;
}

.k-translate-table .status {
	width: 2rem;
	text-align: center;
	vertical-align: top;
	padding-top: var(--spacing-3);
	--icon-size: 14px;
	--icon-color: var(--color-green-400);
}
.k-translate-table .status .k-icon {
	margin-top: 2px;
}
.k-translate-table [data-has-diff="true"] .status {
	--icon-color: var(--color-orange-500);
}
.k-translate-table [data-is-translatable="false"] .status {
	--icon-color: var(--color-gray-700);
}

.k-translate-table [data-is-translated="false"] .b > * {
	opacity: 0.375;
}

.k-translate-table td {
	font-size: var(--text-xs);
	line-height: 1.5;
	padding-inline: 0;
	color: var(--color-text-dimmed);
}
.k-translate-table :where(th, td) {
	padding: 0 var(--spacing-3);
	border-bottom: 1px solid var(--color-gray-950);
	overflow: hidden;
}
.k-translate-table tr[aria-current] :where(th, td) {
	background: var(--color-blue-900);
	color: var(--color-white);
}
.k-translate-table tr[aria-current] :where(th) {
	color: var(--color-blue-500);
}

.k-translate-table .k-html-field-preview {
	padding-block: var(--spacing-3);
}
.k-translate-table .k-tags-field-preview .k-tag {
	--tag-color-back: var(--color-gray-800);
}

.k-translate-table thead {
	position: relative;
}
.k-translate-table thead th {
	position: sticky;
	top: 0;
	line-height: 1;
	padding-block: 0;
	background: var(--color-gray-925);
	height: var(--height-xl);
	vertical-align: middle;
	overflow: hidden;
}
.k-translate-table thead th.a {
	color: var(--color-source-language);
}
.k-translate-table thead th.b {
	color: var(--color-current-language);
	--dropdown-color-current: var(--color-current-language);
}

.k-translate-view-form {
	grid-area: form;
	overflow: scroll;
}

.k-translate-view-form .k-field-header {
	display: none;
}

.k-translate-view-form summary {
	height: var(--height-xl);
	background: var(--color-gray-925);
	padding: 0 var(--spacing-6);
	font-size: var(--text-xs);
	list-style: none;
	font-family: var(--font-mono);
	border-bottom: 1px solid var(--color-gray-950);
}
.k-translate-view-form summary .k-stack {
	height: 100%;
}

.k-translate-view-form details > div {
	padding: var(--spacing-6);
}
.k-translate-view-form details.a[open] {
	border-bottom: 1px solid var(--color-gray-950);
}
.k-translate-view-form details.a {
	--input-color-border: var(--color-gray-850);
	--table-color-border: var(--color-gray-850);
	--color-border: var(--color-gray-850);
}

.k-translate-view-form > details.a summary h3 {
	color: var(--color-source-language);
}
.k-translate-view-form > details.b summary h3 {
	color: var(--color-current-language);
}
</style>
