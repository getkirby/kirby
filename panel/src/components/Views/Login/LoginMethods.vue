<template>
	<template v-if="alternativeMethods.length > 0">
		<p class="k-login-or">
			<span>{{ $t("login.or") }}</span>
		</p>

		<k-stack>
			<k-button
				v-for="method in alternativeMethods"
				:key="method.type"
				:text="method.label"
				:icon="method.icon"
				variant="filled"
				size="lg"
				@click="$emit('change', method)"
			/>
		</k-stack>
	</template>
</template>

<script>
export default {
	props: {
		methods: {
			type: Array,
			required: true
		}
	},
	emits: ["change"],
	computed: {
		alternativeMethods() {
			return this.methods.filter((method) => !method.active);
		}
	}
};
</script>
