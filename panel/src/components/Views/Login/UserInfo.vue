<template>
	<div class="k-user-info">
		<k-image-icon-frame
			:cover="true"
			:src="user.avatar?.url"
			back="black"
			color="white"
			icon="user"
			ratio="1/1"
		/>
		{{ user.name ?? user.email ?? user }}
	</div>
</template>

<script>
/**
 * @internal
 * @since 4.0.0
 */
export default {
	props: {
		user: [Object, String]
	}
};
</script>

<style>
.k-user-info {
	display: flex;
	align-items: center;
	font-size: var(--text-sm);
	height: var(--height-lg);
	gap: 0.75rem;
	padding-inline: var(--spacing-2);
	background: var(--color-white);
	box-shadow: var(--shadow);
}
.k-user-info :where(.k-image-frame, .k-icon-frame) {
	width: 1.5rem;
	border-radius: var(--rounded-sm);
}
</style>
