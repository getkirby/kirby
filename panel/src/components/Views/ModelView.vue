<script>
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
				this.$panel.content.api = this.id;
				this.$panel.content.changes = {};
				this.$panel.content.published = this.model.content;
			},
			immediate: true
		}
	},
	mounted() {
		this.$events.on("model.reload", this.$reload);
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
	},
	destroyed() {
		this.$events.off("model.reload", this.$reload);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
	},
	methods: {
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
