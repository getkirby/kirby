<template>
	<k-dialog
		:cancel-button="false"
		:submit-button="false"
		class="k-search-dialog"
		role="search"
		size="medium"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="submit"
	>
		<div class="k-search-dialog-input">
			<!-- Type select -->
			<k-dropdown class="k-search-dialog-types">
				<k-button
					:dropdown="true"
					:icon="currentType.icon"
					:text="currentType.label"
					variant="dimmed"
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
				:value="query"
				type="text"
				@input="query = $event.target.value"
				@keydown.down.prevent="onDown"
				@keydown.up.prevent="onUp"
				@keydown.enter="onEnter"
			/>
			<k-button
				:icon="isLoading ? 'loader' : 'cancel'"
				:title="$t('close')"
				class="k-search-dialog-close"
				@click="close"
			/>
		</div>

		<div v-if="query?.length > 1" class="k-search-dialog-results">
			<!-- Results -->
			<k-collection
				v-if="items.length"
				ref="items"
				:items="items"
				@mouseout.native="select(-1)"
			/>

			<!-- No results -->
			<footer class="k-search-dialog-footer">
				<p v-if="!items.length">
					{{ $t("search.results.none") }}
				</p>

				<k-button
					v-else-if="items.length < pagination.total"
					icon="search"
					variant="dimmed"
					@click="$go('search', { query: { type, query } })"
				>
					All {{ pagination.total }} results
				</k-button>
			</footer>
		</div>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import Search from "@/mixins/search.js";

export default {
	mixins: [Dialog, Search],
	emits: ["cancel"],
	data() {
		return {
			isLoading: false,
			items: [],
			pagination: {},
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
			this.search();
		}
	},
	methods: {
		clear() {
			this.items = [];
			this.query = null;
		},
		focus() {
			this.$refs.input?.focus();
		},
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
		async search() {
			this.isLoading = true;
			this.$refs.types?.close();
			this.select?.(-1);

			try {
				// Skip API call if query empty
				if (this.query === null || this.query.length < 2) {
					throw Error("Empty query");
				}

				const response = await this.$search(this.type, this.query);
				this.items = response.results;
				this.pagination = response.pagination;
			} catch (error) {
				this.items = [];
				this.pagination = {};
			} finally {
				this.isLoading = false;
			}
		},
		select(index) {
			this.selected = index;
			const items = this.$refs.items?.$el.querySelectorAll(".k-item") ?? [];

			for (const item of items) {
				delete item.dataset.selected;
			}

			if (index >= 0) {
				items[index].dataset.selected = true;
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

.k-search-dialog-input {
	--button-height: var(--input-height);
	display: flex;
	align-items: center;
}
.k-search-dialog-types {
	flex-shrink: 0;
}
.k-search-dialog-input input {
	flex-grow: 1;
	padding-inline: 0.75rem;
	height: var(--input-height);
	border-left: 1px solid var(--color-border);
	line-height: var(--input-height);
	border-radius: var(--rounded);
	font-size: var(--input-font-size);
}
.k-search-dialog-input input:focus {
	outline: 0;
}

.k-search-dialog-input .k-search-dialog-close {
	flex-shrink: 0;
}

.k-search-dialog-results {
	border-top: 1px solid var(--color-border);
	padding: 1rem;
}
.k-search-dialog-results .k-item[data-selected="true"] {
	outline: var(--outline);
}

.k-search-dialog-footer {
	text-align: center;
}
.k-search-dialog-footer p {
	color: var(--color-text-dimmed);
}
.k-search-dialog-footer .k-button {
	margin-top: var(--spacing-4);
}
</style>
