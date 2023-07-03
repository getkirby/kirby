<template>
	<k-navigate ref="navigation" axis="x">
		<k-draggable
			v-direction
			:list="tags"
			:options="dragOptions"
			:data-layout="layout"
			class="k-tags"
			@end="save"
		>
			<k-tag
				v-for="(tag, index) in tags"
				:key="index"
				:removable="!disabled"
				name="tag"
				@click.native.stop
				@keypress.native.enter="edit(index, tag)"
				@dblclick.native="edit(index, tag)"
				@remove="remove(index, tag)"
			>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-html="tag.text" />
			</k-tag>
			<template #footer>
				<k-select-dropdown
					v-if="showSelector"
					ref="selector"
					:add="accept === 'all'"
					:options="selectable"
					@create="add($event)"
					@select="add($event)"
				>
					<k-button
						ref="toggle"
						:autofocus="autofocus"
						:id="id"
						icon="add"
						class="k-tags-toggle"
						size="xs"
						@click.native="$refs.selector.open()"
						@keydown.native="toggle"
						@keydown.native.delete="navigate(tags.length - 1)"
					/>
				</k-select-dropdown>
			</template>
		</k-draggable>
	</k-navigate>
</template>

<script>
import { autofocus, disabled, id } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id],
	props: {
		accept: {
			type: String,
			default: "all"
		},
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
		/**
		 * Options will be shown in the autocomplete dropdown
		 * as soon as you start typing.
		 */
		options: {
			type: Array,
			default: () => []
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
			tags: []
		};
	},
	watch: {
		value: {
			handler() {
				this.tags = this.value.map(this.tag).filter((tag) => tag);
			},
			immediate: true
		}
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
		selectable() {
			return this.options.filter((option) => {
				return this.value.includes(option.value) === false;
			});
		},
		showSelector() {
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
		edit(index, tag) {
			this.$panel.dialog.open({
				component: "k-form-dialog",
				props: {
					fields: {
						value: {
							autofocus: true,
							icon: "tag",
							label: "Tag",
							required: true,
							type: "text"
						}
					},
					submitButton: this.$t("change"),
					value: {
						value: tag.value
					}
				},
				on: {
					submit: (tag) => {
						const updated = this.tag(tag);

						if (this.isAllowed(updated) === false) {
							this.$panel.notification.error("The tag is not allowed");
							return;
						}

						this.$set(this.tags, index, updated);
						this.save();
						this.$panel.dialog.close();
						this.navigate(index);
					}
				}
			});
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

			// avoid duplicates
			if (this.value.includes(tag.value)) {
				return false;
			}

			// if only options are allwed as value
			if (this.accept === "options" && !this.option(tag)) {
				return false;
			}

			return true;
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
.k-tags {
	display: flex;
	gap: 0.25rem;
	align-items: center;
	flex-wrap: wrap;
	flex-grow: 1;
	min-height: var(--tag-height);
}
.k-tags .k-sortable-ghost {
	outline: var(--outline);
}
.k-tags[data-layout="list"] .k-tag {
	width: 100%;
}
.k-tags .k-select-dropdown {
	align-self: start;
	flex-shrink: 0;
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
