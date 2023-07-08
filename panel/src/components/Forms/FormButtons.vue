<template>
	<nav v-if="hasChanges" class="k-form-buttons">
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-if="message" class="k-help" v-html="message" />

		<k-button-group layout="collapsed">
			<k-button
				v-bind="button"
				size="sm"
				variant="filled"
				:disabled="disabled"
				:theme="theme"
			/>
			<template v-if="mode">
				<k-button
					icon="dots"
					size="sm"
					variant="filled"
					:disabled="disabled"
					:theme="theme"
					@click="$refs.dropdown.toggle()"
				/>
				<k-dropdown-content ref="dropdown" :options="dropdown" align-x="end" />
			</template>
		</k-button-group>
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
			return [this.$panel.view.path + "/lock", null, null, true];
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
		button() {
			if (this.mode === "unlock") {
				return {
					icon: "check",
					text: this.$t("confirm"),
					click: this.onResolve
				};
			}

			if (this.mode === "lock") {
				return {
					icon: "unlock",
					text: this.$t("lock.unlock"),
					click: this.onUnlock
				};
			}

			if (this.mode === "changes") {
				return {
					icon: "circle-nested",
					text: this.$t("save"),
					disabled: this.isDisabled,
					click: this.onSave
				};
			}

			return {
				icon: "check",
				text: "No changes",
				click: this.onSave
			};
		},
		dropdown() {
			if (this.mode === "changes") {
				return [
					{
						icon: "undo",
						text: this.$t("revert"),
						disabled: this.isDisabled,
						click: this.revert
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
		this.$events.on("view.save", this.onSave);
	},
	destroyed() {
		// make sure to clear all intervals
		clearInterval(this.isRefreshing);
		clearInterval(this.isLocking);
		this.$events.off("view.save", this.onSave);
	},
	methods: {
		async check() {
			const { lock } = await this.$api.get(...this.api);
			set(this.$panel.view.props, "lock", lock);
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
			link.setAttribute("download", this.$panel.view.path + ".txt");
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
		async onSave(e) {
			e.preventDefault?.();

			await this.$store.dispatch("content/save");
			this.$events.$emit("model.update");
			this.$panel.notification.success();
		},
		async onUnlock(unlock = true) {
			const api = [this.$panel.view.path + "/unlock", null, null, true];

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
