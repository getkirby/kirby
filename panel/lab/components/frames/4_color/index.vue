<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame v-for="ratio in ratios" :key="ratio" :ratio="ratio" />
			</k-grid>
		</k-lab-example>
		<k-lab-example label="Solid Color">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					color="var(--color-purple-400)"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="Alpha Color">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					color="hsla(var(--color-purple-hs), var(--color-purple-l-600), .5)"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="Pattern">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-color-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					color="var(--pattern)"
				/>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		ratios() {
			return ["1/1", "4/3", "16/9"];
		}
	}
};
</script>
