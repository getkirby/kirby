<template>
	<nav class="k-selector" role="search">
		<input
			ref="input"
			:placeholder="placeholder"
			:value="query"
			class="k-selector-input"
			type="search"
			@input="query = $event.target.value"
			@keydown.down.prevent="down"
			@keydown.escape.prevent="escape()"
			@keydown.enter.prevent="select(selected)"
			@keydown.tab="tab"
			@keydown.up.prevent="up"
		/>

		<template v-if="results.length || showCreateButton">
			<div class="k-selector-body">
				<div class="k-selector-results">
					<template v-if="results.length">
						<k-button
							v-for="(result, key) in results"
							:key="key"
							:current="selected === key"
							:disabled="result.disabled"
							icon="tag"
							class="k-selector-button"
							@focus.native="select(key)"
						>
							{{ result.text }}
						</k-button>
					</template>
					<template v-else-if="query?.length && options.length">
						<p class="k-selector-empty">No matches</p>
					</template>
				</div>
			</div>

			<footer v-if="showCreateButton" class="k-selector-footer">
				<k-button
					:current="selected === results.length"
					icon="add"
					class="k-selector-button"
					@focus.native="select(results.length)"
				>
					Add: <span class="k-selector-preview">{{ query }}</span>
				</k-button>
			</footer>
		</template>
	</nav>
</template>

<script>
import Search from "@/mixins/search.js";

export default {
	mixins: [Search],
	props: {
		add: {
			default: true,
			type: Boolean
		},
		delay: {
			default: 0
		},
		options: Array,
		placeholder: {
			default() {
				return this.$t("search");
			}
		}
	},
	data() {
		return {
			selected: -1,
			results: this.options
		};
	},
	watch: {
		selected() {
			if (this.selected === -1) {
				this.focus();
			}
		}
	},
	computed: {
		showCreateButton() {
			if (this.add === false) {
				return false;
			}

			if (!this.query?.length) {
				return false;
			}

			const matches = this.results.filter((result) => {
				return result.text === this.query || result.value === this.query;
			});

			return matches.length === 0;
		}
	},
	methods: {
		down() {
			this.pick(this.selected + 1);
		},
		escape() {
			this.selected = -1;
			this.focus();
			this.$emit("escape");
		},
		focus() {
			this.$refs.input.focus();
		},
		pick(index) {
			const max = this.showCreateButton
				? this.results.length
				: this.results.length - 1;
			const min = -1;

			if (index > max || index < min) {
				return false;
			}

			this.selected = index;
			this.focus();
		},
		async search(query) {
			if (query !== undefined) {
				this.query = query;
			}

			this.selected = -1;
			this.results = this.$helper.array.search(this.options, this.query, {
				field: "text",
				limit: this.limit
			});

			// select the create button if there are no results
			if (this.showCreateButton === true && this.results.length === 0) {
				this.selected = this.results.length;
			}
		},
		select(index) {
			this.pick(index);

			const value = this.results[this.selected];

			if (value) {
				this.$emit("select", value);
			} else if (this.showCreateButton) {
				this.$emit("create", this.query);
			}
		},
		tab(event) {
			event.preventDefault();

			if (event.shiftKey) {
				this.up();
			} else {
				this.down();
			}
		},
		up() {
			this.pick(this.selected - 1);
		}
	}
};
</script>

<style>
.k-selector-input {
	height: var(--height-sm);
	padding: 0 var(--button-padding);
	border-radius: var(--rounded-sm);
}
.k-selector:has([aria-current]) .k-selector-input:focus {
	outline: 0;
}
.k-selector-empty {
	height: var(--height-sm);
	display: flex;
	align-items: center;
	padding-inline: var(--button-padding);
	color: var(--color-text-dimmed);
}
.k-selector-button {
	--button-height: var(--height-sm);
	--button-align: start;
}
.k-selector-button[aria-current] {
	outline: var(--outline);
}
.k-selector-preview {
	display: inline-flex;
	font-weight: var(--font-bold);
}
</style>
