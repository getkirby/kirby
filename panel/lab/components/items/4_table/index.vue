<template>
	<k-lab-examples>
		<k-lab-example label="table">
			<k-items
				:columns="{
					image: {
						label: '',
						type: 'image',
						width: 'var(--table-row-height)'
					},
					text: {
						label: 'Text',
						type: 'text'
					},
					info: {
						label: 'Info',
						type: 'text'
					}
				}"
				:items="items"
				:sortable="true"
				layout="table"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		items: Array
	}
};
</script>
