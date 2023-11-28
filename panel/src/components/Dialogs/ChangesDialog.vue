<template>
	<k-dialog v-bind="$props" class="k-changes-dialog">
		<template v-if="loading === false">
			<k-headline>{{ $t("lock.unsaved") }}</k-headline>
			<k-items v-if="changes.length" :items="changes" layout="list" />
			<k-empty v-else icon="edit-line">{{ $t("lock.unsaved.empty") }}</k-empty>
		</template>
		<template v-else>
			<k-icon type="loader" />
		</template>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @since 4.0.0
 */
export default {
	mixins: [Dialog],
	props: {
		// eslint-disable-next-line vue/require-prop-types
		cancelButton: {
			default: false
		},
		changes: {
			type: Array
		},
		loading: {
			type: Boolean
		},
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "medium"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: false
		}
	},
	computed: {
		ids() {
			return Object.keys(this.store).filter(
				(id) => this.$helper.object.length(this.store[id]?.changes) > 0
			);
		},
		store() {
			return this.$store.state.content.models;
		}
	},
	watch: {
		ids: {
			handler(ids) {
				this.$panel.dialog.refresh({
					method: "POST",
					body: {
						ids: ids
					}
				});
			},
			immediate: true
		}
	}
};
</script>

<style>
.k-changes-dialog .k-headline {
	margin-top: -0.5rem;
	margin-bottom: var(--spacing-3);
}
</style>
