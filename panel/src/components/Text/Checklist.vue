<template>
	<ul class="k-checklist k-stack" :data-theme="theme">
		<slot />
	</ul>
</template>

<script>
export default {
	props: {
		theme: {
			type: String,
			default: "positive"
		}
	}
};
</script>

<style>
.k-checklist {
	--checklist-marker: "✓";
	list-style: none;
	gap: var(--spacing-2);
	line-height: var(--text-line-height);
}
.k-checklist li {
	display: flex;
	gap: var(--spacing-2);
}
.k-checklist li::before {
	width: 1.25em;
	flex-shrink: 0;
	text-align: center;
	content: var(--checklist-marker);
	color: var(--theme-color-text);
}
.k-checklist[data-theme="notice"],
.k-checklist li[data-theme="notice"] {
	--checklist-marker: "⚠";
}
.k-checklist[data-theme="negative"],
.k-checklist li[data-theme="negative"] {
	--checklist-marker: "×";
}
</style>
