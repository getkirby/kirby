<template>
	<k-ui-examples>
		<k-ui-example label="image">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					:src="`https://picsum.photos/600/850?t=` + $helper.uuid()"
					back="pattern"
				/>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="back">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					:src="`https://picsum.photos/600/850?t=` + $helper.uuid()"
					back="black"
					theme="passive"
				/>
			</k-grid>
		</k-ui-example>
		<k-ui-example label="cover">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					:src="`https://picsum.photos/600/850?t=` + $helper.uuid()"
					:cover="true"
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
