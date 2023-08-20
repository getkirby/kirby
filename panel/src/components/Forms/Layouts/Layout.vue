<template>
	<section
		:data-selected="isSelected"
		class="k-layout"
		tabindex="0"
		@click="$emit('select')"
	>
		<k-grid class="k-layout-columns">
			<k-layout-column
				v-for="(column, columnIndex) in columns"
				:key="column.id"
				:endpoints="endpoints"
				:fieldset-groups="fieldsetGroups"
				:fieldsets="fieldsets"
				v-bind="column"
				@input="
					$emit('updateColumn', {
						column,
						columnIndex,
						blocks: $event
					})
				"
			/>
		</k-grid>
		<nav v-if="!disabled" class="k-layout-toolbar">
			<k-button
				v-if="settings"
				:title="$t('settings')"
				class="k-layout-toolbar-button"
				icon="settings"
				@click="openSettings"
			/>

			<k-dropdown>
				<k-button
					class="k-layout-toolbar-button"
					icon="angle-down"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" align-x="end">
					<k-dropdown-item icon="angle-up" @click="$emit('prepend')">
						{{ $t("insert.before") }}
					</k-dropdown-item>
					<k-dropdown-item icon="angle-down" @click="$emit('append')">
						{{ $t("insert.after") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						v-if="settings"
						icon="settings"
						@click="openSettings"
					>
						{{ $t("settings") }}
					</k-dropdown-item>
					<k-dropdown-item icon="copy" @click="$emit('duplicate')">
						{{ $t("duplicate") }}
					</k-dropdown-item>
					<k-dropdown-item
						:disabled="layouts.length === 1"
						icon="dashboard"
						@click="$emit('change')"
					>
						{{ $t("field.layout.change") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item icon="template" @click="$emit('copy')">
						{{ $t("copy") }}
					</k-dropdown-item>
					<k-dropdown-item icon="download" @click="$emit('paste')">
						{{ $t("paste.after") }}
					</k-dropdown-item>
					<hr />
					<k-dropdown-item icon="trash" @click="remove">
						{{ $t("field.layout.delete") }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>
			<k-sort-handle />
		</nav>
	</section>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		attrs: [Array, Object],
		columns: Array,
		disabled: Boolean,
		endpoints: Object,
		fieldsetGroups: Object,
		fieldsets: Object,
		id: String,
		isSelected: Boolean,
		layouts: Array,
		settings: Object
	},
	computed: {
		tabs() {
			let tabs = this.settings.tabs;

			for (const [tabName, tab] of Object.entries(tabs)) {
				for (const fieldName in tab.fields) {
					tabs[tabName].fields[fieldName].endpoints = {
						field: this.endpoints.field + "/fields/" + fieldName,
						section: this.endpoints.section,
						model: this.endpoints.model
					};
				}
			}

			return tabs;
		}
	},
	methods: {
		openSettings() {
			this.$panel.drawer.open({
				component: "k-form-drawer",
				props: {
					icon: "settings",
					tabs: this.tabs,
					title: this.$t("settings"),
					value: this.attrs
				},
				on: {
					input: (attrs) => this.$emit("updateAttrs", attrs)
				}
			});
		},
		remove() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.layout.delete.confirm")
				},
				on: {
					submit: () => {
						this.$emit("remove");
						this.$panel.dialog.close();
					}
				}
			});
		}
	}
};
</script>

<style>
.k-layout {
	--layout-border-color: var(--color-gray-300);
	--layout-toolbar-width: 2rem;

	position: relative;
	padding-inline-end: var(--layout-toolbar-width);
	background: #fff;
	box-shadow: var(--shadow);
}
[data-disabled="true"] .k-layout {
	padding-inline-end: 0;
}
.k-layout:not(:last-of-type) {
	margin-bottom: 1px;
}
.k-layout:focus {
	outline: 0;
}

/** Toolbar **/
.k-layout-toolbar {
	position: absolute;
	inset-block: 0;
	inset-inline-end: 0;
	width: var(--layout-toolbar-width);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: space-between;
	padding-bottom: var(--spacing-2);
	font-size: var(--text-sm);
	background: var(--color-gray-100);
	border-inline-start: 1px solid var(--color-light);
	color: var(--color-gray-500);
}
.k-layout-toolbar:hover {
	color: var(--color-black);
}
.k-layout-toolbar-button {
	width: var(--layout-toolbar-width);
	height: var(--layout-toolbar-width);
}

/** Columns **/
.k-layout-columns.k-grid {
	grid-gap: 1px;
	background: var(--layout-border-color);
	background: var(--color-gray-300);
}
.k-layout:not(:first-child) .k-layout-columns.k-grid {
	border-top: 0;
}
</style>
