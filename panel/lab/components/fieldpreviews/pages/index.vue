<template>
	<k-lab-examples>
		<k-lab-example label="Value as item objects">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview
					:value="[
						{
							image: { icon: 'page', back: 'black', color: 'white' },
							text: 'Projects',
							link: '/'
						},
						{
							image: { icon: 'page', back: 'black', color: 'white' },
							text: 'Blog',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example v-if="ids.length" label="Value as ID strings">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview :value="ids" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Overflow">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview
					:value="
						Array(20).fill({
							image: { icon: 'page', back: 'black', color: 'white' },
							text: 'Projects',
							link: '/'
						})
					"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Long title">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview
					:value="[
						{
							image: { icon: 'page', back: 'black', color: 'white' },
							text: 'Blog',
							link: '/'
						},
						{
							image: { icon: 'page', back: 'black', color: 'white' },
							text: 'A page with a very long title to test how previews handle overflow',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="HTML">
			<k-lab-table-cell>
				<!-- @code -->
				<k-pages-field-preview
					:value="[
						{
							image: { icon: 'page', back: 'black', color: 'white' },
							text: '<b>Projects</b> (4)',
							link: '/'
						}
					]"
				/>
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		ids: {
			type: Array,
			default: () => []
		}
	}
};
</script>
