<template>
	<ul class="k-bubbles">
		<li v-for="(bubble, id) in items" :key="id">
			<k-bubble v-bind="bubble" />
		</li>
	</ul>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		bubbles: Array
	},
	computed: {
		items() {
			let bubbles = this.bubbles;

			if (typeof bubbles === "string") {
				bubbles = bubbles.split(",");
			}

			return bubbles.map((bubble) => {
				if (typeof bubble === "string") {
					return { text: bubble };
				}

				return bubble;
			});
		}
	}
};
</script>

<style>
.k-bubbles {
	display: flex;
	gap: 0.25rem;
}
</style>
