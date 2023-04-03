<template>
	<form class="k-drawer-form" method="dialog" @submit.prevent="$emit('submit')">
		<slot />
	</form>
</template>
