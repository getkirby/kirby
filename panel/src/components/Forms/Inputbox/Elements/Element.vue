<template>
	<span class="k-inputbox-element">
		<slot />
	</span>
</template>

<style>
.k-inputbox-element {
	flex-grow: 1;
}
.k-inputbox-element
	:where(
		input:not(
				[type="button"],
				[type="checkbox"],
				[type="file"],
				[type="image"],
				[type="radio"],
				[type="reset"],
				[type="submit"]
			),
		textarea,
		select
	):focus {
	outline: 0;
}
</style>
