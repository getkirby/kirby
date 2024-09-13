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
			:disabled="disabled"
			:responsive="true"
			:theme="theme"
		/>
	</k-button-group>
</template>

<script>
import { set } from "vue";

export default {
	data() {
		return {
			isLoading: null
		};
	},
	computed: {
		buttons() {
			// if (this.mode === "unlock") {
			// 	return [
			// 		{
			// 			icon: "check",
			// 			text: this.$t("lock.isUnlocked"),
			// 			click: () => this.resolve()
			// 		},
			// 		{
			// 			icon: "download",
			// 			text: this.$t("download"),
			// 			click: () => this.download()
			// 		}
			// 	];
			// }

			// if (this.mode === "lock") {
			// 	return [
			// 		{
			// 			icon: this.$panel.content.lock.unlockable ? "unlock" : "loader",
			// 			text: this.$t("lock.isLocked", {
			// 				email: this.$esc(this.$panel.content.lock.email)
			// 			}),
			// 			title: this.$t("lock.unlock"),
			// 			disabled: !this.$panel.content.lock.unlockable,
			// 			click: () => this.unlock()
			// 		}
			// 	];
			// }

			if (this.mode === "changes") {
				return [
					{
						icon: "undo",
						text: this.$t("revert"),
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
			// if (this.mode === "unlock") {
			// 	return false;
			// }

			// if (this.mode === "lock") {
			// 	return !this.$panel.content.lock.unlockable;
			// }

			if (this.mode === "changes") {
				return this.$panel.content.isPublishing;
			}

			return false;
		},
		mode() {
			if (this.$panel.content.hasUnpublishedChanges === true) {
				return "changes";
			}

			return null;
		},
		theme() {
			// if (this.mode === "lock") {
			// 	return "negative";
			// }
			// if (this.mode === "unlock") {
			// 	return "info";
			// }

			if (this.mode === "changes") {
				return "notice";
			}

			return null;
		}
	},
	methods: {
		download() {
			throw new Error("Not implemented");
		},
		async resolve() {
			throw new Error("Not implemented");
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
						this.$emit("discard");
						this.$panel.dialog.close();
					}
				}
			});
		},
		save(e) {
			e?.preventDefault?.();
			this.$emit("submit");
		},
		async unlock() {
			throw new Error("Not implemented");
		}
	}
};
</script>
