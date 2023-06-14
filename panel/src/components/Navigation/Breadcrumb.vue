<template>
	<nav :aria-label="label" class="k-breadcrumb">
		<k-dropdown class="k-breadcrumb-dropdown">
			<k-button icon="road-sign" @click="$refs.dropdown.toggle()" />
			<k-dropdown-content ref="dropdown" :options="dropdown" theme="light" />
		</k-dropdown>

		<ol>
			<li v-for="(crumb, index) in segments" :key="index">
				<k-button
					:icon="crumb.loading ? 'loader' : crumb.icon"
					:link="crumb.link"
					:text="crumb.text || crumb.label"
					:title="crumb.text || crumb.label"
					:current="isLast(index - 1) ? 'page' : false"
					variant="dimmed"
					class="k-breadcrumb-link"
				/>
			</li>
		</ol>
	</nav>
</template>

<script>
export default {
	props: {
		crumbs: {
			type: Array,
			default: () => []
		},
		label: {
			type: String,
			default: "Breadcrumb"
		},
		view: Object
	},
	computed: {
		dropdown() {
			return this.segments.map((link) => ({
				...link,
				text: link.label,
				icon: "angle-right"
			}));
		},
		segments() {
			return [
				{
					link: this.view.link,
					label: this.view.breadcrumbLabel,
					icon: this.view.icon,
					loading: this.$panel.isLoading
				},
				...this.crumbs
			];
		}
	},
	methods: {
		isLast(index) {
			return this.crumbs.length - 1 === index;
		}
	}
};
</script>

<style>
.k-breadcrumb {
	--breadcrumb-divider: "/";
}

.k-breadcrumb ol {
	display: none;
	gap: 0.125rem;
	align-items: center;
}

.k-breadcrumb li {
	display: flex;
	align-items: center;
	flex-shrink: 3;
}
.k-breadcrumb li:not(:last-child)::after {
	content: var(--breadcrumb-divider);
	opacity: 0.175;
	flex-shrink: 0;
}
.k-breadcrumb li:last-child {
	flex-shrink: 1;
}
.k-breadcrumb li:not(:first-child):not(:last-child) {
	max-width: 15vw;
	max-width: 15cqw;
}

.k-breadcrumb .k-icon[data-type="loader"] {
	opacity: 0.5;
}

.k-breadcrumb-dropdown {
	height: 2.5rem;
	width: 2.5rem;
	display: grid;
	place-content: center;
}

@media screen and (min-width: 40em) {
	.k-breadcrumb ol {
		display: flex;
	}
	.k-breadcrumb-dropdown {
		display: none;
	}
}
</style>
