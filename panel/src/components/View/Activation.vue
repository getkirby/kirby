<template>
	<aside v-if="!$panel.license" :data-closed="!isOpen" class="k-activation">
		<span class="k-text">Ready to launch?</span>

		<k-button
			text="Activate now"
			dialog="registration"
			icon="key"
			theme="positive"
			variant="filled"
		/>
		<k-button icon="cancel-small" class="k-activation-toggle" @click="close" />
	</aside>
</template>

<script>
export default {
	data() {
		return {
			isOpen: sessionStorage.getItem("kirby$activation$card") !== "true"
		};
	},
	methods: {
		close() {
			this.isOpen = false;
			sessionStorage.setItem("kirby$activation$card", "true");
		}
	}
};
</script>

<style>
.k-activation {
	--button-width: 100%;
	position: relative;
	display: flex;
	flex-direction: column;
	gap: var(--spacing-3);

	padding: var(--spacing-6) var(--spacing-3) var(--spacing-3);
	background: var(--color-white);
	border-radius: var(--rounded-lg);
	text-align: center;
	box-shadow: var(--shadow-lg);
}

.k-activation-toggle {
	--button-align: end;
	position: absolute;
	top: 0;
	inset-inline-end: var(--spacing-2);
}

.k-activation .k-icon-frame {
	--icon-size: 1.5rem;
	--icon-color: var(--color-blue-600);
}

.k-panel[data-menu="false"] .k-activation {
	position: fixed;
	width: var(--menu-width-open);
	inset-inline-end: var(--spacing-4);
	bottom: var(--spacing-4);
	border: 1px solid var(--color-gray-300);
}
.k-panel[data-menu="false"] .k-activation[data-closed="true"] {
	display: none;
}
.k-panel[data-menu="true"] .k-activation {
	padding-top: var(--spacing-3);
}
.k-panel[data-menu="true"] .k-activation-toggle {
	display: none;
}
</style>
