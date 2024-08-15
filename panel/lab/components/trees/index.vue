<template>
	<k-lab-examples>
		<k-lab-example label="Page tree">
			<k-page-tree :current="selected?.value" @select="selected = $event" />
		</k-lab-example>
		<k-lab-example label="Pre-load current">
			<k-page-tree
				current="sections/pages/test-page-b"
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
