<template>
	<div class="k-user-info">
		<k-label v-if="label" type="section">{{ label }}</k-label>

		<div class="k-user-info-body">
			<k-image-frame
				v-if="user.avatar"
				:cover="true"
				:src="user.avatar"
				ratio="1/1"
			/>
			<k-icon-frame v-else color="white" back="black" icon="user" />
			{{ user.name ?? user.email ?? user }}
		</div>
	</div>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @unstable
 */
export default {
	props: {
		/**
		 * @since 6.0.0
		 */
		label: String,
		user: [Object, String]
	}
};
</script>

<style>
.k-user-info {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-2);
}
.k-user-info-body {
	display: flex;
	align-items: center;
	font-size: var(--text-sm);
	height: var(--height-lg);
	gap: 0.75rem;
	padding-inline: var(--spacing-2);
	background: var(--item-color-back);
	box-shadow: var(--shadow);
	border-radius: var(--rounded);
}
.k-user-info-body :where(.k-image-frame, .k-icon-frame) {
	width: 1.5rem;
	border-radius: var(--rounded-sm);
}
</style>
