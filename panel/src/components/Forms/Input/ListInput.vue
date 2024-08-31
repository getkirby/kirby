<template>
	<k-writer-input
		ref="input"
		v-bind="$props"
		:class="['k-list-input', $attrs.class]"
		:extensions="listExtensions"
		:style="$attrs.style"
		:value="list"
		@input="onInput"
	/>
</template>

<script>
import Input from "@/mixins/input.js";
import { props as WriterInputProps } from "@/components/Forms/Input/WriterInput.vue";
import ListDoc from "@/components/Forms/Writer/Nodes/ListDoc.js";

export const props = {
	mixins: [WriterInputProps],
	inheritAttrs: false,
	props: {
		nodes: {
			type: Array,
			default: () => ["bulletList", "orderedList"]
		}
	}
};

export default {
	mixins: [Input, props],
	data() {
		return {
			list: this.value,
			html: this.value
		};
	},
	computed: {
		listExtensions() {
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
.k-list-input.k-writer-input[data-placeholder][data-empty="true"]::before {
	padding-inline-start: 2.5em;
}
</style>
