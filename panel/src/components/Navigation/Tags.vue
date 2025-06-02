<template>
	<k-navigate
		ref="navigate"
		:axis="layout === 'list' ? 'y' : 'x'"
		select=":where(.k-tag, .k-tags-navigatable):not(:disabled)"
	>
		<k-draggable
			:list="tags"
			:options="dragOptions"
			:data-layout="layout"
			class="k-tags"
			@end="input"
		>
			<k-tag
				v-for="(item, itemIndex) in tags"
				:key="itemIndex"
				:disabled="disabled"
				:image="item.image"
				:removable="!disabled"
				name="tag"
				@click.native.stop
				@keypress.native.enter="edit(itemIndex, item, $event)"
				@dblclick.native="edit(itemIndex, item, $event)"
				@remove="remove(itemIndex, item)"
			>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-html="item.text" />
			</k-tag>
			<template #footer>
				<!-- @slot Place stuff here in the non-draggable footer -->
				<slot />
			</template>
		</k-draggable>
	</k-navigate>
</template>

<script>
import { disabled, id, options } from "@/mixins/props.js";

export const props = {
	mixins: [disabled, id, options],
	inheritAttrs: false,
	props: {
		/**
		 * You can set the layout to `"list"` to extend the width of each tag
		 * to 100% and show them in a list. This is handy in narrow columns
		 * or when a list is a more appropriate design choice for the input
		 * in general.
		 *
		 * @values "list"
		 */
		layout: String,
		/**
		 * Whether to sort the tags by the available options
		 */
		sort: {
			default: false,
			type: Boolean
		},
		value: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props],
	props: {
		/**
		 * Whether the tags can by sorted manually by dragging
		 * (not available when `sort` is `true`)
		 */
		draggable: {
			default: true,
			type: Boolean
		}
	},
	emits: ["edit", "input"],
	data() {
		return {
			tags: []
		};
	},
	computed: {
		dragOptions() {
			return {
				delay: 1,
				disabled: !this.isDraggable,
				draggable: ".k-tag",
				handle: ".k-tag-text"
			};
		},
		isDraggable() {
			if (
				this.sort === true ||
				this.draggable === false ||
				this.tags.length === 0 ||
				this.disabled === true
			) {
				return false;
			}

			return true;
		}
	},
	watch: {
		value: {
			handler() {
				// make sure values are not reactive
				// otherwise this could have nasty side-effects
				let tags = structuredClone(this.value);

				// sort all tags by the available options
				if (this.sort === true) {
					// temp container for sorted tags
					const sorted = [];

					// add all sorted options first
					for (const option of this.options) {
						const index = tags.indexOf(option.value);

						// if the option exists in the value array …
						if (index !== -1) {
							sorted.push(option);

							// remove the sorted option from the tags array,
							// so it won't be added twice
							tags.splice(index, 1);
						}
					}

					// add all remaining custom options
					sorted.push(...tags);

					tags = sorted;
				}

				// convert all values to tag objects and filter invalid tags
				this.tags = tags.map(this.tag).filter((tag) => tag);
			},
			immediate: true
		}
	},
	methods: {
		edit(index, tag, event) {
			if (this.disabled === false) {
				/**
				 * Tag was double-clicked
				 * @property {Number} index
				 * @property {Object} tag
				 * @property {Event} event
				 */
				this.$emit("edit", index, tag, event);
			}
		},
		focus(index = "last") {
			this.$refs.navigate.move(index);
		},
		index(tag) {
			return this.tags.findIndex((item) => item.value === tag.value);
		},
		input() {
			/**
			 * Tags list was updated
			 * @property {Array} tags
			 */
			this.$emit(
				"input",
				this.tags.map((tag) => tag.value)
			);
		},
		navigate(position) {
			this.focus(position);
		},
		remove(index) {
			if (this.tags.length <= 1) {
				this.navigate("last");
			} else {
				this.navigate("prev");
			}

			this.tags.splice(index, 1);
			this.input();
		},
		/**
		 * Get corresponding option for a tag value
		 * @param {String} tag
		 * @returns {text: String, value: String}
		 * @public
		 */
		option(tag) {
			return this.options.find((option) => option.value === tag.value);
		},
		select() {
			this.focus();
		},
		/**
		 * Create a tag object from a string or object
		 * @param {String, Object} tag
		 * @returns {text: String, value: String}
		 * @public
		 */
		tag(tag) {
			if (typeof tag !== "object") {
				tag = { value: tag };
			}

			// try to find a matching option
			const option = this.option(tag);

			// always prefer options as source
			// as they can be trusted without escaping
			if (option) {
				return option;
			}

			return {
				// always escape HTML in text for tags that
				// can't be matched with any defined option
				// to avoid XSS when displaying via `v-html`
				text: this.$helper.string.escapeHTML(tag.text ?? tag.value),
				value: tag.value
			};
		}
	}
};
</script>

<style>
:root {
	--tags-gap: 0.375rem;
}

.k-tags {
	display: inline-flex;
	max-width: 100%;
	gap: var(--tags-gap);
	align-items: center;
	flex-wrap: wrap;
}
.k-tags .k-sortable-ghost {
	outline: var(--outline);
}
.k-tags[data-layout="list"],
.k-tags[data-layout="list"] .k-tag {
	width: 100%;
}

.k-tags.k-draggable .k-tag-text {
	cursor: grab;
}
.k-tags.k-draggable .k-tag-text:active {
	cursor: grabbing;
}
</style>
