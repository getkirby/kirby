<script>
import debounce from "@/helpers/debounce.js";

/**
 * @internal
 */
export default {
	props: {
		blueprint: String,
		buttons: Array,
		next: Object,
		prev: Object,
		permissions: {
			type: Object,
			default: () => ({})
		},
		lock: {
			type: [Boolean, Object]
		},
		model: {
			type: Object,
			default: () => ({})
		},
		tab: {
			type: Object,
			default() {
				return {
					columns: []
				};
			}
		},
		tabs: {
			type: Array,
			default: () => []
		}
	},
	computed: {
		content() {
			return this.$panel.content.values;
		},
		id() {
			return this.model.link;
		},
		isLocked() {
			return this.$panel.content.isLocked;
		},
		protectedFields() {
			return [];
		}
	},
	watch: {
		"$panel.view.timestamp": {
			handler() {
				this.$store.dispatch("content/create", {
					id: this.id,
					api: this.id,
					content: this.model.content,
					ignore: this.protectedFields
				});
			},
			immediate: true
		}
	},
	mounted() {
		this.onInput = debounce(this.onInput, 50);

		this.$events.on("model.reload", this.$reload);
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
		this.$events.on("view.save", this.onSave);
	},
	destroyed() {
		this.$events.off("model.reload", this.$reload);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("view.save", this.onSave);
	},
	methods: {
		onDiscard() {
			this.$panel.content.discard();
		},
		onInput(values) {
			this.$panel.content.set(values);
		},
		onSave(e) {
			e?.preventDefault?.();
			this.onSubmit();
		},
		onSubmit(values = {}) {
			this.$panel.content.set(values);
			this.$panel.content.publish();
		},
		toPrev(e) {
			if (this.prev && e.target.localName === "body") {
				this.$go(this.prev.link);
			}
		},
		toNext(e) {
			if (this.next && e.target.localName === "body") {
				this.$go(this.next.link);
			}
		}
	}
};
</script>
