<template>
	<k-dialog
		ref="dialog"
		:class="['k-block-importer', $attrs.class]"
		:cancel-button="false"
		:style="$attrs.style"
		:submit-button="false"
		:visible="true"
		size="large"
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
export default {
	inheritAttrs: false,
	emits: ["close", "paste", "submit"],
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
.k-block-importer .k-dialog-body {
	padding: 0;
}
.k-block-importer label {
	display: block;
	padding: var(--spacing-6) var(--spacing-6) 0;
	color: var(--color-text-dimmed);
	line-height: var(--leading-normal);
}
.k-block-importer label small {
	display: block;
	font-size: inherit;
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
