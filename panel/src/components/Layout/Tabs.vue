<template>
	<k-collapsible v-if="tabs.length > 1" element="nav" class="k-tabs">
		<template #default="{ offset }">
			<k-button
				v-for="btn in buttons.slice(0, offset)"
				:key="btn.name"
				ref="visible"
				v-bind="btn"
				variant="dimmed"
				class="k-tabs-button"
			>
				{{ btn.text }}
			</k-button>
		</template>

		<template #fallback="{ offset }">
			<k-button
				:current="hasCurrent(offset)"
				:title="$t('more')"
				class="k-tabs-button k-tabs-dropdown-button"
				icon="dots"
				variant="dimmed"
				@click.stop="$refs.more.toggle()"
			/>
			<k-dropdown
				ref="more"
				:options="buttons.slice(offset)"
				align-x="end"
				class="k-tabs-dropdown"
			/>
		</template>
	</k-collapsible>
</template>

<script>
/**
 * @example <k-tabs
 * 	tab="content"
 * 	tabs="[
 * 		{ name: 'content', label: 'Content', link: '/content' },
 * 		{ name: 'settings', label: 'Settings', link: '/settings', badge: 3 }
 * 	]"
 * />
 */
export default {
	props: {
		/**
		 * Name of the currently active tab
		 */
		tab: String,
		/**
		 * List of tabs to display. Each entry must be an object with the following properties: `name`, `label`, `link`, `icon`, `badge`
		 */
		tabs: {
			type: Array,
			default: () => []
		},
		/**
		 * Theme to style any badge
		 * @values "positive", "negative", "notice", "warning", "info", "passive"
		 */
		theme: {
			type: String,
			default: "passive"
		}
	},
	computed: {
		buttons() {
			return this.tabs.map(this.button);
		},
		current() {
			const tab =
				this.tabs.find((tab) => tab.name === this.tab) ?? this.tabs[0];
			return tab?.name;
		}
	},
	methods: {
		button(tab) {
			const button = {
				...tab,
				current: tab.name === this.current,
				title: tab.label,
				text: tab.label ?? tab.text ?? tab.name
			};

			if (typeof tab.badge === "string" || typeof tab.badge === "number") {
				button.badge = {
					text: tab.badge
				};
			}

			if (button.badge) {
				button.badge.theme ??= this.theme;
			}

			if (tab.badge === false) {
				delete button.badge;
			}

			return button;
		},
		hasCurrent(offset) {
			return !!this.buttons
				.slice(offset)
				.find((button) => this.current === button.name);
		}
	}
};
</script>

<style>
.k-tabs {
	--button-height: var(--height-md);
	--button-padding: var(--spacing-2);
	display: flex;
	gap: var(--spacing-1);
	margin-bottom: var(--spacing-12);
	margin-inline: calc(var(--button-padding) * -1);
}

.k-tabs-button.k-button {
	position: relative;
	margin-block: 2px;
	overflow-x: visible;
}

.k-tabs-button[aria-current="true"]::after {
	position: absolute;
	content: "";
	height: 2px;
	inset-inline: var(--button-padding);
	bottom: -2px;
	background: var(--color-text);
}

.k-tabs-button .k-button-badge {
	top: 3px;
	inset-inline-end: calc(var(--button-padding) / 4);
}
</style>
