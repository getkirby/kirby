<template>
	<!-- eslint-disable -->
	<svg
		aria-hidden="true"
		class="k-icons"
		xmlns="http://www.w3.org/2000/svg"
		overflow="hidden"
	>
		<defs>
			<symbol
				v-for="(icon, type) in $panel.plugins.icons"
				:key="type"
				:id="'icon-' + type"
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
export default {};
</script>

<style>
.k-icons {
	position: absolute;
	width: 0;
	height: 0;
}
</style>
