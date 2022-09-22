<template>
	<div class="k-table-update-status-cell">
		<template v-if="typeof this.value === 'string'">
			{{ value }}
		</template>
		<k-dropdown v-else :data-theme="value.theme">
			<k-button
				class="k-table-update-status-cell-button"
				:icon="value.icon"
				:href="value.url"
				@click.stop="$refs.dropdown.toggle()"
			>
				{{ value.currentVersion }}
			</k-button>
			<k-dropdown-content ref="dropdown" align="right">
				<dl class="k-plugin-info">
					<div>
						<dt>Plugin</dt>
						<dd>{{ value.name }}</dd>
					</div>
					<div>
						<dt>Current version</dt>
						<dd>{{ value.currentVersion }}</dd>
					</div>
					<div>
						<dt>Latest version</dt>
						<dd>{{ value.latestVersion }}</dd>
					</div>
					<div>
						<dt>Status</dt>
						<dd :data-theme="value.theme">{{ value.label }}</dd>
					</div>
				</dl>

				<k-dropdown-item
					:disabled="value.url === null"
					icon="open"
					:link="value.url"
				>
					Version information
				</k-dropdown-item>
			</k-dropdown-content>
		</k-dropdown>
	</div>
</template>

<script>
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

.k-table-update-status-cell-button {
	display: inline-flex;
	padding: 0.25rem 0.325rem;
	padding-right: 1.5rem;
	border-radius: var(--rounded);
	line-height: 1;
	align-items: center;
	background: var(--color-gray-200);
}
.k-table-update-status-cell-button .k-button-text::after {
	position: absolute;
	top: 50%;
	right: 0.5rem;
	margin-top: -2px;
	content: "";
	border-top: 4px solid black;
	border-left: 4px solid transparent;
	border-right: 4px solid transparent;
}
.k-table-update-status-cell-button .k-icon {
	color: var(--theme);
}

.k-plugin-info {
	padding: 1rem;
	border-bottom: 1px solid var(--color-gray-700);
	margin-bottom: 0.5rem;
	width: 20rem;
}
.k-plugin-info div {
	display: flex;
}
.k-plugin-info div + div {
	margin-top: 0.5rem;
}
.k-plugin-info dt {
	color: var(--color-gray-400);
	margin-right: 0.5rem;
}
.k-plugin-info dd[data-theme] {
	color: var(--theme-light);
}
</style>
