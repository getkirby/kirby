<template>
	<div class="k-autocomplete">
		<!-- @slot Use to insert your input -->
		<slot />
		<k-dropdown-content
			ref="dropdown"
			:autofocus="true"
			@leave="$emit('leave')"
		>
			<k-dropdown-item
				v-for="(item, index) in matches"
				:key="index"
				v-bind="item"
				@mousedown.native="onSelect(item)"
				@keydown.native.tab.prevent="onSelect(item)"
				@keydown.native.enter.prevent="onSelect(item)"
				@keydown.native.left.prevent="close"
				@keydown.native.backspace.prevent="close"
				@keydown.native.delete.prevent="close"
			>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-html="html ? item.text : $esc(item.text)" />
			</k-dropdown-item>
		</k-dropdown-content>
		{{ query }}
	</div>
</template>

<script>
/**
 * The Autocomplete component can be wrapped around any form of input to get an flexible starting point to provide an real-time autocomplete dropdown. We use it for our `TagsInput` component.
 *
 * @deprecated 4.0.0
 */
export default {
	props: {
		/**
		 * If set to `true`, the text of the options is rendered as HTML
		 */
		html: {
			type: Boolean,
			default: false
		},
		/**
		 * Maximum number of displayed results
		 */
		limit: {
			type: Number,
			default: 10
		},
		/**
		 * You can pass an array of strings, which should be ignored in the search.
		 */
		skip: {
			type: Array,
			default: () => []
		},
		/**
		 * Options for the autocomplete dropdown must be passed as an array of
		 * objects. Each object can have as many items as you like, but a text
		 * item is required to match agains the query
		 *
		 * @example [ { text: "this will be searched", id: "anything else is optional" }, ];
		 */
		options: Array,
		/**
		 * Term to filter options
		 */
		query: String
	},
	emits: ["leave", "search", "select"],
	data() {
		return {
			matches: [],
			selected: { text: null }
		};
	},
	mounted() {
		window.panel.deprecated(
			"<k-autocomplete> will be removed in a future version."
		);
	},
	methods: {
		close() {
			this.$refs.dropdown.close();
		},
		onSelect(item) {
			/**
			 * New value has been selected
			 * @event select
			 * @property {object} item - option item
			 */
			this.$emit("select", item);
			this.$refs.dropdown.close();
		},
		/**
		 * Opens the dropdown and filters the options
		 * @public
		 * @param {string} query search term
		 */
		search(query) {
			// skip all options in the skip array
			const options = this.options.filter((option) => {
				return this.skip.indexOf(option.value) !== -1;
			});

			this.matches = this.$helper.array.search(options, query, {
				field: "text",
				limit: this.limit
			});

			/**
			 * Search has been performed
			 * @event search
			 * @property {string} query
			 * @property {array} matches - all options that match the search query
			 */
			this.$emit("search", query, this.matches);
			this.$refs.dropdown.open();
		}
	}
};
</script>
