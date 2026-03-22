<template>
	<template v-if="alternativeChallenges.length > 0">
		<p class="k-login-or">
			<span>{{ $t("login.or") }}</span>
		</p>

		<k-stack>
			<k-button
				v-for="challenge in alternativeChallenges"
				:key="challenge.type"
				:text="challenge.label"
				:icon="challenge.icon"
				variant="filled"
				size="lg"
				@click="$emit('change', challenge)"
			/>
		</k-stack>
	</template>
</template>

<script>
export default {
	props: {
		challenges: {
			type: Array,
			required: true
		}
	},
	emits: ["change"],
	computed: {
		alternativeChallenges() {
			return this.challenges.filter((challenge) => !challenge.active);
		}
	}
};
</script>
