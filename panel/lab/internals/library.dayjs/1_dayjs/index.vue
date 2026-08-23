<template>
	<k-lab-examples>
		<k-box theme="text">
			<k-text>
				<p>
					Kirby ships with <code>dayjs</code> bundled: a minimalist JavaScript
					library that parses, validates, manipulates, and displays dates and
					times.
				</p>

				<p>
					In addition to exposing all the
					<a href="https://day.js.org/">core functionality</a>, Kirby extends
					<code>dayjs</code> with the following plugins:
				</p>

				<ul>
					<li>
						<k-link to="/lab/internals/library.dayjs/2_parse">
							<code>dayjs.parse()</code>
						</k-link>
						reads what a user typed as a <code>dayjs</code> object
					</li>
					<li>
						<k-link to="/lab/internals/library.dayjs/3_pattern">
							<code>dayjs.pattern()</code>
						</k-link>
						reads a display pattern like <code>DD.MM.YYYY</code>, formats and
						inspects datetimes against it
					</li>
					<li>
						<k-link to="/lab/internals/library.dayjs/4_locale">
							<code>dayjs.locale()</code>
						</k-link>
						activates a dayjs locale
					</li>
					<li>
						<k-link to="/lab/internals/library.dayjs/5_round">
							<code>dayjs().round()</code>
						</k-link>
						rounds to the nearest step of a unit, e.g. to the nearest 15 minutes
					</li>
					<li>
						<k-link to="/lab/internals/library.dayjs/6_iso">
							<code>dayjs.iso()</code> and <code>dayjs().toISO()</code>
						</k-link>
						convert between the ISO strings values are stored as and
						<code>dayjs</code> objects
					</li>
					<li>
						<k-link to="/lab/internals/library.dayjs/7_validate">
							<code>dayjs().validate()</code>
						</k-link>
						checks a datetime against a <code>min</code>/<code>max</code>
						boundary
					</li>
				</ul>
			</k-text>
		</k-box>
		<k-lab-example label="dayjs" :code="false">
			<k-code language="javascript">this.$library.dayjs() // now</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-examples .k-text .k-link {
	text-decoration: underline;
}
</style>
