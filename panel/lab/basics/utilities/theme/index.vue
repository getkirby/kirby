<template>
	<k-lab-examples>
		<k-lab-example label="data-theme">
			<k-text>
				<p>
					The <code>data-theme</code> attribute can be slapped on any element to
					apply pre-defined theme colors via CSS. The element receives the
					following CSS variables that you can then use for your styles:
				</p>
				<k-code language="css">{{ variables }}</k-code>
			</k-text>
		</k-lab-example>

		<k-lab-example
			v-for="theme in themes"
			:key="theme.name"
			:label="label(theme)"
		>
			<div class="k-theming-example" :data-theme="theme.name">
				<k-icon type="star" />
				<p class="k-theming-example-text">Example Text</p>
				<p class="k-theming-example-text-dimmed">Example Text Dimmed</p>
				<k-box icon="box" :theme="theme.name">Example Box</k-box>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		themes() {
			return [
				{
					name: "blue",
					alias: "info"
				},
				{
					name: "green",
					alias: "positive"
				},
				{
					name: "yellow",
					alias: "warning"
				},
				{
					name: "orange",
					alias: "notice"
				},
				{
					name: "red",
					alias: "error|negative"
				},
				{
					name: "pink",
					alias: "love"
				},
				{
					name: "aqua"
				},
				{
					name: "purple"
				},
				{
					name: "gray",
					alias: "passive"
				},
				{
					name: "white"
				}
			];
		},
		variables() {
			return `
	--theme-color-100
	--theme-color-150
	--theme-color-200
	--theme-color-250
	--theme-color-300
	--theme-color-350
	--theme-color-400
	--theme-color-450
	--theme-color-500
	--theme-color-550
	--theme-color-600
	--theme-color-650
	--theme-color-700
	--theme-color-750
	--theme-color-800
	--theme-color-850
	--theme-color-900
	--theme-color-950

	--theme-color-back
	--theme-color-border
	--theme-color-hover
	--theme-color-icon
	--theme-color-icon-highlight
	--theme-color-text
	--theme-color-text-dimmed
	--theme-color-text-highlight
			`;
		}
	},
	methods: {
		label(theme) {
			if (theme.alias) {
				return `data-theme=${theme.name}|${theme.alias}`;
			}

			return `data-theme=${theme.name}`;
		}
	}
};
</script>

<style>
.k-theming-example {
	display: flex;
	align-items: center;
	gap: var(--spacing-6);
}
.k-theming-example > svg {
	color: var(--theme-color-icon);
}
.k-theming-example p {
	white-space: nowrap;
}
.k-theming-example-text {
	color: var(--theme-color-text);
}
.k-theming-example-text-dimmed {
	color: var(--theme-color-text-dimmed);
}
</style>
