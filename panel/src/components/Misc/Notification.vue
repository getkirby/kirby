<template>
	<div :data-theme="type" class="k-notification">
		<p>{{ message }}</p>
		<k-button icon="cancel" @click="$emit('close')" />
	</div>
</template>

<script>
export const props = {
	props: {
		message: {
			type: String
		},
		type: {
			type: String
		}
	}
};

export default {
	mixins: [props]
};
</script>

<style>
.k-notification {
	padding: 0.75rem 1.5rem;
	background: var(--color-gray-900);
	width: 100%;
	line-height: 1.25rem;
	color: var(--color-white);
	display: flex;
	flex-shrink: 0;
	align-items: center;
}

.k-notification[data-theme] {
	background: var(--theme-light);
	color: var(--color-black);
}

.k-notification p {
	flex-grow: 1;
	word-wrap: break-word;
	overflow: hidden;
}

.k-notification .k-button {
	display: flex;
	margin-inline-start: 1rem;
}
</style>
