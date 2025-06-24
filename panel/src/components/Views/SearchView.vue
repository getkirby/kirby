<template>
	<k-panel-inside class="k-search-view">
		<k-header>
			{{ $t("search") }}

			<k-input
				ref="input"
				slot="buttons"
				:aria-label="$t('search')"
				:autofocus="true"
				:icon="isLoading ? 'loader' : 'search'"
				:placeholder="$t('search') + ' …'"
				:spellcheck="false"
				:value="query"
				class="k-search-view-input"
				type="text"
				@input="query = $event"
			/>
		</k-header>
		<k-tabs :tab="currentType.id" :tabs="tabs" />

		<div class="k-search-view-results">
			<k-collection
				:items="results"
				:empty="{
					icon: isLoading ? 'loader' : 'search',
					text: empty
				}"
				:pagination="pagination"
				@paginate="onPaginate"
			/>
		</div>
	</k-panel-inside>
</template>

<script>
import Search from "@/mixins/search.js";

/**
 * @internal
 * @since 4.0.0
 */
export default {
	mixins: [Search],
	props: {
		type: {
			default: "pages",
			type: String
		}
	},
	data() {
		return {
			query: new URLSearchParams(window.location.search).get("query"),
			pagination: {},
			results: []
		};
	},
	computed: {
		currentType() {
			return (
				this.$panel.searches[this.type] ??
				Object.values(this.$panel.searches)[0]
			);
		},
		empty() {
			if (this.isLoading) {
				return this.$t("searching") + "…";
			}

			if (this.query.length < 2) {
				return this.$t("search.min", { min: 2 });
			}

			return this.$t("search.results.none");
		},
		isLoading() {
			return this.$panel.searcher.isLoading;
		},
		tabs() {
			const tabs = [];

			for (const id in this.$panel.searches) {
				const type = this.$panel.searches[id];

				tabs.push({
					label: type.label,
					link: "/search/?type=" + id + "&query=" + this.query,
					name: id
				});
			}

			return tabs;
		}
	},
	watch: {
		isLoading(state) {
			this.$panel.isLoading = state;
		},
		query: {
			handler() {
				// reload results when query changes
				// and reset pagination
				this.search(1);
			},
			immediate: true
		},
		type() {
			this.search();
		}
	},
	methods: {
		focus() {
			this.$refs.input?.focus();
		},
		onPaginate(pagination) {
			this.search(pagination.page);
		},
		async search(page) {
			if (!page) {
				page = new URLSearchParams(window.location.search).get("page") ?? 1;
			}

			const url = this.$panel.url(window.location, {
				type: this.currentType.id,
				query: this.query,
				page
			});

			window.history.pushState("", "", url.toString());

			const response = await this.$panel.search(
				this.currentType.id,
				this.query,
				{
					page,
					limit: 15
				}
			);

			if (response) {
				this.results = response.results ?? [];
				this.pagination = response.pagination;
			}
		}
	}
};
</script>

<style>
/* if not tabs are displayed, add space */
.k-header + .k-search-view-results {
	margin-top: var(--spacing-12);
}

.k-search-view-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	--input-height: var(--height-md);
	width: 40cqw;
}
</style>
