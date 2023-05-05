<template>
	<k-inside>
		<k-view class="k-search-view">
			<k-header>
				{{ $t("search") }}: <strong>{{ query ?? "–" }}</strong>
			</k-header>

			<div class="k-search-results">
				<!-- Results -->
				<k-collection v-if="results.length" ref="items" :items="results" />

				<!-- No results -->
				<p v-else class="k-search-empty">
					{{ $t("search.results.none") }}
				</p>
			</div>
		</k-view>
	</k-inside>
</template>

<script>
export default {
	props: {
		query: String,
		results: Object
	}
};
</script>

<style>
.k-search-view-input {
	background: var(--color-white);
	padding: var(--spacing-2) var(--spacing-3);
	border-radius: var(--rounded);
	margin-bottom: var(--spacing-6);
}
</style>
