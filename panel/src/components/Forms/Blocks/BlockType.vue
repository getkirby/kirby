<script>
/**
 * @internal
 */
export default {
	inheritAttrs: false,
	props: {
		content: [Object, Array],
		fieldset: Object,
		id: String
	},
	methods: {
		field(name, fallback = null) {
			let field = null;

			for (const tab of Object.values(this.fieldset.tabs)) {
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
