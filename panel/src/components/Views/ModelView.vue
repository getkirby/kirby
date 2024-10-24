<script>
import throttle from "@/helpers/throttle.js";

/**
 * @internal
 */
export default {
	props: {
		api: String,
		blueprint: String,
		buttons: Array,
		changesUrl: String,
		content: Object,
		id: String,
		link: String,
		lock: {
			type: [Boolean, Object]
		},
		model: Object,
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
		editor() {
			return this.lock.user.email;
		},
		hasTabs() {
			return this.tabs.length > 1;
		},
		isLocked() {
			return this.lock.isLocked;
		},
		isUnsaved() {
			return this.$panel.content.hasChanges;
		},
		modified() {
			return this.lock.modified;
		},
		protectedFields() {
			return [];
		}
	},
	mounted() {
		this.autosave = throttle(this.autosave, 1000, {
			leading: true,
			trailing: true
		});

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
			if (this.isLocked === true) {
				return false;
			}

			this.$panel.content.save();
		},
		async onDiscard() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.discard();
			this.$panel.view.reload();
		},
		onInput(values) {
			if (this.isLocked === true) {
				return false;
			}

			this.$panel.content.update(values);
			this.autosave();
		},
		onSave(e) {
			e?.preventDefault?.();
			this.onSubmit();
		},
		async onSubmit(values = {}) {
			if (this.isLocked === true) {
				return false;
			}

			this.$panel.content.update(values);
			await this.$panel.content.publish();
			await this.$panel.view.refresh();
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
