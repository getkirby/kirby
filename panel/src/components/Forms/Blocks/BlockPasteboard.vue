<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		class="k-block-importer"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<!-- eslint-disable vue/no-v-html -->
		<label
			for="pasteboard"
			v-html="$t('field.blocks.fieldsets.paste', { shortcut })"
		/>
		<!-- eslint-enable -->
		<textarea id="pasteboard" @paste.prevent="paste" />
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @internal
 */
export default {
	inheritAttrs: false,
	mixins: [Dialog],
	props: {
		cancelButton: {
			default: false
		},
		size: {
			default: "large"
		},
		submitButton: {
			default: false
		}
	},
	computed: {
		shortcut() {
			return this.$helper.keyboard.metaKey() + "+v";
		}
	},
	methods: {
		paste(html) {
			this.$emit("close");
			this.$emit("paste", html);
		}
	}
};
</script>

<style>
.k-block-importer.k-dialog {
	background: var(--color-slate-800);
	color: var(--color-white);
}
.k-block-importer .k-dialog-body {
	padding: 0;
}
.k-block-importer label {
	display: block;
	padding: var(--spacing-6) var(--spacing-6) 0;
	color: var(--color-gray-400);
}
.k-block-importer label kbd {
	background: rgba(0, 0, 0, 0.5);
	font-family: var(--font-mono);
	letter-spacing: 0.1em;
	padding: 0.25rem;
	border-radius: var(--rounded);
	margin: 0 0.25rem;
}
.k-block-importer label small {
	display: block;
	margin-top: 0.5rem;
	color: var(--color-gray-500);
}

.k-block-importer textarea {
	width: 100%;
	height: 20rem;
	background: none;
	font: inherit;
	color: var(--color-white);
	border: 0;
	padding: var(--spacing-6);
	resize: none;
}
.k-block-importer textarea:focus {
	outline: 0;
}
</style>
