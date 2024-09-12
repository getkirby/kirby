<template>
	<k-lab-examples>
		<k-lab-example label="File browser">
			<k-file-browser :selected="selected?.value" @select="selected = $event" />
		</k-lab-example>
		<k-lab-example label="Pagination">
			<k-file-browser
				:limit="1"
				:selected="selected?.value"
				@select="selected = $event"
			/>
		</k-lab-example>
		<k-lab-example label="Page pre-opened">
			<k-file-browser
				:selected="selected?.value"
				opened="sections/files"
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
	},
	watch: {
		selected(value) {
			console.log(value);
		}
	}
};
</script>

<style>
.k-lab-example .k-headline {
	margin-bottom: 0.5rem;
}
.k-lab-example .k-file-browser {
	border: 1px solid var(--color-border);
	border-radius: var(--rounded);
}
</style>
