<script>
import TagsInput, { props as TagsInputProps } from "./TagsInput.vue";

export const props = {
	mixins: [TagsInputProps],
	props: {
		accept: {
			default: "options",
			type: String
		},
		layout: {
			default: "list",
			type: String
		}
	}
};

export default {
	extends: TagsInput,
	mixins: [props],
	class: "k-multiselect-input"
};
</script>
