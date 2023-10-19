<script>
import { props as BlockTitleProps } from "./BlockTitle.vue";

export const props = {
	mixins: [BlockTitleProps],
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
 * @internal
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
