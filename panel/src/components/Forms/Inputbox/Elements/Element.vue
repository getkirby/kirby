<template>
	<span class="k-inputbox-element">
		<slot />
	</span>
</template>

<style>
.k-inputbox-element {
	display: flex;
	flex-grow: 1;
}
.k-inputbox[data-variant="multiline"] .k-inputbox-element {
	display: block;
}
.k-inputbox-element
	:where(
		input[type="email"],
		input[type="number"],
		input[type="password"],
		input[type="tel"],
		input[type="text"],
		input[type="url"],
		textarea,
		select,
		[contenteditable]
	):focus {
	outline: 0;
}
</style>
