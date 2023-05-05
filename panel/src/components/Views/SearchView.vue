<template>
	<k-inside>
		<k-view class="k-search-view">
			<k-header>
				<!-- Type select -->
				<k-dropdown class="k-search-types">
					<k-button
						:icon="currentType.icon"
						:text="currentType.label"
						@click="$refs.types.toggle()"
					/>
					<k-dropdown-content ref="types">
						<k-dropdown-item
							v-for="(typeItem, typeIndex) in $panel.searches"
							:key="typeIndex"
							:icon="typeItem.icon"
							@click="
								type = typeIndex;
								focus();
							"
						>
							{{ typeItem.label }}
						</k-dropdown-item>
					</k-dropdown-content>
				</k-dropdown>

				<!-- Input -->
				<input
					ref="input"
					:aria-label="$t('search')"
					:autofocus="true"
					:placeholder="$t('search') + ' …'"
					:value="q"
					type="text"
					@input="search($event.target.value)"
					@keydown.esc="clear"
				/>
			</k-header>

			<div class="k-search-results">
				<!-- Results -->
				<k-collection v-if="items.length" ref="items" :items="items" />

				<!-- No results -->
				<k-empty v-else icon="search">
					{{ $t("search.results.none") }}
				</k-empty>
			</div>
		</k-view>
	</k-inside>
</template>

<script>
import { search } from "@/components/Dialogs/SearchDialog.vue";

export default {
	mixins: [search],
	data() {
		return {
			q: window.panel.view.query.q,
			type: window.panel.view.query.type ?? "page"
		};
	},
	watch: {
		q: {
			handler(query) {
				this.search(query);
			},
			immediate: true
		}
	}
};
</script>

<style>
.k-search-view .k-headline {
	display: flex;
	gap: 1rem;
}
.k-search-view input {
	background: transparent;
	border: none;
	padding: var(--spacing-1);
}
</style>
