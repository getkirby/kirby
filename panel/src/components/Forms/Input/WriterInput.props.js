import { props as InputProps } from "@/mixins/input.js";
import {
	maxlength,
	minlength,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

/**
 * Props for `k-writer-input`.
 * Kept in their own module so that components which only
 * need the prop definitions don't pull all of ProseMirror.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export const props = {
	mixins: [InputProps, maxlength, minlength, placeholder, spellcheck],
	props: {
		breaks: Boolean,
		code: Boolean,
		emptyDocument: {
			type: Object,
			default: () => ({
				type: "doc",
				content: []
			})
		},
		extensions: Array,
		headings: {
			default: () => [1, 2, 3, 4, 5, 6],
			type: [Array, Boolean]
		},
		inline: Boolean,
		keys: Object,
		marks: {
			type: [Array, Boolean],
			default: true
		},
		nodes: {
			type: [Array, Boolean],
			default: () => ["heading", "bulletList", "orderedList"]
		},
		paste: {
			type: Function,
			default: () => () => false
		},
		/**
		 * See `k-writer-toolbar` for available options
		 */
		toolbar: {
			type: Object,
			default: () => ({
				inline: true
			})
		},
		value: {
			type: String,
			default: ""
		}
	}
};

export default props;
