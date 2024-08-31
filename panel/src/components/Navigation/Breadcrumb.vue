<template>
	<nav :aria-label="label" class="k-breadcrumb">
		<div v-if="crumbs.length > 1" class="k-breadcrumb-dropdown">
			<k-button icon="home" @click="$refs.dropdown.toggle()" />
			<k-dropdown-content ref="dropdown" :options="dropdown" />
		</div>

		<ol>
			<li v-for="(crumb, index) in crumbs" :key="index">
				<k-button
					:icon="crumb.loading ? 'loader' : crumb.icon"
					:link="crumb.link"
					:disabled="!crumb.link"
					:text="crumb.text ?? crumb.label"
					:title="crumb.text ?? crumb.label"
					:current="index === crumbs.length - 1 ? 'page' : false"
					variant="dimmed"
					size="sm"
					class="k-breadcrumb-link"
				/>
			</li>
		</ol>
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
		dropdown() {
			return this.crumbs.map((link) => ({
				...link,
				text: link.label,
				icon: "angle-right"
			}));
		}
	}
};
</script>

<style>
.k-breadcrumb {
	--breadcrumb-divider: "/";
	overflow-x: clip;
	padding: 2px;
}

.k-breadcrumb ol {
	display: none;
	gap: 0.125rem;
	align-items: center;
}
.k-breadcrumb ol li {
	display: flex;
	align-items: center;
	min-width: 0;
	transition: flex-shrink 0.1s;
}
.k-breadcrumb ol li:has(.k-icon) {
	/*
	 * without a useful min-width, the item will vanish completely on hover of a very long other item.
	 * 2.25rem helps to keep at least the icon visible for items with icons.
	 */
	min-width: 2.25rem;
}
.k-breadcrumb ol li:not(:last-child)::after {
	content: var(--breadcrumb-divider);
	opacity: 0.175;
	flex-shrink: 0;
}

.k-breadcrumb .k-icon[data-type="loader"] {
	opacity: 0.5;
}
.k-breadcrumb ol li:is(:hover, :focus-within) {
	flex-shrink: 0;
}
.k-button.k-breadcrumb-link {
	flex-shrink: 1;
	min-width: 0;
	justify-content: flex-start;
}

.k-breadcrumb-dropdown {
	display: grid;
}
.k-breadcrumb-dropdown .k-dropdown-content {
	width: 15rem;
}

@container (min-width: 40em) {
	.k-breadcrumb ol {
		display: flex;
	}
	.k-breadcrumb-dropdown {
		display: none;
	}
}
</style>
