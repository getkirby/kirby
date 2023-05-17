<template>
	<k-inside class="k-search-view">
		<k-header>Search</k-header>

		<div class="k-search-view-layout">
			<aside class="k-search-view-types">
				<nav>
					<k-button
						v-for="(typeItem, typeIndex) in $panel.searches"
						:key="typeIndex"
						:current="type === typeIndex"
						:icon="typeItem.icon"
						:link="'/search/?type=' + typeIndex + '&query=' + query"
						class="k-search-view-type"
					>
						{{ typeItem.label }}
					</k-button>
				</nav>
			</aside>

			<k-input
				ref="input"
				:aria-label="$t('search')"
				:autofocus="autofocus"
				:placeholder="$t('search') + ' …'"
				:value="query"
				class="k-search-view-input"
				icon="search"
				type="text"
				@input="query = $event"
			/>

			<div class="k-search-view-results">
				<k-collection
					v-if="query"
					:items="items"
					:empty="{
						icon: 'search',
						text: $t('search.results.none')
					}"
				/>
			</div>
		</div>
	</k-inside>
</template>

<script>
export default {
	props: {
		type: {
			default: "pages",
			type: String
		}
	},
	data() {
		return {
			query: this.getQuery(),
			items: []
		};
	},
	updated() {
		this.query = this.getQuery();
		this.focus();
	},
	watch: {
		query: {
			handler(query) {
				this.search(query);
			},
			immediate: true
		},
		type() {
			this.search(this.query);
		}
	},
	methods: {
		focus() {
			this.$refs.input?.focus();
		},
		getQuery() {
			return new URLSearchParams(window.location.search).get("query");
		},
		async search(query) {
			this.$panel.isLoading = true;

			const url = this.$panel.url(window.location, {
				type: this.type,
				query: this.query
			});

			window.history.pushState("", "", url.toString());

			try {
				// Skip API call if query empty
				if (query === null || query.length < 2) {
					throw Error("Empty query");
				}

				const response = await this.$search(this.type, query);
				this.items = response.results;
			} catch (error) {
				this.items = [];
			} finally {
				this.$panel.isLoading = false;
			}
		}
	}
};
</script>

<style>
.k-search-view .k-header {
	margin-bottom: var(--spacing-6);
}
.k-search-view-layout {
	display: grid;
	grid-template-columns: 15rem 1fr;
	grid-template-rows: var(--height-lg) 1fr;
	column-gap: 3rem;
	row-gap: 1.5rem;
	grid-template-areas:
		"types input"
		"types results";
}
.k-search-view-types {
	grid-area: types;
}
.k-search-view-types nav {
	display: flex;
	flex-direction: column;
	gap: 2px;
	background: var(--color-white);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
	padding: var(--spacing-1);
}
.k-search-view-type {
	display: flex;
	height: var(--height-sm);
	align-items: center;
	padding-inline: var(--spacing-2);
	border-radius: var(--rounded-sm);
}
.k-search-view-type[aria-current] {
	background: var(--color-blue-200);
}
.k-search-view-input {
	font: inherit;
	background: var(--color-gray-300);
	border: none;
	width: 100%;
	border-radius: var(--rounded);
	padding: var(--spacing-3);
	grid-area: input;
}
</style>
