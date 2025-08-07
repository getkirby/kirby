<template>
	<section v-if="props.length" class="k-lab-docs-section">
		<k-headline class="h3">Props</k-headline>
		<div class="k-table">
			<table>
				<thead>
					<tr>
						<th style="width: 10rem">Name</th>
						<th style="width: 10rem">Type</th>
						<th style="width: 10rem">Default</th>
						<th>Description</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="prop in props" :key="prop.name">
						<td>
							<k-text>
								<code>{{ prop.name }}</code>
								<abbr v-if="prop.required" class="k-lab-docs-required">✶</abbr>
								<div v-if="prop.since?.length" class="k-lab-docs-since">
									since {{ prop.since }}
								</div>
							</k-text>
						</td>
						<td>
							<k-lab-docs-types :types="prop.type?.split('|')" />
						</td>
						<td>
							<k-text v-if="prop.default">
								<code>{{ prop.default }}</code>
							</k-text>
						</td>
						<td class="k-lab-docs-description">
							<k-lab-docs-warning title="Deprecated" :text="prop.deprecated" />

							<k-text
								v-if="prop.description?.length"
								:html="prop.description"
							/>

							<k-text
								v-if="
									prop.value?.length ||
									prop.values?.length ||
									prop.example?.length
								"
								class="k-lab-docs-prop-values"
							>
								<dl v-if="prop.value?.length">
									<dt>Value</dt>
									<dd>
										<code>{{ prop.value }}</code>
									</dd>
								</dl>

								<dl v-if="prop.values?.length">
									<dt>Values</dt>
									<dd>
										<code v-for="value in prop.values" :key="value">
											{{ value.replaceAll("`", "") }}
										</code>
									</dd>
								</dl>

								<dl v-if="prop.example?.length">
									<dt>Example</dt>
									<dd>
										<code>{{ prop.example }}</code>
									</dd>
								</dl>
							</k-text>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</section>
</template>

<script>
export const props = {
	props: {
		props: {
			default: () => [],
			type: Array
		}
	}
};

export default {
	mixins: [props]
};
</script>

<style>
.k-lab-docs-prop-values {
	font-size: var(--text-xs);
	border-left: 2px solid var(--color-blue-300);
	padding-inline-start: var(--spacing-2);
}
.k-lab-docs-prop-values dl {
	font-weight: var(--font-bold);
}
.k-lab-docs-prop-values dl + dl {
	margin-top: var(--spacing-2);
}
.k-lab-docs-prop-values dd {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}
</style>
