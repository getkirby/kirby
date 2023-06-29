<template>
	<div class="k-dialog-body">
		<slot />
	</div>
</template>

<style>
.k-dialog-body {
	padding: var(--dialog-padding) var(--dialog-padding);
}
.k-dialog:has(.k-dialog-footer) .k-dialog-body {
	padding-bottom: 0;
}
</style>
