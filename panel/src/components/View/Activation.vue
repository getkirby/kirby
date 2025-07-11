<template>
	<div v-if="$panel.activation.isOpen" class="k-activation">
		<p>
			<strong>{{ $t(`license.status.${status}.bubble`) }}</strong>
			<template v-if="status === 'missing'">
				<a href="https://getkirby.com/buy" target="_blank">{{
					$t("license.buy")
				}}</a>
				&amp;
				<button type="button" @click="$dialog('registration')">
					{{ $t("license.activate") }}
				</button>
			</template>
		</p>
		<k-button
			class="k-activation-toggle"
			icon="cancel-small"
			@click="$panel.activation.close()"
		/>
	</div>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		status: {
			default: "missing",
			type: String
		}
	}
};
</script>

<style>
.k-activation {
	position: relative;
	display: flex;
	color: var(--dropdown-color-text);
	background: var(--dropdown-color-bg);
	border-radius: var(--dropdown-rounded);
	box-shadow: var(--dropdown-shadow);
	justify-content: space-between;
}
.k-activation p {
	padding-inline-start: var(--spacing-3);
	padding-inline-end: var(--spacing-2);
	padding-block: 0.425rem;
	line-height: 1.25;
}
.k-activation p strong {
	font-weight: var(--font-normal);
	margin-inline-end: var(--spacing-1);
}
.k-activation p :where(button, a) {
	color: var(--color-pink-400);
	text-decoration: underline;
	text-decoration-color: currentColor;
	text-underline-offset: 2px;
	border-radius: var(--rounded-sm);
}

/* Hide Button */
.k-activation-toggle {
	--button-color-text: var(--color-gray-400);
	--button-rounded: 0;
	border-left: 1px solid var(--dropdown-color-hr);
}
.k-activation-toggle:is(:hover, :focus) {
	--button-color-text: var(--color-white);
}
.k-activation-toggle:focus {
	--button-rounded: var(--rounded);
}
</style>
