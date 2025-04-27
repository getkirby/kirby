<template>
	<k-button-group
		v-if="buttons.length > 0"
		layout="collapsed"
		class="k-form-buttons"
	>
		<k-button
			v-for="button in buttons"
			:key="button.icon"
			v-bind="button"
			size="sm"
			variant="filled"
			:disabled="isDisabled"
			:theme="theme"
		/>
	</k-button-group>
</template>

<script>
import { set } from "vue";

export default {
	props: {
		lock: [Boolean, Object]
	},
	data() {
		return {
			isLoading: null,
			isLocking: null
		};
	},
	computed: {
		api() {
			// always use silent requests (without loading spinner)
			return [this.$panel.view.path + "/lock", null, null, true];
		},
		buttons() {
			if (this.mode === "unlock") {
				return [
					{
						icon: "check",
						text: this.$t("lock.isUnlocked"),
						click: () => this.resolve()
					},
					{
						icon: "download",
						text: this.$t("download"),
						responsive: true,
						click: () => this.download()
					}
				];
			}

			if (this.mode === "lock") {
				return [
					{
						icon: this.lock.data.unlockable ? "unlock" : "loader",
						text: this.$t("lock.isLocked", {
							email: this.$esc(this.lock.data.email)
						}),
						title: this.$t("lock.unlock"),
						disabled: !this.lock.data.unlockable,
						click: () => this.unlock()
					}
				];
			}

			if (this.mode === "changes") {
				return [
					{
						icon: "undo",
						text: this.$t("revert"),
						responsive: true,
						click: () => this.revert()
					},
					{
						icon: "check",
						text: this.$t("save"),
						click: () => this.save()
					}
				];
			}

			return [];
		},
		disabled() {
			if (this.mode === "unlock") {
				return false;
			}

			if (this.mode === "lock") {
				return !this.lock.data.unlockable;
			}

			if (this.mode === "changes") {
				return this.isDisabled;
			}

			return false;
		},
		hasChanges() {
			return this.$store.getters["content/hasChanges"]();
		},
		isDisabled() {
			return this.$store.state.content.status.enabled === false;
		},
		isLocked() {
			return this.lockState === "lock";
		},
		isUnlocked() {
			return this.lockState === "unlock";
		},
		mode() {
			if (this.lockState !== null) {
				return this.lockState;
			}

			if (this.hasChanges === true) {
				return "changes";
			}

			return null;
		},
		lockState() {
			return this.supportsLocking && this.lock ? this.lock.state : null;
		},
		supportsLocking() {
			return this.lock !== false;
		},
		theme() {
			if (this.mode === "lock") {
				return "negative";
			}
			if (this.mode === "unlock") {
				return "info";
			}
			if (this.mode === "changes") {
				return "notice";
			}

			return null;
		}
	},
	watch: {
		hasChanges: {
			handler(changes, before) {
				if (this.supportsLocking === true) {
					if (this.isLocked === false && this.isUnlocked === false) {
						if (changes === true) {
							// unsaved changes, write lock every 30 seconds
							this.locking();
							this.isLocking = setInterval(this.locking, 30000);
						} else if (before) {
							// no more unsaved changes, stop writing lock, remove lock
							clearInterval(this.isLocking);
							this.locking(false);
						}
					}
				}
			},
			immediate: true
		},
		isLocked(locked) {
			// model used to be locked by another user,
			// lock has been lifted, so refresh data
			if (locked === false) {
				this.$events.emit("model.reload");
			}
		}
	},
	mounted() {
		// refresh lock data every 10 seconds
		if (this.supportsLocking) {
			this.isLoading = setInterval(this.check, 10000);
		}
		this.$events.on("view.save", this.save);
	},
	destroyed() {
		// make sure to clear all intervals
		clearInterval(this.isLoading);
		clearInterval(this.isLocking);
		this.$events.off("view.save", this.save);
	},
	methods: {
		async check() {
			if (this.$panel.isOffline === false) {
				const { lock } = await this.$api.get(...this.api);
				set(this.$panel.view.props, "lock", lock);
			}
		},
		download() {
			let content = "";
			const changes = this.$store.getters["content/changes"]();

			for (const key in changes) {
				const change = changes[key];
				content += key + ": \n\n";

				// provides pretty JSON output for only object and array values
				// @todo refactor with better method for type-based output
				if (
					(typeof change === "object" && Object.keys(change).length) ||
					(Array.isArray(change) && change.length)
				) {
					content += JSON.stringify(change, null, 2);
				} else {
					content += change;
				}

				content += "\n\n----\n\n";
			}

			let link = document.createElement("a");
			link.setAttribute(
				"href",
				"data:text/plain;charset=utf-8," + encodeURIComponent(content)
			);
			link.setAttribute("download", this.$panel.view.path + ".txt");
			link.style.display = "none";

			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		},
		async locking(lock = true) {
			if (this.$panel.isOffline === true) {
				return;
			}

			// writing lock
			if (lock === true) {
				try {
					await this.$api.patch(...this.api);
				} catch {
					// If setting lock failed, a competing lock has been set between
					// API calls. In that case, discard changes, stop setting lock
					clearInterval(this.isLocking);
					this.$store.dispatch("content/revert");
				}
			}

			// removing lock
			else {
				clearInterval(this.isLocking);
				await this.$api.delete(...this.api);
			}
		},
		async resolve() {
			// remove content unlock and throw away unsaved changes
			await this.unlock(false);
			this.$store.dispatch("content/revert");
		},
		revert() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					submitButton: {
						icon: "undo",
						text: this.$t("revert")
					},
					text: this.$t("revert.confirm")
				},
				on: {
					submit: () => {
						this.$store.dispatch("content/revert");
						this.$panel.dialog.close();
					}
				}
			});
		},
		async save(e) {
			e?.preventDefault?.();

			await this.$store.dispatch("content/save");
			this.$events.emit("model.update");
			this.$panel.notification.success();
		},
		async unlock(unlock = true) {
			const api = [this.$panel.view.path + "/unlock", null, null, true];

			if (unlock === true) {
				this.$panel.dialog.open({
					component: "k-remove-dialog",
					props: {
						submitButton: {
							icon: "unlock",
							text: this.$t("lock.unlock")
						},
						text: this.$t("lock.unlock.submit", {
							email: this.$esc(this.lock.data.email)
						})
					},
					on: {
						submit: async () => {
							// unlocking (writing unlock)
							await this.$api.patch(...api);

							this.$panel.dialog.close();
							this.$reload({ silent: true });
						}
					}
				});
				return;
			}

			// resolving unlock (removing unlock)
			await this.$api.delete(...api);

			this.$reload({ silent: true });
		}
	}
};
</script>
