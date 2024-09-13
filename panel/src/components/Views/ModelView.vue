<script>
import debounce from "@/helpers/debounce";

/**
 * @internal
 */
export default {
	props: {
		api: String,
		blueprint: String,
		buttons: Array,
		content: Object,
		lock: Object,
		model: {
			type: Object,
			default: () => ({})
		},
		next: Object,
		originals: Object,
		prev: Object,
		permissions: {
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
		changes() {
			return this.$panel.content.changes;
		},
		id() {
			return this.model.link;
		},
		isLocked() {
			return this.lock.isActive === true;
		},
		protectedFields() {
			return [];
		}
	},
	mounted() {
		this.autosave = debounce(this.autosave, 200);

		this.$events.on("model.reload", this.$reload);
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
		this.$events.on("view.save", this.onSubmit);
	},
	destroyed() {
		this.$events.off("model.reload", this.$reload);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("view.save", this.onSubmit);
	},
	methods: {
		autosave() {
			this.$panel.content.save();
		},
		onDiscard() {
			this.$panel.content.discard();
		},
		onInput(values) {
			this.$panel.content.change(values);
			this.autosave();
		},
		onSubmit(e) {
			e?.preventDefault?.();
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
