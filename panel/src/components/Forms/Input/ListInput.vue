<template>
	<k-writer
		ref="input"
		v-bind="$props"
		:extensions="extensions"
		:value="list"
		class="k-list-input"
		@input="onInput"
	/>
</template>

<script>
import { disabled } from "@/mixins/props.js";
import ListDoc from "@/components/Forms/Writer/Nodes/ListDoc.js";

export const props = {
	mixins: [disabled],
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		keys: Object,
		nodes: {
			type: Array,
			default: () => ["bulletList", "orderedList"]
		},
		marks: {
			type: [Array, Boolean],
			default: true
		},
		spellcheck: Boolean,
		value: String
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			list: this.value,
			html: this.value
		};
	},
	computed: {
		extensions() {
			return [
				new ListDoc({
					inline: true,
					nodes: this.nodes
				})
			];
		}
	},
	watch: {
		value(html) {
			// if we don't compare the passed html
			// the writer stops from working properly
			// the list is updated with trimmed spaces
			// which leads to unwanted side-effects
			if (html !== this.html) {
				this.list = html;
				this.html = html;
			}
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onInput(html) {
			let dom = new DOMParser().parseFromString(html, "text/html");
			let list = dom.querySelector("ul, ol");

			if (!list) {
				this.$emit("input", (this.list = ""));
				return;
			}

			let text = list.textContent.trim();

			if (text.length === 0) {
				this.$emit("input", (this.list = ""));
				return;
			}

			// updates `list` data with raw html
			this.list = html;
			this.html = html.replace(/(<p>|<\/p>)/gi, "");

			// emit value with removed `<p>` and `</p>` tags from html value
			this.$emit("input", this.html);
		}
	}
};
</script>

<style>
.k-list-input .ProseMirror ol > li::marker {
	font-size: var(--text-sm);
	color: var(--color-gray-500);
}
</style>
