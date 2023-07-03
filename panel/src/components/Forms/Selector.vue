<template>
	<nav class="k-selector" role="search">
		<header class="k-selector-header">
			<input
				ref="input"
				:placeholder="inputPlaceholder + ' …'"
				:value="query"
				class="k-selector-input"
				type="search"
				@click="pick(-1)"
				@input="filter($event.target.value)"
				@keydown.down.prevent="down"
				@keydown.escape.prevent="escape()"
				@keydown.enter.prevent="select(selected)"
				@keydown.tab="tab"
				@keydown.up.prevent="up"
			/>
		</header>

		<div class="k-selector-body" v-if="options.length">
			<template v-if="filtered.length">
				<div ref="results" class="k-selector-results">
					<k-button
						v-for="(option, key) in filtered"
						:key="key"
						:current="selected === key"
						:disabled="option.disabled"
						:icon="option.icon ?? icon"
						class="k-selector-button"
						@focus.native="select(key)"
					>
						<span v-html="option.highlighted ?? option.text" />
					</k-button>
				</div>
			</template>
			<template v-else>
				<p class="k-selector-empty">{{ empty }}</p>
			</template>
		</div>

		<footer v-if="showCreateButton" class="k-selector-footer">
			<k-button
				:current="selected === filtered.length"
				icon="add"
				class="k-selector-button"
				@focus.native="select(filtered.length)"
			>
				{{ $t("add") }}: <span class="k-selector-preview">{{ query }}</span>
			</k-button>
		</footer>
	</nav>
</template>

<script>
export const props = {
	props: {
		add: {
			default: true,
			type: Boolean
		},
		icon: {
			type: String
		},
		options: {
			default() {
				return [];
			},
			type: Array
		},
		placeholder: {
			default() {
				return this.$t("search");
			}
		},
		search: [Object, Boolean]
	}
};

export default {
	mixins: [props],
	emits: ["create", "escape", "pick", "select"],
	data() {
		return {
			filtered: this.options,
			query: null,
			selected: -1
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
		empty() {
			return this.$t("search.results.none");
		},
		inputPlaceholder() {
			if (this.options.length === 0) {
				return "Create a new option";
			}

			return this.placeholder;
		},
		/**
		 * Regular expression for current search term
		 * @returns {RegExp}
		 */
		regex() {
			return new RegExp(`(${RegExp.escape(this.query ?? "")})`, "ig");
		},
		showCreateButton() {
			if (this.add === false) {
				return false;
			}

			// don't show the button if the query is empty
			if ((this.query ?? "").trim().length === 0) {
				return false;
			}

			const matches = this.filtered.filter((result) => {
				return result.text === this.query || result.value === this.query;
			});

			return matches.length === 0;
		}
	},
	methods: {
		create(value) {
			value = value.trim();

			if (value.length === 0) {
				return;
			}

			this.$emit("create", value);
		},
		down() {
			this.pick(this.selected + 1);
		},
		escape() {
			this.reset();
			this.focus();
			this.$emit("escape");
		},
		filter(query) {
			this.query = query;

			this.selected = -1;
			this.filtered = this.$helper.array.search(this.options, this.query, {
				field: "text"
			});

			// highlight queries in the text
			this.filtered = this.filtered.map((result) => {
				result.highlighted = this.toHighlightedString(result.text);
				return result;
			});

			// select the create button if there are no results
			if (this.showCreateButton === true && this.filtered.length === 0) {
				this.selected = this.filtered.length;
			} else if (this.filtered.length) {
				this.selected = 0;
			}
		},
		focus() {
			this.$refs.input?.focus();
		},
		pick(index) {
			const max = this.showCreateButton
				? this.filtered.length
				: this.filtered.length - 1;
			const min = -1;

			if (index > max || index < min) {
				return false;
			}

			this.selected = index;
			this.$emit("pick", index);
			this.focus();

			// scroll the results list to the selected button
			this.$nextTick(() => {
				this.$refs.results?.querySelector("[aria-current]")?.scrollIntoView({
					block: "nearest"
				});
			});
		},
		reset() {
			this.filter("");
		},
		select(index) {
			this.pick(index);

			const value = this.filtered[this.selected];

			if (value) {
				this.$emit("select", value);
			} else if (this.showCreateButton) {
				this.create(this.query);
			} else {
				return;
			}

			// reset the search query
			this.query = null;
		},
		tab(event) {
			event.preventDefault();

			if (event.shiftKey) {
				this.up();
			} else {
				this.down();
			}
		},
		toHighlightedString(string) {
			// make sure that no HTML exists before in the string
			// to avoid XSS when displaying via `v-html`
			string = this.$helper.string.stripHTML(string);
			return string.replace(this.regex, "<b>$1</b>");
		},
		up() {
			this.pick(this.selected - 1);
		}
	}
};
</script>

<style>
:root {
	--selector-color-highlight: var(--color-yellow-500);
}

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
.k-selector-button b {
	color: var(--selector-color-highlight);
	font-weight: var(--font-normal);
}
.k-selector-preview {
	color: var(--selector-color-highlight);
	display: inline-flex;
}
</style>
