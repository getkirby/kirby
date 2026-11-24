<template>
	<k-lab-examples>
		<k-lab-example label="External URL">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					:src="`https://picsum.photos/600/850?t=` + $helper.uuid()"
					back="pattern"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example v-if="id" label="Internal file UUID">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:cover="true"
					:file="id"
					:ratio="ratio"
					back="pattern"
				/>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="back">
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
		</k-lab-example>
		<k-lab-example label="cover">
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
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		id: String
	},
	computed: {
		ratios() {
			return ["1/1", "4/3", "16/9"];
		}
	}
};
</script>
