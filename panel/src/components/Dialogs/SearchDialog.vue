<template>
	<k-dialog
		:cancel-button="false"
		:submit-button="false"
		:visible="true"
		class="k-search-dialog"
		role="search"
		size="medium"
		@cancel="$emit('cancel')"
		@submit="submit"
	>
		<k-search-bar
			ref="search"
			:default-type="type ?? $panel.view.search"
			:is-loading="$panel.searcher.isLoading"
			:pagination="pagination"
			:results="results"
			:types="$panel.searches"
			@close="close"
			@more="$go('search', { query: $event })"
			@navigate="navigate"
			@search="search"
		/>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		type: String
	},
	emits: ["cancel"],
	data() {
		return {
			results: null,
			pagination: {}
		};
	},
	methods: {
		focus() {
			this.$refs.search?.focus();
		},
		navigate(result) {
			if (result) {
				this.$go(result.link);
				this.close();
			}
		},
		async search({ type, query }) {
			const response = await this.$panel.search(type, query);

			if (response) {
				this.results = response.results;
				this.pagination = response.pagination;
			}
		}
	}
};
</script>

<style>
.k-search-dialog {
	--dialog-padding: 0;
	--dialog-rounded: var(--rounded);
	overflow: visible;
}
.k-overlay[open][data-type="dialog"] > .k-portal > .k-search-dialog {
	margin-top: 0;
}
</style>
