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
		changes() {
			return this.$panel.content.changes;
		},
		content() {
			return this.$panel.content.values;
		},
		id() {
			return this.model.link;
		},
		isLocked() {
			return false;
		},
		isUnsaved() {
			return this.$helper.object.length(this.changes) > 0;
		},
		protectedFields() {
			return [];
		}
	},
	watch: {
		"$panel.view.timestamp": {
			handler() {
				// this is a temporary emulation of what should be coming
				// directly from the backend.
				this.$panel.view.props.originals = this.model.content;
				this.$panel.view.props.content = this.model.content;
				this.$panel.view.props.api = this.id;
			},
			immediate: true
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
