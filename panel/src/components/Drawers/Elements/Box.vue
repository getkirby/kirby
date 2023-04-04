<template>
	<form
		:data-id="id"
		class="k-drawer"
		@submit.prevent="$emit('submit', $event)"
	>
		<slot />
	</form>
</template>

<script>
export default {
	props: {
		id: String
	}
};
</script>
