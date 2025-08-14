<template>
	<div class="k-search-bar">
		<div class="k-search-bar-input">
			<!-- Type select -->
			<template v-if="typesDropdown.length > 1">
				<k-button
					:dropdown="true"
					:icon="types[type].icon"
					:text="types[type].label"
					variant="dimmed"
					class="k-search-bar-types"
					@click="$refs.types.toggle()"
				/>
				<k-dropdown ref="types" :options="typesDropdown" />
			</template>

			<!-- Input -->
			<k-search-input
				ref="input"
				:aria-label="$t('search')"
				:autofocus="true"
				:value="query"
				@input="query = $event"
				@keydown.down.prevent="onDown"
				@keydown.up.prevent="onUp"
				@keydown.enter="onEnter"
			/>
			<k-button
				:icon="isLoading ? 'loader' : 'cancel'"
				:title="$t('close')"
				class="k-search-bar-close"
				@click="$emit('close')"
			/>
		</div>

		<div v-if="results" class="k-search-bar-results">
			<!-- Results -->
			<k-collection
				v-if="results.length"
				ref="results"
				:items="results"
				@mouseout="select(-1)"
			/>

			<footer class="k-search-bar-footer">
				<!-- No results -->
				<p v-if="results.length === 0">
					{{ $t("search.results.none") }}
				</p>

				<!-- More results then displayed -->
				<k-button
					v-if="results.length < pagination.total"
					icon="search"
					variant="dimmed"
					@click="$emit('more', { type, query })"
				>
					{{ $t("search.all", { count: pagination.total }) }}
				</k-button>
			</footer>
		</div>
	</div>
</template>

<script>
import Search from "@/mixins/search.js";

/**
 * @since 4.4.0
 */
export default {
	mixins: [Search],
	props: {
		defaultType: String,
		isLoading: Boolean,
		pagination: {
			type: Object,
			default: () => ({})
		},
		results: Array,
		types: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["close", "more", "navigate", "search"],
	data() {
		return {
			selected: -1,
			type: this.types[this.defaultType]
				? this.defaultType
				: Object.keys(this.types)[0]
		};
	},
	computed: {
		typesDropdown() {
			return Object.values(this.types).map((search) => ({
				...search,
				current: this.type === search.id,
				click: () => {
					this.type = search.id;
					this.focus();
				}
			}));
		}
	},
	watch: {
		type() {
			this.search();
		}
	},
	methods: {
		focus() {
			this.$refs.input?.focus();
		},
		onDown() {
			this.select(Math.min(this.selected + 1, this.results.length - 1));
		},
		onEnter() {
			this.$emit("navigate", this.results[this.selected] ?? this.results[0]);
		},
		onUp() {
			this.select(Math.max(this.selected - 1, -1));
		},
		async search() {
			this.$refs.types?.close();
			this.select?.(-1);

			this.$emit("search", { type: this.type, query: this.query });
		},
		select(index) {
			this.selected = index;
			const items = this.$refs.results?.$el.querySelectorAll(".k-item") ?? [];

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
.k-search-bar-input {
	--button-height: var(--input-height);
	display: flex;
	align-items: center;
}
.k-search-bar-types {
	flex-shrink: 0;
	border-inline-end: 1px solid var(--color-border);
}
.k-search-bar-input input {
	flex-grow: 1;
	padding-inline: 0.75rem;
	height: var(--input-height);
	line-height: var(--input-height);
	border-radius: var(--rounded);
	font-size: var(--input-font-size);
}
.k-search-bar-input input:focus {
	outline: 0;
}

.k-search-bar-input .k-search-bar-close {
	flex-shrink: 0;
}

.k-search-bar-results {
	border-top: 1px solid var(--color-border);
	padding: 1rem;
}
.k-search-bar-results .k-item[data-selected="true"] {
	outline: var(--outline);
}

.k-search-bar-footer {
	text-align: center;
}
.k-search-bar-footer p {
	color: var(--color-text-dimmed);
}
.k-search-bar-footer .k-button {
	margin-top: var(--spacing-4);
}
</style>
