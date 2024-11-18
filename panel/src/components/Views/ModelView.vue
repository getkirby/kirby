<script>
import { length } from "@/helpers/object";

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
	data() {
		return {
			isSaved: true
		};
	},
	computed: {
		changes() {
			return this.$panel.content.changes(this.api);
		},
		editor() {
			return this.lock.user.email;
		},
		hasChanges() {
			return length(this.changes) > 0;
		},
		hasTabs() {
			return this.tabs.length > 1;
		},
		isLocked() {
			return this.lock.isLocked;
		},
		modified() {
			return this.lock.modified;
		}
	},
	mounted() {
		this.$events.on("beforeunload", this.onBeforeUnload);
		this.$events.on("content.save", this.onContentSave);
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
		this.$events.on("model.reload", this.$reload);
		this.$events.on("view.save", this.onSubmitShortcut);
	},
	destroyed() {
		this.$events.off("beforeunload", this.onBeforeUnload);
		this.$events.off("content.save", this.onContentSave);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("model.reload", this.$reload);
		this.$events.off("view.save", this.onSubmitShortcut);
	},
	methods: {
		onBeforeUnload(e) {
			if (this.$panel.content.isProcessing === true || this.isSaved === false) {
				e.preventDefault();
				e.returnValue = "";
			}
		},
		onContentSave({ api }) {
			if (api === this.api) {
				this.isSaved = true;
			}
		},
		async onDiscard() {
			await this.$panel.content.discard(this.api);
			this.$panel.view.reload();
		},
		onInput(values) {
			this.$panel.content.update(values, this.api);
			this.$panel.content.saveLazy(values, this.api);
		},
		async onSubmit(values = {}) {
			if (length(values) > 0) {
				this.$panel.content.update(values, this.api);
			}

			await this.$panel.content.publish(this.content, this.api);

			this.$panel.notification.success();
			this.$events.emit("model.update");

			await this.$panel.view.refresh();
		},
		onSubmitShortcut(e) {
			e?.preventDefault?.();
			this.onSubmit();
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
