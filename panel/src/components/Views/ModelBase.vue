<script>
/**
 * @internal
 */
export default {
	feature: "view",
	props: {
		api: String,
		blueprint: String,
		id: String,
		link: String,
		lock: {
			type: [Boolean, Object]
		},
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
		uuid: String,
		versions: Object
	},
	data() {
		return {
			isSaved: true
		};
	},
	computed: {
		content() {
			return this.versions.changes;
		},
		diff() {
			return this.$panel.content.diff({
				model: this.$panel[this.$options.feature].props
			});
		},
		editor() {
			return this.lock.user.email;
		},
		hasDiff() {
			return this.$panel.content.hasDiff({
				model: this.$panel[this.$options.feature].props
			});
		},
		isLocked() {
			return this.lock.isLocked;
		},
		isSaving() {
			return this.$panel.content.isProcessing;
		},
		modified() {
			return this.lock.modified;
		}
	},
	mounted() {
		this.$events.on("beforeunload", this.onBeforeUnload);
		this.$events.on("content.save", this.onContentSave);
		this.$events.on("model.reload", this.$reload);
	},
	unmounted() {
		this.$events.off("beforeunload", this.onBeforeUnload);
		this.$events.off("content.save", this.onContentSave);
		this.$events.off("model.reload", this.$reload);
	},
	methods: {
		onBeforeUnload(e) {
			if (this.$panel.content.isProcessing === true || this.isSaved === false) {
				e.preventDefault();
				e.returnValue = "";
			}
		},
		onContentSave({ api, language }) {
			if (api === this.api && language === this.$panel.language.code) {
				this.isSaved = true;
			}
		},
		async onDiscard() {
			await this.$panel.content.discard({
				model: this.$panel[this.$options.feature].props,
				language: this.$panel.language.code
			});

			this.$panel.view.refresh();
			await this.$panel.drawer.refresh();
		},
		onInput(values) {
			// update the content for the current view
			// this will also refresh the content prop
			this.$panel.content.updateLazy(values, {
				model: this.$panel[this.$options.feature].props,
				language: this.$panel.language.code
			});
		},
		async onSubmit() {
			try {
				await this.$panel.content.publish(this.content, {
					model: this.$panel[this.$options.feature].props,
					language: this.$panel.language.code
				});

				this.$panel.notification.success();
				this.$events.emit("model.update");

				// the view needs to be refreshed to get an updated set of props
				// this will also rerender sections if needed
				await this.$panel.view.refresh();
				await this.$panel.drawer.refresh();
			} catch (error) {
				this.$panel.notification.error(error);
			}
		}
	}
};
</script>
