<template>
	<k-lab-examples>
		<k-lab-example label="External URL">
			<k-grid style="--columns: 3; gap: var(--spacing-6)">
				<k-image-frame
					v-for="ratio in ratios"
					:key="ratio"
					:ratio="ratio"
					:src="srcset[0]"
					:srcset="srcset.join(',')"
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
					:src="srcset[0]"
					:srcset="srcset.join(',')"
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
					:src="srcset[0]"
					:srcset="srcset.join(',')"
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
		},
		srcset() {
			const uuid = this.$helper.uuid();

			return [200, 400, 800, 1200, 2400].map((size) => {
				return `https://picsum.photos/${size}/${Math.round(size * 1.5)}?t=${uuid} ${size}w`;
			});
		}
	}
};
</script>
