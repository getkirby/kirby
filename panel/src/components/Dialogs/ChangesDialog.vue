<template>
	<k-dialog v-bind="$props" class="k-changes-dialog">
		<section v-if="pages.length">
			<k-headline>Unsaved pages</k-headline>
			<k-items :items="pages" layout="list" />
		</section>

		<section v-if="files.length">
			<k-headline>Unsaved files</k-headline>
			<k-items :items="files" layout="list" />
		</section>

		<section v-if="users.length">
			<k-headline>Unsaved accounts</k-headline>
			<k-items :items="users" layout="list" />
		</section>

		<section v-if="!pages.length && !files.length && !users.length">
			<k-headline>Unsaved changes</k-headline>
			<k-empty icon="edit-line">{{ $t("lock.unsaved.empty") }}</k-empty>
		</section>
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
		files: {
			type: Array
		},
		pages: {
			type: Array
		},
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "medium"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: false
		},
		users: {
			type: Array
		}
	}
};
</script>

<style>
.k-changes-dialog section + section {
	margin-top: var(--spacing-6);
}
.k-changes-dialog .k-headline {
	margin-top: -0.5rem;
	margin-bottom: var(--spacing-3);
}
</style>
