<template>
	<component :is="tag" class="k-headline" @click="$emit('click', $event)">
		<k-link v-if="link" :to="link">
			<!-- @slot Content/text of the headline -->
			<slot />
		</k-link>
		<slot v-else />
	</component>
</template>

<script>
/**
 * Use to display consistently sized headlines
 *
 * @example <k-headline>This is a nice headline</k-headline>
 */
export default {
	props: {
		/**
		 * Sets a link for the headline.
		 * The link can be absolute or relative.
		 */
		link: String,
		/**
		 * HTML tag for the headline element
		 */
		tag: {
			type: String,
			default: "h2"
		}
	},
	emits: ["click"]
};
</script>

<style>
:root {
	--text-h1: 2em;
	--text-h2: 1.75em;
	--text-h3: 1.5em;
	--text-h4: 1.25em;
	--text-h5: 1.125em;
	--text-h6: 1em;

	--font-h1: var(--font-semi);
	--font-h2: var(--font-semi);
	--font-h3: var(--font-semi);
	--font-h4: var(--font-semi);
	--font-h5: var(--font-semi);
	--font-h6: var(--font-semi);

	--leading-h1: 1.125;
	--leading-h2: 1.125;
	--leading-h3: 1.25;
	--leading-h4: 1.375;
	--leading-h5: 1.5;
	--leading-h6: 1.5;
}

.k-headline {
	line-height: 1.5em;
	font-weight: var(--font-bold);
}

.h1,
.k-text h1,
.k-headline[data-size="huge"] {
	color: var(--color-h1, var(--color-h));
	font-family: var(--font-family-h1);
	font-size: var(--text-h1);
	font-weight: var(--font-h1);
	line-height: var(--leading-h1);
}
.h2,
.k-text h2,
.k-headline[data-size="large"] {
	color: var(--color-h2, var(--color-h));
	font-family: var(--font-family-h2);
	font-size: var(--text-h2);
	font-weight: var(--font-h2);
	line-height: var(--leading-h2);
}
.h3,
.k-text h3 {
	color: var(--color-h3, var(--color-h));
	font-family: var(--font-family-h3);
	font-size: var(--text-h3);
	font-weight: var(--font-h3);
	line-height: var(--leading-h3);
}
.h4,
.k-text h4,
.k-headline[data-size="small"] {
	color: var(--color-h4, var(--color-h));
	font-family: var(--font-family-h4);
	font-size: var(--text-h4);
	font-weight: var(--font-h4);
	line-height: var(--leading-h4);
}
.h5,
.k-text h5 {
	color: var(--color-h5, var(--color-h));
	font-family: var(--font-family-h5);
	font-size: var(--text-h5);
	font-weight: var(--font-h5);
	line-height: var(--leading-h5);
}
.h6,
.k-text h6 {
	color: var(--color-h6, var(--color-h));
	font-family: var(--font-family-h6);
	font-size: var(--text-h6);
	font-weight: var(--font-h6);
	line-height: var(--leading-h6);
}

.k-text > * + h6 {
	margin-block-start: calc(var(--text-line-height) * 1.5em);
}
</style>
