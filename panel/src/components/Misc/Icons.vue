<template>
	<svg
		aria-hidden="true"
		class="k-icons"
		xmlns="http://www.w3.org/2000/svg"
		overflow="hidden"
	>
		<defs>
			<!-- eslint-disable vue/no-v-html -->
			<symbol
				v-for="(icon, type) in $options.icons"
				:id="'icon-' + type"
				:key="type"
				viewBox="0 0 24 24"
				v-html="icon"
			/>
		</defs>
	</svg>
</template>

<script>
/**
 * Component to load all icons from plugins
 * @internal
 */
export default {
	icons: window.panel.plugins.icons
};
</script>

<style>
.k-icons {
	position: absolute;
	width: 0;
	height: 0;
}
</style>
