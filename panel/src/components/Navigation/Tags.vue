<template>
	<k-navigate ref="navigation" :axis="layout === 'list' ? 'y' : 'x'">
		<k-draggable
			:list="tags"
			:options="dragOptions"
			:data-layout="layout"
			class="k-tags"
			@end="save"
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
				<!-- add selector -->
				<k-selector-dropdown
					v-if="showAddSelector"
					ref="selector"
					v-bind="selectorOptions"
					:options="selectable"
					@create="add($event)"
					@select="add($event)"
				>
					<k-button
						:id="id"
						ref="toggle"
						:autofocus="autofocus"
						icon="add"
						class="k-tags-toggle"
						size="xs"
						@click.native="$refs.selector.open()"
						@keydown.native="toggle"
						@keydown.native.delete="navigate(tags.length - 1)"
					/>
				</k-selector-dropdown>

				<!-- replace selector -->
				<k-selector-dropdown
					ref="editor"
					v-bind="selectorOptions"
					:options="replacable"
					:value="editing?.tag.text"
					@create="replace($event)"
					@select="replace($event)"
				/>
			</template>
		</k-draggable>
	</k-navigate>
</template>

<script>
import { autofocus, disabled, id } from "@/mixins/props.js";
import { props as SelectorProps } from "@/components/Forms/Selector.vue";

export const props = {
	mixins: [autofocus, disabled, id, SelectorProps],
	inheritAttrs: false,
	props: {
		draggable: {
			default: true,
			type: Boolean
		},
		/**
		 * You can set the layout to `list` to extend the width of each tag
		 * to 100% and show them in a list. This is handy in narrow columns
		 * or when a list is a more appropriate design choice for the input
		 * in general.
		 *
		 * @values "list"
		 */
		layout: String,
		/**
		 * The maximum number of accepted tags
		 */
		max: Number,
		/**
		 * The minimum number of required tags
		 */
		min: Number,
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
	data() {
		return {
			editing: null,
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
		},
		isFull() {
			if (!this.max) {
				return false;
			}

			return this.tags.length >= this.max;
		},
		replacable() {
			return this.options.filter((option) => {
				return (
					this.value.includes(option.value) === false ||
					option.value === this.editing?.tag.value
				);
			});
		},
		selectable() {
			return this.options.filter(
				(option) => this.value.includes(option.value) === false
			);
		},
		selectorOptions() {
			return {
				accept: this.accept,
				disabled: this.disabled,
				ignore: this.value,
				search: this.search
			};
		},
		showAddSelector() {
			if (this.disabled === true) {
				return false;
			}

			if (this.isFull === true) {
				return false;
			}

			if (this.accept !== "all" && this.selectable.length === 0) {
				return false;
			}

			return true;
		}
	},
	watch: {
		value: {
			handler() {
				if (this.sort === true) {
					// sort all tags by the available options
					this.tags = this.sortByOptions(this.value);
				} else {
					// convert all values to tag objects and filter invalid tags
					this.tags = this.value.map(this.tag).filter((tag) => tag);
				}
			},
			immediate: true
		}
	},
	methods: {
		add(tag) {
			// clean up the input
			tag = this.tag(tag);

			// no new tags if this is full
			if (this.isFull === true) {
				return false;
			}

			// check if the tag is accepted
			if (this.isAllowed(tag) === false) {
				return false;
			}

			this.tags.push(tag);
			this.save();
		},
		edit(index, tag, event) {
			this.editing = {
				index,
				tag
			};

			return this.$refs.editor.open(event.target.closest(".k-tag"));
		},
		focus(index = "last") {
			this.$refs.navigation.move(index);
		},
		index(tag) {
			return this.tags.findIndex((item) => item.value === tag.value);
		},
		isAllowed(tag) {
			if (typeof tag !== "object" || tag.value.length === 0) {
				return false;
			}

			// if only options are allowed as value
			if (this.accept === "options" && !this.option(tag)) {
				return false;
			}

			if (this.isDuplicate(tag) === true) {
				return false;
			}

			return true;
		},
		isDuplicate(tag) {
			return this.value.includes(tag.value) === true;
		},
		navigate(position) {
			this.focus(position);
		},
		remove(index) {
			this.tags.splice(index, 1);

			if (this.tags.length === 0) {
				this.navigate("last");
			} else {
				this.navigate("prev");
			}

			this.save();
		},
		replace(value) {
			const { index } = this.editing;
			const updated = this.tag(value);

			if (this.isAllowed(updated) === false) {
				return false;
			}

			this.$set(this.tags, index, updated);
			this.save();
			this.navigate(index);
			this.editing = null;
		},
		open() {
			if (this.$refs.selector) {
				this.$refs.toggle.focus();
				this.$refs.selector.open(this.$refs.toggle);
			} else {
				this.focus();
			}
		},
		option(tag) {
			return this.options.find((option) => option.value === tag.value);
		},
		select() {
			this.focus();
		},
		save() {
			this.$emit(
				"input",
				this.tags.map((tag) => tag.value)
			);
		},
		sortByOptions(values) {
			// make sure values are not reactive
			// otherwise this could have nasty side-effects
			values = this.$helper.object.clone(values);

			// container for sorted tags
			const tags = [];

			// add all sorted options first
			for (const option of this.options) {
				const index = values.indexOf(option.value);

				// if the option exists in the value array …
				if (index !== -1) {
					tags.push(option);

					// remove the sorted option from the temporary values array
					values.splice(index, 1);
				}
			}

			// add all remaining custom options
			for (const option of values) {
				tags.push(this.tag(option));
			}

			return tags;
		},
		/**
		 * @param {String,Object} tag
		 * @returns {text: String, value: String}
		 */
		tag(tag) {
			if (typeof tag !== "object") {
				tag = { value: tag };
			}

			// try to find a matching option
			const option = this.option(tag);

			// if only options are allwed as value
			if (this.accept === "options") {
				return option;
			}

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
		},

		toggle(event) {
			if (event.metaKey || event.altKey || event.ctrlKey) {
				return false;
			}

			const char = String.fromCharCode(event.keyCode);

			if (char.match(/(\w)/g)) {
				this.$refs.selector.open();
			}
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
.k-tags-toggle.k-button {
	--button-rounded: var(--rounded-sm);
	--button-color-icon: var(--color-gray-600);
	opacity: 0;
	transition: opacity 0.3s;
}
.k-tags:is(:hover, :focus-within) .k-tags-toggle {
	opacity: 1;
}
.k-tags .k-tags-toggle:is(:focus, :hover) {
	--button-color-icon: var(--color-text);
}
</style>
