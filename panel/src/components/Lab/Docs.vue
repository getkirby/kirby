<template>
	<div class="k-lab-docs">
		<section v-if="description.length" class="k-lab-docs-section">
			<k-headline class="h3">Description</k-headline>
			<k-box theme="text">
				<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
				<k-text v-html="description" />
			</k-box>
		</section>

		<section v-if="examples.length" class="k-lab-docs-section">
			<k-headline class="h3">Examples</k-headline>
			<k-code
				v-for="(example, index) in examples"
				:key="index"
				language="html"
				>{{ example.content }}</k-code>
		</section>

		<section v-if="props.length" class="k-lab-docs-section">
			<k-headline class="h3">Props</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 10rem">Name</th>
						<th style="width: 10rem">Type</th>
						<th style="width: 10rem">Default</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="prop in props" :key="prop.name">
							<td>
								<k-text>
									<code>{{ prop.name }}</code>
								</k-text>
							</td>
							<td>
								<k-text class="k-lab-docs-types">
									<code
										v-for="type in prop.type?.split('|')"
										:key="type"
										:data-type="type"
									>
										{{ type }}
									</code>
								</k-text>
							</td>
							<td>
								<k-text v-if="prop.default">
									<code>{{ prop.default }}</code>
								</k-text>
							</td>
							<td class="k-lab-docs-description">
								<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
								<k-text v-if="prop.description?.length" v-html="prop.description" />

								<k-box
									v-if="prop.deprecated?.length"
									theme="warning"
									class="k-lab-docs-deprecated"
								>
									Deprecated: {{ prop.deprecated }}
								</k-box>

								<k-text v-if="prop.values?.length">
									<p class="k-lab-docs-values">
										<strong>Values</strong><br />
										<span>
											<code v-for="value in prop.values" :key="value">
												{{ value.replaceAll("`", "") }}
											</code>
										</span>
									</p>
								</k-text>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section v-if="slots.length" class="k-lab-docs-section">
			<k-headline class="h3">Slots</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 10rem">Slot</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="slot in slots" :key="slot.name">
							<td style="width: 12rem">
								<k-text>
									<code>{{ slot.name }}</code>
								</k-text>
							</td>
							<td>
								<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
								<k-text v-html="slot.description" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section v-if="events.length" class="k-lab-docs-section">
			<k-headline class="h3">Events</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 10rem">Event</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="event in events" :key="event.name">
							<td style="width: 12rem">
								<k-text>
									<code>{{ event.name }}</code>
								</k-text>
							</td>
							<td>
								<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
								<k-text v-html="event.description" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section v-if="methods.length" class="k-lab-docs-section">
			<k-headline class="h3">Methods</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 8rem">Method</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="method in methods" :key="method.name">
							<td style="width: 12rem">
								<k-text>
									<code>{{ method.name }}</code>
								</k-text>
							</td>
							<td>
								<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
								<k-text v-html="method.description" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</template>

<script>
export default {
	props: {
		component: String,
		description: String,
		events: {
			default: () => [],
			type: Array
		},
		examples: {
			default: () => [],
			type: Array
		},
		methods: {
			default: () => [],
			type: Array
		},
		slots: {
			default: () => [],
			type: Array
		},
		props: {
			default: () => [],
			type: Array
		}
	}
};
</script>

<style>
.k-lab-docs-section + .k-lab-docs-section {
	margin-top: var(--spacing-12);
}
.k-lab-docs-section .k-headline {
	margin-bottom: var(--spacing-3);
}
.k-lab-docs-section .k-table td {
	padding: 0.375rem var(--table-cell-padding);
	vertical-align: top;
	line-height: 1.5;
	word-break: break-word;
}
.k-lab-docs-types {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}

.k-lab-docs-description :where(.k-text, .k-box) + :where(.k-text, .k-box) {
	margin-top: var(--spacing-3);
}

.k-lab-docs-deprecated {
	--box-height: var(--height-xs);
	font-size: var(--text-xs);
}

.k-lab-docs-values {
	font-size: var(--text-xs);
	border-left: 2px solid var(--color-blue-300);
	padding-inline-start: var(--spacing-2);
}
.k-lab-docs-values span {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}

.k-lab-docs-types code[data-type="boolean"] {
	color: var(--color-purple-800);
	outline-color: var(--color-purple-400);
	background: var(--color-purple-300);
}
.k-lab-docs-types code[data-type="string"] {
	color: var(--color-green-800);
	outline-color: var(--color-green-500);
	background: var(--color-green-300);
}
.k-lab-docs-types code[data-type="number"] {
	color: var(--color-orange-800);
	outline-color: var(--color-orange-500);
	background: var(--color-orange-300);
}
.k-lab-docs-types code[data-type="array"] {
	color: var(--color-aqua-800);
	outline-color: var(--color-aqua-500);
	background: var(--color-aqua-300);
}
.k-lab-docs-types code[data-type="object"] {
	color: var(--color-yellow-800);
	outline-color: var(--color-yellow-500);
	background: var(--color-yellow-300);
}
.k-lab-docs-types code[data-type="func"] {
	color: var(--color-pink-800);
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
</style>
