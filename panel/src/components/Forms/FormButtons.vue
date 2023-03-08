<template>
	<nav class="k-form-buttons">
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-if="message" class="k-help" v-html="message" />

		<k-button-group
			:buttons="buttons"
			:theme="theme"
			layout="collapsed"
			variant="filled"
		/>

		<k-dialog
			ref="revert"
			:submit-button="$t('revert')"
			icon="undo"
			theme="negative"
			@submit="revert"
		>
			<k-text :html="$t('revert.confirm')" />
		</k-dialog>
	</nav>
</template>

<script>
import { set } from "vue";

export default {
	props: {
		lock: [Boolean, Object]
	},
	data() {
		return {
			isRefreshing: null,
			isLocking: null
		};
	},
	computed: {
		api() {
			// always use silent requests (without loading spinner)
			return [this.$view.path + "/lock", null, null, true];
		},
		message() {
			if (this.mode === "unlock") {
				return this.$t("lock.isUnlocked");
			}

			if (this.mode === "lock") {
				return this.$t("lock.isLocked", {
					email: this.$esc(this.lock.data.email)
				});
			}

			return false;
		},
		buttons() {
			if (this.mode === "unlock") {
				return [
					{
						icon: "download",
						text: this.$t("download"),
						responsive: true,
						click: this.onDownload
					},
					{
						icon: "check",
						text: this.$t("confirm"),
						click: this.onResolve
					}
				];
			}

			if (this.mode === "lock") {
				if (this.lock.data.unlockable) {
					return [
						{
							icon: "unlock",
							text: this.$t("lock.unlock"),
							click: this.onUnlock
						}
					];
				}

				return [
					{
						icon: "loader"
					}
				];
			}

			if (this.mode === "changes") {
				return [
					{
						icon: "undo",
						text: this.$t("revert"),
						responsive: true,
						disabled: this.isDisabled,
						click: this.onRevert
					},
					{
						icon: "check",
						text: this.$t("save"),
						disabled: this.isDisabled,
						click: this.onSave
					}
				];
			}

			return [];
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

			return "notice";
		}
	},
	watch: {
		hasChanges: {
			handler(changes, before) {
				if (this.supportsLocking === true) {
					if (this.isLocked === false && this.isUnlocked === false) {
						if (changes === true) {
							// unsaved changes, write lock every 30 seconds
							this.onLock();
							this.isLocking = setInterval(this.onLock, 30000);
						} else if (before) {
							// no more unsaved changes, stop writing lock, remove lock
							clearInterval(this.isLocking);
							this.onLock(false);
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
				this.$events.$emit("model.reload");
			}
		}
	},
	created() {
		// refresh lock data every 10 seconds
		if (this.supportsLocking) {
			this.isRefreshing = setInterval(this.check, 10000);
		}
		this.$events.$on("keydown.cmd.s", this.onSave);
	},
	destroyed() {
		// make sure to clear all intervals
		clearInterval(this.isRefreshing);
		clearInterval(this.isLocking);
		this.$events.$off("keydown.cmd.s", this.onSave);
	},
	methods: {
		async check() {
			const { lock } = await this.$api.get(...this.api);
			set(this.$view.props, "lock", lock);
		},
		async onLock(lock = true) {
			// writing lock
			if (lock === true) {
				try {
					await this.$api.patch(...this.api);
				} catch (error) {
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
		/**
		 * Download unsaved changes after model got unlocked
		 */
		onDownload() {
			let content = "";
			const changes = this.$store.getters["content/changes"]();

			Object.keys(changes).forEach((key) => {
				content += key + ": \n\n" + changes[key];
				content += "\n\n----\n\n";
			});

			let link = document.createElement("a");
			link.setAttribute(
				"href",
				"data:text/plain;charset=utf-8," + encodeURIComponent(content)
			);
			link.setAttribute("download", this.$view.path + ".txt");
			link.style.display = "none";

			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
		},
		async onResolve() {
			// remove content unlock and throw away unsaved changes
			await this.onUnlock(false);
			this.$store.dispatch("content/revert");
		},
		onRevert() {
			this.$refs.revert.open();
		},
		async onSave(e) {
			if (!e) {
				return false;
			}

			e.preventDefault?.();

			try {
				await this.$store.dispatch("content/save");
				this.$events.$emit("model.update");
				this.$store.dispatch("notification/success", ":)");
			} catch (response) {
				if (response.code === 403) {
					return;
				}

				if (response.details && Object.keys(response.details).length > 0) {
					this.$store.dispatch("notification/error", {
						message: this.$t("error.form.incomplete"),
						details: response.details
					});
				} else {
					this.$store.dispatch("notification/error", {
						message: this.$t("error.form.notSaved"),
						details: [
							{
								label: "Exception: " + response.exception,
								message: response.message
							}
						]
					});
				}
			}
		},
		async onUnlock(unlock = true) {
			const api = [this.$view.path + "/unlock", null, null, true];

			if (unlock === true) {
				// unlocking (writing unlock)
				await this.$api.patch(...api);
			} else {
				// resolving unlock (removing unlock)
				await this.$api.delete(...api);
			}

			this.$reload({ silent: true });
		},
		revert() {
			this.$store.dispatch("content/revert");
			this.$refs.revert.close();
		}
	}
};
</script>

<style>
.k-form-buttons {
	display: flex;
	align-items: center;
	gap: 1rem;
}
.k-form-buttons .k-help {
	max-width: 22rem;
}

@container (max-width: 60rem) {
	.k-form-buttons .k-help {
		display: none;
	}
}
</style>
