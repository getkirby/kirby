<template>
	<nav v-if="tabs && tabs.length > 1" class="k-tabs">
		<k-button
			v-for="tabButton in visibleTabs"
			:key="tabButton.name"
			:link="tabButton.link"
			:current="current === tabButton.name"
			:icon="tabButton.icon"
			:title="tabButton.label"
			variant="dimmed"
			class="k-tab-button"
		>
			{{ tabButton.label ?? tabButton.text ?? tabButton.name }}

			<span v-if="tabButton.badge" :data-theme="theme" class="k-tabs-badge">
				{{ tabButton.badge }}
			</span>
		</k-button>

		<k-button
			v-if="invisibleTabs.length"
			:current="invisibleTabs.find((tabButton) => tab === tabButton.name)"
			:title="$t('more')"
			class="k-tab-button k-tabs-dropdown-button"
			icon="dots"
			variant="dimmed"
			@click.stop="$refs.more.toggle()"
		/>
		<k-dropdown-content
			v-if="invisibleTabs.length"
			ref="more"
			align-x="end"
			class="k-tabs-dropdown"
		>
			<k-dropdown-item
				v-for="tabButton in invisibleTabs"
				:key="'more-' + tabButton.name"
				:link="tabButton.link"
				:current="tab === tabButton.name"
				:icon="tabButton.icon"
				:title="tabButton.label"
			>
				{{ tabButton.label ?? tabButton.text ?? tabButton.name }}
			</k-dropdown-item>
		</k-dropdown-content>
	</nav>
</template>

<script>
export default {
	props: {
		tab: String,
		tabs: Array,
		theme: String
	},
	data() {
		return {
			size: null,
			visibleTabs: this.tabs,
			invisibleTabs: []
		};
	},
	computed: {
		current() {
			const tab =
				this.tabs.find((tab) => tab.name === this.tab) ?? this.tabs[0] ?? {};
			return tab.name;
		}
	},
	watch: {
		tabs: {
			handler(tabs) {
				this.visibleTabs = tabs;
				this.invisibleTabs = [];
				this.resize(true);
			},
			immediate: true
		}
	},
	created() {
		window.addEventListener("resize", this.resize);
	},
	destroyed() {
		window.removeEventListener("resize", this.resize);
	},
	methods: {
		resize(force) {
			if (!this.tabs || this.tabs.length <= 1) {
				return;
			}

			if (this.tabs.length <= 3) {
				this.visibleTabs = this.tabs;
				this.invisibleTabs = [];
				return;
			}

			if (window.innerWidth >= 700) {
				if (this.size === "large" && !force) {
					return;
				}

				this.visibleTabs = this.tabs;
				this.invisibleTabs = [];
				this.size = "large";
			} else {
				if (this.size === "small" && !force) {
					return;
				}

				this.visibleTabs = this.tabs.slice(0, 2);
				this.invisibleTabs = this.tabs.slice(2);
				this.size = "small";
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
	margin-inline-start: calc(var(--button-padding) * -1);
}

.k-tab-button.k-button {
	margin-block: 2px;
	overflow-x: visible;
}

.k-tab-button[aria-current]::after {
	position: absolute;
	content: "";
	height: 2px;
	inset-inline: var(--button-padding);
	bottom: -2px;
	background: currentColor;
}

.k-tabs-badge {
	position: absolute;
	top: 2px;
	font-variant-numeric: tabular-nums;
	inset-inline-end: var(--button-padding);
	transform: translateX(75%);
	line-height: 1.5;
	padding: 0 0.25rem;
	border-radius: 1rem;
	text-align: center;
	font-size: 10px;
	box-shadow: var(--shadow-md);
	background: var(--theme-color-back);
	border: 1px solid var(--theme-color-500);
	color: var(--theme-color-text);
	z-index: 1;
}
</style>
