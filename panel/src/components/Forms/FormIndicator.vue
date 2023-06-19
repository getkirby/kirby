<template>
	<k-dialog v-bind="$props" class="k-form-indicator">
		<template #header>
			<div :data-theme="theme" class="k-notification">
				<p v-if="options.length">{{ $t("lock.unsaved") }}</p>
				<p v-else>{{ $t("lock.unsaved.empty") }}</p>
				<k-button icon="cancel" @click="$panel.dialog.close()" />
			</div>
		</template>

		<k-items v-if="options.length" :items="items" layout="list" />
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		cancelButton: {
			default: false
		},
		size: {
			default: "medium"
		},
		submitButton: {
			default: false
		}
	},
	data() {
		return {
			options: []
		};
	},
	computed: {
		hasChanges() {
			return this.ids.length > 0;
		},
		ids() {
			return Object.keys(this.store).filter(
				(id) => this.$helper.object.length(this.store[id]?.changes) > 0
			);
		},
		items() {
			return this.options.map((option) => ({
				...option,
				image: { icon: option.icon, back: "black", color: "gray-300" }
			}));
		},
		store() {
			return this.$store.state.content.models;
		},
		theme() {
			return this.options.length ? "notice" : "info";
		}
	},
	async mounted() {
		try {
			await this.$dropdown("changes", {
				method: "POST",
				body: {
					ids: this.ids
				}
			})((options) => {
				this.options = options;
			});
		} catch (e) {
			this.options = [];
			this.$store.dispatch("content/clear");
		}
	}
};
</script>

<style>
.k-form-indicator .k-notification p {
	font-weight: var(--font-bold);
}
</style>
