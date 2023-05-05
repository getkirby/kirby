<template>
	<k-overlay
		ref="dialog"
		:dimmed="true"
		:visible="visible"
		type="dialog"
		@cancel="cancel"
		@ready="ready"
	>
		<form
			class="k-search-dialog k-dialog"
			data-size="medium"
			method="dialog"
			role="search"
			@submit.prevent="submit"
		>
			<div class="k-search-input">
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
					:placeholder="$t('search') + ' …'"
					:aria-label="$t('search')"
					:autofocus="true"
					type="text"
					@input="search($event.target.value)"
					@keydown.down.prevent="onDown"
					@keydown.up.prevent="onUp"
					@keydown.tab.prevent="onTab"
					@keydown.enter="onEnter"
					@keydown.esc="
						clear();
						close();
					"
				/>
				<k-button
					:icon="isLoading ? 'loader' : 'cancel'"
					:tooltip="$t('close')"
					class="k-search-close"
					@click="close"
				/>
			</div>

			<div v-if="q?.length > 1" class="k-search-results">
				<!-- Results -->
				<k-collection
					v-if="items.length"
					ref="items"
					:items="items"
					@mouseout.native="select(-1)"
				/>

				<!-- No results -->
				<p v-else class="k-search-empty">
					{{ $t("search.results.none") }}
				</p>
			</div>
		</form>
	</k-overlay>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import debounce from "@/helpers/debounce.js";

export const search = {
	data() {
		return {
			isLoading: false,
			items: [],
			q: null,
			selected: -1,
			type: this.$panel.view.search
		};
	},
	computed: {
		currentType() {
			return (
				this.$panel.searches[this.type] ??
				Object.values(this.$panel.searches)[0]
			);
		}
	},
	watch: {
		type() {
			this.search(this.q);
		}
	},
	created() {
		this.search = debounce(this.search, 250);
	},
	methods: {
		clear() {
			this.items = [];
			this.q = null;
		},
		focus() {
			this.$refs.input?.focus();
		},
		async search(query) {
			this.q = query;
			this.isLoading = true;
			this.$refs.types?.close();
			this.select(-1);

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
				this.isLoading = false;
			}
		}
	}
};

export default {
	mixins: [Dialog, search],
	created() {
		this.$events.$on("keydown.cmd.shift.f", this.open);
		this.$events.$on("keydown.cmd./", this.open);
	},
	destroyed() {
		this.$events.$off("keydown.cmd.shift.f", this.open);
		this.$events.$off("keydown.cmd./", this.open);
	},
	methods: {
		navigate(item) {
			if (item) {
				this.$go(item.link);
				this.close();
			}
		},
		onDown() {
			if (this.selected < this.items.length - 1) {
				this.select(this.selected + 1);
			}
		},
		onEnter() {
			this.navigate(this.items[this.selected] ?? this.items[0]);
		},
		onTab() {
			this.navigate(this.items[this.selected]);
		},
		onUp() {
			if (this.selected >= 0) {
				this.select(this.selected - 1);
			}
		},
		select(index) {
			this.selected = index;
			const items = this.$refs.items?.$el.querySelectorAll(".k-item") ?? [];
			[...items].forEach((item) => delete item.dataset.selected);

			if (index >= 0) {
				items[index].dataset.selected = true;
			}
		}
	}
};
</script>

<style>
.k-search-dialog {
	margin: 2.5rem auto;
}
.k-search-input {
	display: flex;
}
.k-search-types {
	flex-shrink: 0;
	display: flex;
}
.k-search-types > .k-button {
	padding-inline-start: 1rem;
	font-size: var(--text-base);
	line-height: 1;
	height: 2.5rem;
}
.k-search-types > .k-button .k-icon {
	height: 2.5rem;
}
.k-search-types > .k-button .k-button-text {
	opacity: 1;
	font-weight: 500;
}
.k-search-input input {
	background: none;
	flex-grow: 1;
	font: inherit;
	padding: 0.75rem;
	border: 0;
	height: 2.5rem;
}
.k-search-close {
	width: 3rem;
	line-height: 1;
}
.k-search-close .k-icon-loader {
	animation: Spin 2s linear infinite;
}
.k-search-dialog input:focus {
	outline: 0;
}

.k-search-results {
	padding: 0.5rem 1rem 1rem;
}
.k-search-dialog .k-item:not(:last-child) {
	margin-bottom: 0.25rem;
}
.k-search-dialog .k-item[data-selected="true"] {
	outline: 2px solid var(--color-focus);
}
.k-search-dialog-search .k-item-info {
	font-size: var(--text-xs);
}

.k-search-empty {
	text-align: center;
	font-size: var(--text-xs);
	color: var(--color-gray-600);
}
</style>
