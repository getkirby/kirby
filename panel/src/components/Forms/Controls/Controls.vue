<template>
	<k-button-group
		v-if="buttons.length"
		layout="collapsed"
		class="k-form-controls"
	>
		<k-button
			v-for="button in buttons"
			:key="button.text"
			v-bind="button"
			size="sm"
			variant="filled"
		/>
	</k-button-group>
</template>

<script>
import { length } from "@/helpers/object.js";

/**
 * @displayName FormControls
 * @since 5.0.0
 */
export default {
	props: {
		/**
		 * An object of changed fields and their changed values
		 */
		changes: Object,
		/**
		 * Whether the content is locked, and if, by whom
		 */
		lock: Object
	},
	emits: ["discard", "submit"],
	computed: {
		buttons() {
			if (this.lock?.isActive === true) {
				return [
					{
						theme: "negative",
						text: this.lock.user.email,
						icon: "lock",
						click: () => this.locked()
					}
				];
			}

			if (length(this.changes) !== 0) {
				return [
					{
						theme: "notice",
						text: this.$t("revert"),
						icon: "undo",
						click: () => this.discard()
					},
					{
						theme: "notice",
						text: this.$t("save"),
						icon: "check",
						click: () => this.$emit("submit")
					}
				];
			}

			return [];
		}
	},
	methods: {
		discard() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					size: "medium",
					submitButton: {
						icon: "undo",
						text: this.$t("form.discard")
					},
					text: this.$t("form.discard.confirm")
				},
				on: {
					submit: () => {
						this.$panel.dialog.close();
						this.$emit("discard");
					}
				}
			});
		},
		locked() {
			this.$panel.notification.open({
				icon: "lock",
				theme: "negative",
				message: this.$t("form.locked")
			});
		}
	}
};
</script>
