<template>
	<k-panel-inside class="k-search-view">
		<k-header>
			{{ $t("search") }}

			<k-input
				ref="input"
				slot="buttons"
				:aria-label="$t('search')"
				:autofocus="true"
				:placeholder="$t('search') + ' …'"
				:spellcheck="false"
				:value="query"
				class="k-search-view-input"
				icon="search"
				type="text"
				@input="query = $event"
			/>
		</k-header>
		<k-tabs :tab="currentType.id" :tabs="tabs" />

		<div class="k-search-view-results">
			<k-collection
				:items="items"
				:empty="{
					icon: 'search',
					text: $t('search.results.none')
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
			items: [],
			query: new URLSearchParams(window.location.search).get("query"),
			pagination: {}
		};
	},
	computed: {
		currentType() {
			return (
				this.$panel.searches[this.type] ??
				Object.values(this.$panel.searches)[0]
			);
		},
		tabs() {
			const tabs = [];

			for (const typeId in this.$panel.searches) {
				const type = this.$panel.searches[typeId];

				tabs.push({
					label: type.label,
					link: "/search/?type=" + typeId + "&query=" + this.query,
					name: typeId
				});
			}

			return tabs;
		}
	},
	watch: {
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
			this.$panel.isLoading = true;

			if (!page) {
				page = new URLSearchParams(window.location.search).get("page") ?? 1;
			}

			const url = this.$panel.url(window.location, {
				type: this.currentType.id,
				query: this.query,
				page: page > 1 ? page : null
			});

			window.history.pushState("", "", url.toString());

			try {
				// Skip API call if query empty
				if (this.query === null || this.query.length < 2) {
					throw Error("Empty query");
				}

				const response = await this.$search(this.currentType.id, this.query, {
					page,
					limit: 15
				});
				this.items = response.results;
				this.pagination = response.pagination;
			} catch (error) {
				this.items = [];
				this.pagination = {};
			} finally {
				this.$panel.isLoading = false;
			}
		}
	}
};
</script>

<style>
.k-search-view .k-header {
	margin-bottom: 0;
}

/* if not tabs are displayed, add space */
.k-header + .k-search-view-results {
	margin-top: var(--spacing-12);
}

.k-search-view-input {
	--input-color-border: transparent;
	--input-color-back: var(--color-gray-300);
	--input-height: var(--height-md);
	width: 40cqw;
}
</style>
