<template>
	<k-lab-examples>
		<k-lab-example label="On white" :code="false">
			<ul class="k-lab-icons">
				<li v-for="icon in icons" :key="icon">
					<k-icon :type="icon" /> {{ icon }}
				</li>
			</ul>
		</k-lab-example>
		<k-lab-example label="On black" :code="false">
			<ul class="k-lab-icons" data-variant="dark">
				<li v-for="icon in icons" :key="icon">
					<k-icon :type="icon" /> {{ icon }}
				</li>
			</ul>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		icons: Array
	}
};
</script>

<style>
.k-lab-icons {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr));
	gap: var(--spacing-2);
}
.k-lab-icons li {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	padding: var(--spacing-2);
	color: var(--color-black);
	background: var(--color-white);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
.k-lab-icons[data-variant="dark"] li {
	color: var(--color-white);
	background: var(--color-black);
}
</style>
