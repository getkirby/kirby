<template>
	<k-block-title
		:class="$attrs.class"
		:content="content"
		:fieldset="fieldset"
		:style="$attrs.style"
		@dblclick="$emit('open')"
	/>
</template>

<script>
import { props as BlockTitleProps } from "../Elements/BlockTitle.vue";
import { disabled } from "@/mixins/props.js";

export const props = {
	mixins: [BlockTitleProps, disabled],
	props: {
		/**
		 * API endpoints
		 * @value { field, model, section }
		 */
		endpoints: {
			default: () => ({}),
			type: [Array, Object]
		},
		/**
		 * A unique ID for the block
		 */
		id: String
	}
};

/**
 * @displayName BlockTypeDefault
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["open", "update"],
	methods: {
		field(name, fallback = null) {
			let field = null;

			for (const tab of Object.values(this.fieldset.tabs ?? {})) {
				if (tab.fields[name]) {
					field = tab.fields[name];
				}
			}

			return field ?? fallback;
		},
		open() {
			this.$emit("open");
		},
		update(content) {
			this.$emit("update", {
				...this.content,
				...content
			});
		}
	}
};
</script>

<style>
.k-block-type-default .k-block-title {
	line-height: 1.5em;
}
</style>
