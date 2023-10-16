<template>
	<nav
		class="k-selector"
		role="search"
		:data-has-current="filtered?.includes(selected)"
	>
		<header class="k-selector-header">
			<div v-if="showSearch" class="k-selector-search">
				<input
					ref="input"
					:value="query"
					:placeholder="inputPlaceholder + ' …'"
					class="k-selector-input"
					type="search"
					@click="pick(-1)"
					@input="query = $event.target.value"
					@keydown.down.prevent="down"
					@keydown.escape.prevent="escape()"
					@keydown.enter.prevent="select(selected)"
					@keydown.tab="tab"
					@keydown.up.prevent="up"
				/>
			</div>
		</header>

		<div v-if="filtered.length || showEmpty" class="k-selector-body">
			<k-navigate
				v-if="filtered.length"
				ref="results"
				axis="y"
				class="k-selector-results"
			>
				<k-button
					v-for="(option, key) in filtered"
					:key="key"
					:current="selected === key"
					:disabled="option.disabled"
					:icon="option.icon ?? icon"
					class="k-selector-button"
					@click="select(key)"
					@focus.native="pick(key)"
				>
					<!-- eslint-disable-next-line vue/no-v-html -->
					<span v-html="highlight(option.text)" />
				</k-button>
			</k-navigate>

			<p v-else-if="showEmpty" class="k-selector-empty">{{ empty }}</p>
		</div>

		<footer v-if="showCreateButton" class="k-selector-footer">
			<k-button
				:current="selected === filtered.length"
				icon="add"
				class="k-selector-button k-selector-add-button"
				@focus.native="select(filtered.length)"
			>
				<strong>{{ value ? $t("replace.with") : $t("create") }}:</strong>
				<span class="k-selector-preview">{{ query }}</span>
			</k-button>
		</footer>
	</nav>
</template>

<script>
export const props = {
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

/**
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["create", "escape", "pick", "select"],
	data() {
		return {
			query: this.value ?? "",
			selected: -1
		};
	},
	computed: {
		empty() {
			return this.$t("options.none");
		},
		filtered() {
			// show all results if the query is too short or empty
			if (this.hasQuery === false) {
				return this.options;
			}

			return this.$helper.array.search(this.options, this.query, {
				field: "text"
			});
		},
		hasQuery() {
			// min length for the search to kick in
			const min = this.search.min ?? 0;

			return this.query.length >= min;
		},
		inputPlaceholder() {
			return this.accept === "options" ? this.$t("filter") : this.$t("enter");
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

			const matches = this.options.filter(
				(result) => result.text === this.query || result.value === this.query
			);

			return matches.length === 0;
		},
		showEmpty() {
			return (
				this.accept === "options" &&
				this.filtered.legnth === 0 &&
				this.options.length
			);
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
		query() {
			// reset the focus on the input
			this.selected = -1;

			// show all results if the query is too short or empty
			if (this.hasQuery === false) {
				return;
			}

			// select the create button if there are no results
			if (this.showCreateButton === true && this.filtered.length === 0) {
				this.selected = this.filtered.length;
			} else if (this.filtered.length) {
				this.selected = 0;
			}
		},
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
		focus() {
			this.$refs.input?.focus();
		},
		async pick(index) {
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

			await this.$nextTick();

			// scroll the results list to the selected button
			this.$refs.results?.$el.querySelector("[aria-current]")?.scrollIntoView({
				block: "nearest"
			});
		},
		reset() {
			this.query = "";
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
	--selector-color-highlight: var(--color-yellow-500);
}

.k-selector {
	--button-align: start;
	--button-height: var(--height-sm);
	--button-rounded: var(--rounded-sm);
	--button-width: 100%;
}

.k-selector-input {
	height: var(--button-height);
	padding: 0 var(--button-padding);
	border-radius: var(--button-rounded);
}
.k-selector-input::placeholder {
	color: var(--color-text-dimmed);
}
/** TODO: .k-selector:has([aria-current]) .k-selector-input:focus  */
.k-selector[data-has-current="true"] .k-selector-input:focus {
	outline: 0;
}

.k-selector-empty {
	height: var(--button-height);
	display: flex;
	align-items: center;
	padding-inline: var(--button-padding);
	color: var(--color-text-dimmed);
}
.k-selector-button[aria-current] {
	outline: var(--outline);
}
.k-selector-button b {
	color: var(--selector-color-highlight);
	font-weight: var(--font-normal);
}

.k-selector-add-button {
	--button-height: auto;
	padding-block: var(--button-padding);
}
.k-selector-add-button .k-button-icon {
	align-self: start;
}
.k-selector-add-button .k-button-text strong {
	display: block;
	font-weight: var(--font-semi);
	margin-bottom: 0.25rem;
}

.k-selector-preview {
	color: var(--selector-color-highlight);
}
</style>
