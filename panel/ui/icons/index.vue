<template>
	<k-ui-examples>
		<k-ui-example label="On white">
			<ul class="k-ui-icons">
				<li v-for="icon in icons" :key="icon">
					<k-icon :type="icon" /> {{ icon }}
				</li>
			</ul>
		</k-ui-example>
		<k-ui-example label="On black">
			<ul class="k-ui-icons" data-variant="dark">
				<li v-for="icon in icons" :key="icon">
					<k-icon :type="icon" /> {{ icon }}
				</li>
			</ul>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		icons: Array,
	},
};
</script>

<style>
.k-ui-icons {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr));
	gap: var(--spacing-2);
}
.k-ui-icons li {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	padding: var(--spacing-2);
	background: var(--color-white);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
.k-ui-icons[data-variant="dark"] li {
	color: var(--color-white);
	background: var(--color-black);
}
</style>
