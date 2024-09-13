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
		<k-dropdown-content
			v-if="lock.isActive"
			ref="lock"
			align-x="end"
			class="k-form-controls-dropdown"
		>
			<p>
				{{ $t("form.locked") }}
			</p>
			<hr />
			<dl>
				<div>
					<dt><k-icon type="user" /></dt>
					<dd>{{ lock.user.email }}</dd>
				</div>
				<div>
					<dt><k-icon type="clock" /></dt>
					<dd>12 minutes ago</dd>
				</div>
			</dl>
			<hr />
			<k-dropdown-item icon="preview">Preview changes</k-dropdown-item>
		</k-dropdown-content>
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
						dropdown: true,
						text: this.lock.user.email,
						icon: "lock",
						click: () => this.$refs.lock.toggle()
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
		}
	}
};
</script>

<style>
.k-form-controls-dropdown {
	max-width: 15rem;
}
.k-form-controls-dropdown p {
	line-height: var(--leading-normal);
	padding: var(--spacing-1) var(--spacing-2);
}
.k-form-controls-dropdown dl div {
	padding: var(--spacing-1) var(--spacing-2);
	line-height: var(--leading-normal);
	display: flex;
	align-items: center;
	gap: 0.75rem;
	color: var(--color-gray-500);
}
</style>
