<template>
	<nav :aria-label="label" class="k-breadcrumb">
		<k-collapsible direction="start" element="ol">
			<!-- Responsive fallback -->
			<template #fallback="{ offset, total }">
				<li v-if="offset !== 0">
					<k-button v-bind="button(first)" />
				</li>
				<li>
					<k-button
						:icon="offset === 0 ? (first.icon ?? 'home') : 'dots'"
						size="sm"
						variant="dimmed"
						@click="$refs.dropdown.toggle()"
					/>
					<k-dropdown ref="dropdown" :options="dropdown(offset, total)" />
				</li>
			</template>

			<!-- Crumbs list -->
			<template #default="{ offset }">
				<li v-for="(crumb, index) in visible(offset)" :key="crumb.link">
					<k-button v-bind="button(crumb, index === offset - 1)" />
				</li>
			</template>
		</k-collapsible>
	</nav>
</template>

<script>
/**
 * Displays a breadcrumb trail
 *
 * @example <k-breadcrumb
 * 	:crumbs="[{ link: '/a', label: 'A' }, { link: '/b', label: 'B' }]"
 * />
 */
export default {
	props: {
		/**
		 * Segments of the breadcrumb trail
		 * @value { link, label, icon }
		 */
		crumbs: {
			type: Array,
			default: () => []
		},
		label: {
			type: String,
			default: "Breadcrumb"
		}
	},
	computed: {
		first() {
			return this.crumbs[0];
		}
	},
	methods: {
		button(crumb, isCurrent = false) {
			const label = crumb.text ?? crumb.label;

			return {
				...crumb,
				current: isCurrent ? "page" : false,
				disabled: !crumb.link && !crumb.click && !crumb.dialog && !crumb.drawer,
				icon: crumb.loading ? "loader" : crumb.icon,
				size: "sm",
				text: label,
				title: label,
				variant: "dimmed"
			};
		},
		dropdown(offset, total) {
			const start = offset > 0 ? 1 : 0;

			// Skip first crumb (except when all crumbs are hidden)
			// and get remaining hidden crumbs to show in the dropdown
			return this.crumbs.slice(start, total - offset).map((crumb) => ({
				...crumb,
				text: crumb.text ?? crumb.label,
				icon: "angle-right",
				variant: null // remove dimmed variant in dropdown
			}));
		},
		visible(offset) {
			if (offset > 0) {
				return this.crumbs.slice(-offset);
			}

			return [];
		}
	}
};
</script>

<style>
.k-breadcrumb {
	--breadcrumb-divider: "/";
	padding: 2px;
}

.k-breadcrumb ol {
	display: flex;
	gap: 0.125rem;
	align-items: center;
}
.k-breadcrumb ol li {
	flex-shrink: 0;
	display: flex;
	align-items: center;
}
.k-breadcrumb ol li:not(:last-child)::after {
	content: var(--breadcrumb-divider);
	opacity: 0.175;
}

.k-breadcrumb .k-icon[data-type="loader"] {
	opacity: 0.5;
}

.k-breadcrumb .k-dropdown {
	max-width: 15rem;
}
</style>
