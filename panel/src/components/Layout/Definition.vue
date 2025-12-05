<template>
	<div>
		<dt>{{ term }}</dt>
		<dd><slot /></dd>
	</div>
</template>

<script>
export default {
	props: {
		term: String
	}
};
</script>
