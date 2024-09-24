<template>
	<div :data-can-add="canAdd" class="k-tags-input">
		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(value)"
		>
			<k-tags
				ref="tags"
				v-bind="$props"
				:removable="true"
				@edit="edit"
				@input="$emit('input', $event)"
				@click.native.stop="$refs.toggle?.$el?.click()"
			>
				<k-button
					v-if="!max || value.length < max"
					:id="id"
					ref="toggle"
					:autofocus="autofocus"
					:disabled="disabled"
					class="k-tags-input-toggle k-tags-navigatable input-focus"
					size="xs"
					icon="add"
					@click="$refs.create.open()"
					@keydown.native.delete="$refs.tags.focus('prev')"
					@keydown.native="toggle"
				/>
			</k-tags>
		</k-input-validator>

		<k-picklist-dropdown
			ref="replace"
			v-bind="picklist"
			:multiple="false"
			:options="replacableOptions"
			:value="editing?.tag.value ?? ''"
			@create="replace"
			@input="replace"
		/>

		<k-picklist-dropdown
			ref="create"
			v-bind="picklist"
			:options="creatableOptions"
			:value="value"
			@create="create"
			@input="pick"
		/>
	</div>
</template>

<script>
import Multiselect, { props as MultiselectProps } from "./MultiselectInput.vue";

export const props = {
	mixins: [MultiselectProps],
	props: {
		/**
		 * Whether to accept only options or also custom tags
		 * @values "all", "options"
		 */
		accept: {
			type: String,
			default: "all"
		},
		/**
		 * Separator which will be used when pasting
		 * a list of tags to split them into individual tags
		 */
		separator: {
			type: String,
			default: ","
		}
	}
};

export default {
	extends: Multiselect,
	mixins: [props],
	data() {
		return {
			editing: null
		};
	},
	computed: {
		canAdd() {
			return !this.max || this.value.length < this.max;
		},
		creatableOptions() {
			// tags should be unique, so when creating,
			// only show options that are not already selected
			return this.options.filter(
				(option) => this.value.includes(option.value) === false
			);
		},
		picklist() {
			return {
				disabled: this.disabled,
				create: this.showCreate,
				ignore: this.ignore,
				min: this.min,
				max: this.max,
				search: this.showSearch
			};
		},
		replacableOptions() {
			// when replacing, we want to hide all options
			// that are already selected (as in `creatableOptions`),
			// but the one we are replacing should be visible for context
			return this.options.filter(
				(option) =>
					this.value.includes(option.value) === false ||
					option.value === this.editing?.tag.value
			);
		},
		showCreate() {
			// never show create when only accepting options
			if (this.accept === "options") {
				return false;
			}

			// when replacing, show custom submit text
			if (this.editing) {
				return { submit: this.$t("replace.with") };
			}

			return true;
		},
		showSearch() {
			if (this.search === false) {
				return false;
			}

			if (this.editing) {
				return { placeholder: this.$t("replace.with"), ...this.search };
			}

			if (this.accept === "options") {
				return { placeholder: this.$t("filter"), ...this.search };
			}

			return this.search;
		}
	},
	methods: {
		create(input) {
			const inputs = input.split(this.separator).map((tag) => tag.trim());
			const tags = this.$helper.object.clone(this.value);

			for (let tag of inputs) {
				// convert input to tag object
				tag = this.$refs.tags.tag(tag, this.separator);

				// no new tags if this is full,
				// check if the tag is accepted
				if (this.isAllowed(tag) === true) {
					tags.push(tag.value);
				}
			}

			this.$emit("input", tags);
			this.$refs.create.close();
		},
		async edit(index, tag) {
			this.editing = { index, tag };
			this.$refs.replace.open();
		},
		focus() {
			if (this.canAdd) {
				this.$refs.create.open();
			}
		},
		isAllowed(tag) {
			if (typeof tag !== "object" || tag.value.trim().length === 0) {
				return false;
			}

			// if only options are allowed as value
			if (this.accept === "options" && !this.$refs.tags.option(tag)) {
				return false;
			}

			// don't allow duplicates
			if (this.value.includes(tag.value) === true) {
				return false;
			}

			return true;
		},
		pick(tags) {
			this.$emit("input", tags);
			this.$refs.create.close();
		},
		replace(value) {
			// get index of tag that is being replaced
			// and tag object of the new value
			const { index } = this.editing;
			const updated = this.$refs.tags.tag(value);

			// close the replace dropdown and reset editing
			this.$refs.replace.close();
			this.editing = null;

			// don't replace if the new value is not allowed
			if (this.isAllowed(updated) === false) {
				return false;
			}

			// replace the tag at the given index
			const tags = this.$helper.object.clone(this.value);
			tags.splice(index, 1, updated.value);
			this.$emit("input", tags);

			// focus the tag that was replaced
			this.$refs.tags.navigate(index);
		},
		toggle(event) {
			if (event.metaKey || event.altKey || event.ctrlKey) {
				return false;
			}

			if (event.key === "ArrowDown") {
				this.$refs.create.open();
				event.preventDefault();
				return;
			}

			if (String.fromCharCode(event.keyCode).match(/(\w)/g)) {
				this.$refs.create.open();
			}
		}
	}
};
</script>

<style>
.k-tags-input {
	padding: var(--tags-gap);
}
.k-tags-input[data-can-add="true"] {
	cursor: pointer;
}

.k-tags-input-toggle.k-button {
	--button-color-text: var(--input-color-placeholder);
	opacity: 0;
}
.k-tags-input-toggle.k-button:focus {
	--button-color-text: var(--input-color-text);
}
.k-tags-input:focus-within .k-tags-input-toggle {
	opacity: 1;
}

.k-tags-input .k-picklist-dropdown {
	margin-top: var(--spacing-1);
}
.k-tags-input .k-picklist-dropdown .k-choice-input:focus-within {
	outline: var(--outline);
}
</style>
