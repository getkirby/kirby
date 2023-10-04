<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame v-for="ratio in ratios" :key="ratio" :ratio="ratio" />
			</k-grid>
		</k-ui-example>
		<k-ui-example label="Solid Color">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					color="var(--color-purple-400)"
				/>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="Alpha Color">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					color="hsla(var(--color-purple-hs), var(--color-purple-l-600), .5)"
				/>
			</k-grid>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		ratios() {
			return ["1/1", "4/3", "16/9"];
		},
	},
};
</script>
