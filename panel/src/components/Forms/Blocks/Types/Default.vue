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
		 * @value { field, model }
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
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	/**
	 * All events that `k-block` listens to need to be declared.
	 * Otherwise, the listeners end up as native DOM listeners on
	 * the root element (see `preserveListeners` mixin) and
	 * e.g. native `copy` or `paste` events from inputs within the
	 * block would trigger the block's copy/paste actions.
	 */
	emits: [
		"append",
		"chooseToAppend",
		"chooseToConvert",
		"chooseToPrepend",
		"close",
		"copy",
		"duplicate",
		"focus",
		"hide",
		"merge",
		"open",
		"paste",
		"prepend",
		"remove",
		"removeSelected",
		"show",
		"sortDown",
		"sortUp",
		"split",
		"update"
	],
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
