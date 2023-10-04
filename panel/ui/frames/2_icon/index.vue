<template>
	<k-ui-examples>
		<k-ui-example label="icon">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-icon-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					icon="heart"
					theme="passive"
				/>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="emoji">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-icon-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					icon="💛"
					theme="passive"
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
