<template>
	<k-lab-examples>
		<k-lab-example label="Page tree">
			<k-page-tree :current="selected?.value" @select="selected = $event" />
		</k-lab-example>

		<k-lab-example label="Page tree: limit 5">
			<k-page-tree
				:current="selected?.value"
				:limit="5"
				@select="selected = $event"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			selected: null
		};
	}
};
</script>
