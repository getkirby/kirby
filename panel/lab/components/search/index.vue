<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-search />
		</k-lab-example>
		<k-lab-example label="Types">
			<k-search :types="types" />
		</k-lab-example>
		<k-lab-example label="Default type">
			<k-search :types="types" default-type="files" />
		</k-lab-example>
		<k-lab-example label="No results">
			<k-search :results="[]" />
		</k-lab-example>
		<k-lab-example label="Results">
			<k-search :results="results" />
		</k-lab-example>
		<k-lab-example label="More results">
			<k-search :results="results" :pagination="{ total: 20 }" />
		</k-lab-example>
		<k-lab-example label="Is Loading">
			<k-search :is-loading="true" :results="results" :types="types" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		results() {
			return [
				{ text: "Result A" },
				{ text: "Result B" },
				{ text: "Result C" },
				{ text: "Result D" }
			];
		},
		types() {
			return {
				pages: {
					id: "pages",
					icon: "page",
					label: "Pages"
				},
				files: {
					id: "files",
					icon: "file",
					label: "Files"
				}
			};
		}
	}
};
</script>

<style>
.k-search-bar {
	background: var(--color-gray-100);
}
</style>
