<template>
	<div class="k-preview-browser">
		<header class="k-preview-browser-header">
			<k-headline>
				<k-icon type="git-branch" />
				{{ label }}
			</k-headline>
			<k-button-group>
				<template v-if="versionId === 'changes'">
					<p v-if="hasDiff === false" class="k-preview-browser-message">
						{{ $t("lock.unsaved.empty") }}
					</p>
					<k-form-controls
						v-else
						:editor="editor"
						:has-diff="hasDiff"
						:is-locked="isLocked"
						:modified="modified"
						size="xs"
						@discard="$emit('discard', $event)"
						@submit="$emit('submit', $event)"
					/>
				</template>
				<k-button :link="src" icon="open" size="xs" target="_blank" />
			</k-button-group>
		</header>

		<iframe ref="browser" :src="srcWithPreviewParam" />
	</div>
</template>

<script>
import { props } from "@/components/Forms/FormControls.vue";

export default {
	mixins: [props],
	props: {
		label: String,
		src: String,
		versionId: String
	},
	emits: ["discard", "submit"],
	computed: {
		srcWithPreviewParam() {
			const uri = new URL(this.src, this.$panel.urls.site);
			uri.searchParams.append("_preview", true);
			return uri.toString();
		}
	},
	mounted() {
		this.$events.on("content.discard", this.reload);
		this.$events.on("content.publish", this.reload);
	},
	destroyed() {
		this.$events.off("content.discard", this.reload);
		this.$events.off("content.publish", this.reload);
	},
	methods: {
		reload() {
			this.$refs.browser.contentWindow.location.reload();
		}
	}
};
</script>

<style>
:root {
	--preview-browser-color-background: var(--input-color-back);
}
.k-preview-browser {
	container-type: inline-size;
	display: flex;
	flex-direction: column;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
	background: var(--preview-browser-color-background);
	overflow: hidden;
	border: 1px solid var(--color-border);
}
.k-preview-browser-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	background: var(--preview-browser-color-background);
	border-bottom: 1px solid var(--color-border);
	color: var(--color-text);
	padding-inline: var(--spacing-2);
	height: var(--input-height);
}
.k-preview-browser header .k-headline {
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
	font-weight: var(--font-normal);
	font-size: var(--text-xs);
	padding-inline: var(--spacing-1);
}
.k-preview-browser-header .k-form-controls-button {
	font-size: var(--text-xs);
	--button-rounded: 3px;
	--icon-size: 1rem;
}
.k-preview-browser-message {
	font-size: var(--text-xs);
	display: flex;
	margin-inline-end: var(--spacing-1);
	color: var(--color-text-dimmed);
}
.k-preview-browser iframe {
	width: 100%;
	flex-grow: 1;
}
@container (max-width: 30rem) {
	.k-preview-browser-message {
		display: none;
	}
}
</style>
