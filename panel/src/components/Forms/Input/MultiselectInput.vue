<script>
import TagsInput from "./TagsInput.vue";

export default {
	extends: TagsInput,
	inheritAttrs: false,
	props: {
		search: [Object, Boolean]
	}
};
</script>
