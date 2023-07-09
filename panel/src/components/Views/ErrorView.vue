<template>
	<component :is="`k-${layout}`" class="k-error-view">
		<div class="k-error-view-content">
			<k-text>
				<p>
					<k-icon class="k-error-view-icon" type="alert" />
				</p>
				<slot>
					<p>
						{{ error }}
					</p>
				</slot>
			</k-text>
		</div>
	</component>
</template>

<script>
export default {
	props: {
		error: String,
		layout: String
	}
};
</script>

<style>
.k-error-view {
	display: grid;
	place-items: center;
}
.k-error-view-content {
	line-height: 1.5em;
	max-width: 25rem;
	text-align: center;
}
.k-error-view-icon {
	color: var(--color-red-700);
	display: inline-block;
}
.k-error-view-content p:not(:last-child) {
	margin-bottom: 0.75rem;
}
</style>
