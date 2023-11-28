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
				v-for="(icon, type) in $options.icons"
				:key="type"
				:id="'icon-' + type"
				:viewBox="viewbox(type, icon)"
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
	icons: window.panel.plugins.icons,
	methods: {
		/**
		 * @deprecated 4.0.0
		 * @todo switch to only supporting `0 0 24 24` viewbox in v5
		 */
		viewbox(name, path) {
			const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
			svg.innerHTML = path;
			document.body.appendChild(svg);
			const bbox = svg.getBBox();
			const width = bbox.width + bbox.x * 2;
			const height = bbox.height + bbox.y * 2;
			const average = (width + height) / 2;
			const distanceTo16 = Math.abs(average - 16);
			const distanceTo24 = Math.abs(average - 24);
			document.body.removeChild(svg);

			if (distanceTo16 < distanceTo24) {
				window.panel.deprecated(
					`Custom icon "${name}" seems to use a 16x16 viewbox which has been deprecated. In an upcoming version, Kirby will only support custom icons with a 24x24 viewbox by default. If you want to continue using icons with a different viewport, please wrap them in an <svg> element with the corresponding viewBox attribute.`
				);

				return "0 0 16 16";
			}

			return "0 0 24 24";
		}
	}
};
</script>

<style>
.k-icons {
	position: absolute;
	width: 0;
	height: 0;
}
</style>
