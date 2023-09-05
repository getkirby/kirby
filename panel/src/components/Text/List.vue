<style>
:root {
	--link-color: var(--color-blue-800);
	--link-underline-offset: 2px;
}

.k-text :where(.k-link, a) {
	color: var(--link-color);
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
	border-radius: var(--rounded-xs);
	outline-offset: 2px;
}
</style>
