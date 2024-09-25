<script>
import debounce from "@/helpers/debounce.js";

/**
 * @internal
 */
export default {
	props: {
		api: String,
		blueprint: String,
		buttons: Array,
		content: Object,
		id: String,
		link: String,
		lock: {
			type: [Boolean, Object]
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
		},
		uuid: String
	},
	computed: {
		changes() {
			return this.$panel.content.changes;
		},
		hasTabs() {
			return this.tabs.length > 1;
		},
		isLocked() {
			return false;
		},
		isUnsaved() {
			return this.$panel.content.hasChanges;
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
		this.$events.on("view.save", this.onSave);
	},
	destroyed() {
		this.$events.off("model.reload", this.$reload);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("view.save", this.onSave);
	},
	methods: {
		autosave() {
			this.$panel.content.save();
		},
		async onDiscard() {
			await this.$panel.content.discard();
		},
		onInput(values) {
			this.$panel.content.update(values);
			this.autosave();
		},
		onSave(e) {
			e?.preventDefault?.();
			this.onSubmit();
		},
		onSubmit(values = {}) {
			this.$panel.content.update(values);
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
