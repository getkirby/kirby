<template>
	<nav
		class="k-filter-input"
		role="search"
		:aria-disabled="disabled"
		:data-has-current="filtered?.includes(selected)"
	>
		<header class="k-filter-input-header">
			<h2 v-if="label" class="k-filter-input-label">
				{{ label }}
			</h2>
			<div v-if="showSearch" class="k-filter-input-search">
				<input
					ref="input"
					:disabled="disabled"
					:placeholder="inputPlaceholder + ' …'"
					:value="query"
					type="search"
					@click="pick(-1)"
					@input="filter($event.target.value)"
					@keydown.down.prevent="down"
					@keydown.escape.prevent="escape()"
					@keydown.enter.prevent="select(selected)"
					@keydown.tab="tab"
					@keydown.up.prevent="up"
				/>
			</div>
		</header>

		<div v-if="filtered.length || options.length" class="k-filter-input-body">
			<template v-if="filtered.length">
				<k-navigate ref="results" axis="y" class="k-filter-input-results">
					<k-button
						v-for="(option, key) in filtered"
						:key="key"
						:current="selected === key"
						:disabled="disabled ?? option.disabled"
						:icon="option.icon ?? icon"
						class="k-filter-input-button"
						@click="select(key)"
						@focus.native="pick(key)"
					>
						<!-- eslint-disable-next-line vue/no-v-html -->
						<span v-html="highlight(option.text)" />
					</k-button>
				</k-navigate>
			</template>
			<template v-else-if="options.length">
				<p class="k-filter-input-empty">{{ empty }}</p>
			</template>
		</div>

		<footer v-if="showCreateButton" class="k-filter-input-footer">
			<k-button
				:current="selected === filtered.length"
				icon="add"
				class="k-filter-input-button k-filter-input-add-button"
				@focus.native="select(filtered.length)"
			>
				<strong>{{ value ? $t("replace.with") : $t("create") }}:</strong>
				<span class="k-filter-input-preview">{{ query }}</span>
			</k-button>
		</footer>
	</nav>
</template>

<script>
import { disabled } from "@/mixins/props.js";

export const props = {
	mixins: [disabled],
	props: {
		accept: {
			type: String,
			default: "all"
		},
		icon: {
			type: String
		},
		ignore: {
			default: () => [],
			type: Array
		},
		label: {
			type: String
		},
		options: {
			default: () => [],
			type: Array
		},
		search: {
			default: true,
			type: [Object, Boolean]
		},
		value: {
			type: String
		}
	}
};

export default {
	mixins: [props],
	emits: ["escape", "pick", "input"],
	data() {
		return {
			filtered: this.options,
			query: this.value ?? "",
			selected: -1
		};
	},
	computed: {
		empty() {
			return this.$t("options.none");
		},
		hasQuery() {
			// min length for the search to kick in
			const min = this.search.min ?? 0;

			return this.query.length >= min;
		},
		inputPlaceholder() {
			return this.options.length === 0 ? this.$t("enter") : this.$t("filter");
		},
		/**
		 * Regular expression for current search term
		 * @returns {RegExp}
		 */
		regex() {
			return new RegExp(`(${RegExp.escape(this.query)})`, "ig");
		},
		showCreateButton() {
			if (this.accept !== "all") {
				return false;
			}

			// don't show the button if the query is empty
			if (this.query.length === 0) {
				return false;
			}

			// don't show the button if the query is in the ignore list
			if (this.ignore.includes(this.query) === true) {
				return false;
			}

			const matches = this.filtered.filter((result) => {
				return result.text === this.query || result.value === this.query;
			});

			return matches.length === 0;
		},
		showSearch() {
			// if new options can be added,
			// the search input is always needed
			if (this.accept === "all") {
				return true;
			}

			return this.search !== false;
		}
	},
	watch: {
		selected() {
			if (this.selected === -1) {
				this.focus();
			}
		}
	},
	mounted() {
		this.$refs.input?.select();
	},
	methods: {
		create(value) {
			value = value.trim();

			if (value.length === 0) {
				return;
			}

			this.$emit("input", value);
		},
		down() {
			this.pick(this.selected + 1);
		},
		escape() {
			this.reset();
			this.focus();
			this.$emit("escape");
		},
		filter(query = "") {
			this.query = query;

			// reset the focus on the input
			this.selected = -1;

			// show all results if the query is too short or empty
			if (this.hasQuery === false) {
				this.filtered = this.options;
				return;
			}

			this.filtered = this.$helper.array.search(this.options, this.query, {
				field: "text"
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

			const option = this.filtered[this.selected];

			if (option) {
				this.$emit("input", option.value);
			} else if (this.showCreateButton) {
				this.create(this.query);
			} else {
				return;
			}

			// reset the search query
			this.query = "";
		},
		tab(event) {
			event.preventDefault();

			if (event.shiftKey) {
				this.up();
			} else {
				this.down();
			}
		},
		highlight(string) {
			// make sure that no HTML exists before in the string
			// to avoid XSS when displaying via `v-html`
			string = this.$helper.string.stripHTML(string);

			if (this.query.length === 0) {
				return string;
			}

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
	--filter-input-color-highlight: var(--color-focus);
}

.k-filter-input {
	--button-align: start;
	--button-height: var(--height-sm);
	--button-rounded: var(--rounded-sm);
	--button-width: 100%;
}

.k-filter-input-search input {
	height: var(--button-height);
	padding: 0 var(--button-padding);
	border-radius: var(--button-rounded);
}
.k-filter-input-search input::placeholder {
	color: var(--color-text-dimmed);
}
/** TODO: .k-filter-input:has([aria-current]) .k-filter-input-input:focus  */
.k-filter-input[data-has-current="true"] .k-filter-input-search input:focus {
	outline: 0;
}

.k-filter-input-empty {
	height: var(--button-height);
	display: flex;
	align-items: center;
	padding-inline: var(--button-padding);
	color: var(--color-text-dimmed);
}
.k-filter-input-button[aria-current] {
	outline: var(--outline);
}
.k-filter-input-button b {
	color: var(--filter-input-color-highlight);
	font-weight: var(--font-normal);
}

.k-filter-input-add-button {
	--button-height: auto;
	padding-block: var(--button-padding);
}
.k-filter-input-add-button .k-button-icon {
	align-self: start;
}
.k-filter-input-add-button .k-button-text strong {
	display: block;
	font-weight: var(--font-semi);
	margin-bottom: 0.25rem;
}

.k-filter-input-preview {
	color: var(--filter-input-color-highlight);
}
</style>
