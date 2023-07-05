<template>
	<nav class="k-selector" role="search">
		<header class="k-selector-header">
			<label v-if="label" :for="_uid" class="k-selector-label">
				{{ label }}
			</label>
			<div class="k-selector-input">
				<input
					ref="input"
					:id="_uid"
					:value="query"
					:placeholder="inputPlaceholder + ' …'"
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

		<div v-if="options.length || query.length" class="k-selector-body">
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
						<!-- eslint-disable-next-line vue/no-v-html -->
						<span v-html="highlight(option.text)" />
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
				class="k-selector-button k-selector-add-button"
				@focus.native="select(filtered.length)"
			>
				<strong>Create new option:</strong>
				<span class="k-selector-preview">{{ query }}</span>
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
		placeholder: {
			default() {
				return "Filter options";
			}
		},
		search: [Object, Boolean],
		value: {
			type: String
		}
	}
};

export default {
	mixins: [props],
	emits: ["create", "escape", "pick", "select"],
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
		inputPlaceholder() {
			return this.options.length === 0 ? "Enter" : "Filter";
		},
		/**
		 * Regular expression for current search term
		 * @returns {RegExp}
		 */
		regex() {
			return new RegExp(`(${RegExp.escape(this.query)})`, "ig");
		},
		showCreateButton() {
			if (this.add === false) {
				return false;
			}

			const query = this.query.trim();

			// don't show the button if the query is empty
			if (query.length === 0) {
				return false;
			}

			// don't show the button if the query is in the ignore list
			if (this.ignore.includes(query) === true) {
				return false;
			}

			const matches = this.filtered.filter((result) => {
				return result.text === this.query || result.value === this.query;
			});

			return matches.length === 0;
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
		this.$refs.input.select();
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
			this.query = query ?? "";

			this.selected = -1;
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

.k-selector-input input {
	height: var(--button-height);
	padding: 0 var(--button-padding);
	border-radius: var(--button-rounded);
}
.k-selector:has([aria-current]) .k-selector-input input:focus {
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
