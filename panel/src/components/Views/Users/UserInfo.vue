<template>
	<div class="k-user-info">
		<k-image-frame
			v-if="user.avatar"
			:cover="true"
			:src="user.avatar.url"
			ratio="1/1"
		/>
		<k-icon-frame v-else color="white" back="black" icon="user" />
		{{ user.name ?? user.email ?? user }}
	</div>
</template>

<script>
/**
 * @since 4.0.0
 * @internal
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
	background: var(--item-color-back);
	box-shadow: var(--shadow);
}
.k-user-info :where(.k-image-frame, .k-icon-frame) {
	width: 1.5rem;
	border-radius: var(--rounded-sm);
}
</style>
