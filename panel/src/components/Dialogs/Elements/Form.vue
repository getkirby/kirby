<template>
	<form class="k-dialog-form" method="dialog" @submit.prevent="$emit('submit')">
		<slot />
	</form>
</template>
