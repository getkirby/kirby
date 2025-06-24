<template>
	<div class="k-file-preview-details">
		<dl>
			<div v-for="detail in details" :key="detail.title">
				<dt>{{ detail.title }}</dt>
				<dd>
					<k-link
						v-if="detail.link"
						:to="detail.link"
						tabindex="-1"
						target="_blank"
					>
						{{ detail.text }}
					</k-link>
					<template v-else>
						{{ detail.text }}
					</template>
				</dd>
			</div>

			<slot />
		</dl>
	</div>
</template>

<script>
/**
 * @since 5.0.0
 */
export default {
	props: {
		details: {
			default: () => [],
			type: Array
		}
	}
};
</script>

<style>
.k-file-preview-details {
	display: grid;
}
.k-file-preview-details dl {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
	grid-gap: var(--spacing-6) var(--spacing-12);
	align-self: center;
	padding: var(--spacing-6);
	line-height: 1.5em;
}
.k-file-preview-details dt {
	font-size: var(--text-sm);
	font-weight: var(--font-semi);
	color: var(--color-gray-500);
	margin-bottom: var(--spacing-1);
}
.k-file-preview-details :where(dd, a) {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	color: var(--file-preview-color-text);
	font-size: var(--text-sm);
}
</style>
