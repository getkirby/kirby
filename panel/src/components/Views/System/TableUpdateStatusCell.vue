<template>
	<div class="k-table-update-status-cell">
		<span
			v-if="typeof value === 'string'"
			class="k-table-update-status-cell-version"
		>
			{{ value }}
		</span>
		<template v-else>
			<k-button
				class="k-table-update-status-cell-button"
				:dropdown="true"
				:icon="value.icon"
				:href="value.url"
				:text="value.currentVersion"
				:theme="value.theme"
				size="xs"
				variant="filled"
				@click.stop="$refs.dropdown.toggle()"
			/>
			<k-dropdown ref="dropdown" align-x="end">
				<dl class="k-plugin-info">
					<dt>{{ $t("plugin") }}</dt>
					<dd>{{ value.pluginName }}</dd>
					<dt>{{ $t("version.current") }}</dt>
					<dd>{{ value.currentVersion }}</dd>
					<dt>{{ $t("version.latest") }}</dt>
					<dd>{{ value.latestVersion }}</dd>
					<dt>{{ $t("system.updateStatus") }}</dt>
					<dd :data-theme="value.theme">{{ value.label }}</dd>
				</dl>
				<template v-if="value.url">
					<hr />
					<k-button icon="open" :link="value.url">
						{{ $t("versionInformation") }}
					</k-button>
				</template>
			</k-dropdown>
		</template>
	</div>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		value: [String, Object]
	}
};
</script>

<style>
.k-table-update-status-cell {
	padding: 0 0.75rem;
	display: flex;
	align-items: center;
	height: 100%;
}

.k-table-update-status-cell-version,
.k-table-update-status-cell-button {
	font-variant-numeric: tabular-nums;
}

.k-plugin-info {
	display: grid;
	column-gap: var(--spacing-3);
	row-gap: 2px;
	padding: var(--button-padding);
}
.k-plugin-info dt {
	color: var(--color-gray-400);
}
.k-plugin-info dd[data-theme] {
	color: var(--theme-color-600);
}

@container (max-width: 30em) {
	.k-plugin-info dd:not(:last-of-type) {
		margin-bottom: var(--spacing-2);
	}
}

@container (min-width: 30em) {
	.k-plugin-info {
		width: 20rem;
		grid-template-columns: 1fr auto;
	}
}
</style>
