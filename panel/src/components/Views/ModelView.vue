<script>
import { length } from "@/helpers/object";
import throttle from "@/helpers/throttle.js";

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
			const changes = {};

			for (const field in this.content) {
				const changed = JSON.stringify(this.content[field]);
				const original = JSON.stringify(this.originals[field]);

				if (changed !== original) {
					changes[field] = this.content[field];
				}
			}

			return changes;
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
			return length(this.changes) > 0;
		},
		modified() {
			return this.lock.modified;
		},
		protectedFields() {
			return [];
		}
	},
	mounted() {
		// create a delayed version of save
		// that we can use in the input event
		this.autosave = throttle(this.save, 1000, {
			leading: true,
			trailing: true
		});

		this.$events.on("beforeunload", this.onBeforeUnload);
		this.$events.on("model.reload", this.$reload);
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
		this.$events.on("view.save", this.onSave);
	},
	destroyed() {
		this.$events.off("beforeunload", this.onBeforeUnload);
		this.$events.off("model.reload", this.$reload);
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
		this.$events.off("view.save", this.onSave);
	},
	methods: {
		async save(api, values) {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.save(api, values);

			// update the last modification timestamp
			// if the view hasn't changed in the meantime,
			// in which case the correct timestamp will come
			// from the server
			if (api === this.api) {
				this.$panel.view.props.lock.modified = new Date();
			}

			this.isSaved = true;
		},
		onBeforeUnload(e) {
			if (this.isSaved === false) {
				e.preventDefault();
				e.returnValue = "";
			}
		},
		async onDiscard() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.discard(this.api);
			this.$panel.view.props.content = this.$panel.view.props.originals;
			this.$panel.view.reload();
		},
		onInput(values) {
			if (this.isLocked === true) {
				return false;
			}

			this.update(values);
			this.autosave(this.api, values);
		},
		onSave(e) {
			e?.preventDefault?.();
			this.onSubmit();
		},
		async onSubmit(values = {}) {
			if (this.isLocked === true) {
				return false;
			}

			this.update(values);

			await this.$panel.content.publish(this.api, this.content);

			this.$panel.view.props.originals = this.content;
			this.$panel.notification.success();

			this.$events.emit("model.update");

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
		},
		update(values) {
			if (length(values) === 0) {
				return;
			}

			this.$panel.view.props.content = {
				...this.originals,
				...values
			};

			this.isSaved = false;
		}
	}
};
</script>
