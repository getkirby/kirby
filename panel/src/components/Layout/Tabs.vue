<template>
	<nav v-if="tabs.length > 1" class="k-tabs">
		<k-button
			v-for="btn in buttons"
			:key="btn.name"
			ref="visible"
			v-bind="btn"
			variant="dimmed"
			class="k-tabs-button"
		>
			{{ btn.text }}
		</k-button>

		<template v-if="invisible.length">
			<k-button
				:current="!!invisible.find((button) => tab === button.name)"
				:title="$t('more')"
				class="k-tabs-button k-tabs-dropdown-button"
				icon="dots"
				variant="dimmed"
				@click.stop="$refs.more.toggle()"
			/>
			<k-dropdown
				ref="more"
				:options="dropdown"
				align-x="end"
				class="k-tabs-dropdown"
			/>
		</template>
	</nav>
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
	data() {
		return {
			observer: null,
			visible: this.tabs,
			invisible: []
		};
	},
	computed: {
		buttons() {
			return this.visible.map(this.button);
		},
		current() {
			const tab =
				this.tabs.find((tab) => tab.name === this.tab) ?? this.tabs[0];
			return tab?.name;
		},
		dropdown() {
			return this.invisible.map(this.button);
		}
	},
	watch: {
		tabs: {
			async handler() {
				// disconnect any previous observer
				this.observer?.disconnect();
				await this.$nextTick();

				// only if $el exists (more than one tab),
				// add new observer and measure tab sizes
				if (this.$el instanceof Element) {
					this.observer = new ResizeObserver(this.resize);
					this.observer.observe(this.$el);
				}
			},
			immediate: true
		}
	},
	unmounted() {
		this.observer?.disconnect();
	},
	methods: {
		button(tab) {
			const button = {
				...tab,
				current: tab.name === this.current,
				title: tab.label,
				text: tab.label ?? tab.text ?? tab.name
			};

			if (tab.badge) {
				button.badge = {
					theme: this.theme,
					text: tab.badge
				};
			} else {
				delete button.badge;
			}

			return button;
		},
		async resize() {
			// container width
			const width = this.$el.offsetWidth;

			// reset all tabs
			this.visible = this.tabs;
			this.invisible = [];

			// measure tab sizes
			await this.$nextTick();
			const sizes = [...this.$refs.visible].map((tab) => tab.$el.offsetWidth);

			// initial width of visible tabs
			// that already account for the dropdown button
			let tabs = 32;

			for (let index = 0; index < this.tabs.length; index++) {
				// tab size plus grid gap
				tabs += sizes[index] + 4;

				if (tabs > width) {
					this.visible = this.tabs.slice(0, index);
					this.invisible = this.tabs.slice(index);
					return;
				}
			}
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
