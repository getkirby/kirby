<template>
	<k-ui-examples>
		<k-ui-example label="z">
			<div>
				<k-box
					v-for="(index, x) in z"
					:key="index"
					:label="`z-${index}`"
					:style="`
						z-index: var(--z-${index});
						font-size: var(--text-xs);
						font-family: var(--font-mono);
						inset-block-start: ${x * 24}px;
						inset-inline-start: ${x * 24}px;
					`"
					theme="white"
				>
					z-{{ index }}
				</k-box>
			</div>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		z: Array,
	},
};
</script>

<style>
.k-ui-example-canvas > div {
	position: relative;
}
.k-ui-example .k-box {
	position: absolute;
	width: auto;
	box-shadow: var(--shadow-xl);
}
</style>
