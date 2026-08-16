<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					<code>dayjs.pattern()</code> reads a display pattern like
					<code>DD.MM.YYYY</code> and tells you what it is made of: which units
					it shows, where each of them sits in the string and whether it
					describes a date or a time. It also formats a
					<code>dayjs</code> object in that pattern.
				</p>
			</k-text>
		</k-box>

		<div class="k-lab-example-meta" data-theme="blue">
			<k-text-field
				label="Format pattern"
				:value="format"
				@input="format = $event"
			/>
		</div>

		<k-lab-example label="dayjs.pattern() getters" :code="false">
			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.pattern("{{ format }}")</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label><k-code-token>.units</k-code-token></k-label>
					<k-code language="javascript">{{ pattern.units }}</k-code>
				</k-column>

				<k-column width="1/2">
					<k-label><k-code-token>.type</k-code-token></k-label>
					<k-code>{{ pattern.type }}</k-code>

					<k-label><k-code-token>.source</k-code-token></k-label>
					<k-code>{{ pattern.source }}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.pattern().at()" :code="false">
			<k-text>
				<p>
					The part at a cursor position, or the last one before it. Pass a
					second index to look up a selection instead of a caret and a
					<code>dayjs</code> object to look it up in the rendering.
				</p>
			</k-text>

			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.pattern("{{ format }}").at(start, end, dt): object|undefined</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Cursor position in {{ formatted }}</k-label>
					<k-input
						type="number"
						min="0"
						:max="formatted?.length"
						:value="position"
						@input="position = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<k-label>at({{ position }}, {{ position }}, dt)</k-label>
					<k-code language="javascript">{{ part ?? "undefined" }}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.pattern().format()" :code="false">
			<k-text>
				<p>
					Formats a <code>dayjs</code> object in the pattern. Unlike
					<code>dt.format()</code> it is safe to call with a missing or invalid
					datetime and returns <code>null</code> for those.
				</p>
			</k-text>

			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.pattern("{{ format }}").format(dt): string|null</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>Input</k-label>
					<k-box theme="code">{{ iso }}</k-box>
				</k-column>
				<k-column width="1/2">
					<k-label>Result</k-label>
					<k-box theme="code">{{ formatted ?? "null" }}</k-box>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="dayjs.pattern().parts()" :code="false">
			<k-text>
				<p>
					What the pattern is made of. Without a <code>dayjs</code> object the
					parts are positioned in the pattern itself, with one in the string
					that datetime renders into.
				</p>
			</k-text>

			<!-- prettier-ignore -->
			<k-code language="javascript">this.$library.dayjs.pattern("{{ format }}").parts(dt): array</k-code>

			<k-grid variant="fields">
				<k-column width="1/2">
					<k-label>parts()</k-label>
					<k-code language="javascript">{{ pattern.parts() }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-label>parts(dt) in "{{ formatted }}"</k-label>
					<k-code language="javascript">{{ pattern.parts(dt) }}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			dt: this.$library.dayjs(),
			format: "DD.MM.YYYY",
			position: 0
		};
	},
	computed: {
		formatted() {
			return this.pattern.format(this.dt);
		},
		iso() {
			return this.dt.toISO();
		},
		part() {
			return this.pattern.at(Number(this.position), undefined, this.dt);
		},
		pattern() {
			return this.$library.dayjs.pattern(this.format);
		}
	}
};
</script>

<style>
.k-lab-example .k-code {
	margin-bottom: var(--spacing-6);
}
.k-lab-example .k-text {
	margin-bottom: var(--spacing-6);
}
</style>
