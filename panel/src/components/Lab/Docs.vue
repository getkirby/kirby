<template>
	<div class="k-ui-docs">
		<section v-if="info.description" class="k-ui-docs-section">
			<k-headline class="h3">Description</k-headline>
			<k-box theme="text">
				<k-text v-html="md(info.description)" />
			</k-box>
		</section>

		<section v-if="info.tags.examples?.length" class="k-ui-docs-section">
			<k-headline class="h3">Examples</k-headline>
			<k-ui-code
				v-for="(example, index) in info.tags.examples"
				:key="index"
				language="html"
				>{{ example.content }}</k-ui-code
			>
		</section>

		<section class="k-ui-docs-section">
			<k-headline class="h3">Props</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 8rem">Name</th>
						<th style="width: 12rem">Type</th>
						<th style="width: 10rem">Default</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="prop in info.props" :key="prop.name">
							<td>
								<k-text>
									<code>{{ prop.name }}</code>
								</k-text>
							</td>
							<td>
								<k-text class="k-ui-docs-types">
									<code
										v-for="type in prop.type.name.split('|')"
										:key="type"
										:data-type="type"
									>
										{{ type }}
									</code>
								</k-text>
							</td>
							<td>
								<code v-if="prop.defaultValue">{{
									prop.defaultValue?.value
								}}</code>
							</td>
							<td>
								<k-text v-html="md(prop.description)" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section v-if="info.slots?.length" class="k-ui-docs-section">
			<k-headline class="h3">Slots</k-headline>
			<div class="k-table">
				<table>
					<thead>
						<th style="width: 8rem">Slot</th>
						<th>Description</th>
					</thead>
					<tbody>
						<tr v-for="slot in info.slots" :key="slot.name">
							<td style="width: 12rem">
								<k-text>
									<code>{{ slot.name }}</code>
								</k-text>
							</td>
							<td>
								<k-text v-html="md(slot.description)" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</template>

<script>
import { marked } from "marked";
import ui from "/dist/ui.json";

export default {
	props: {
		component: {
			type: String
		}
	},
	computed: {
		example() {
			return `<${this.component} />`;
		},
		info() {
			return ui.find((doc) => {
				const componentName =
					"k-" + this.$helper.string.camelToKebab(doc.displayName);
				return componentName === this.component;
			});
		}
	},
	methods: {
		md(text) {
			return marked.parse(text ?? "");
		}
	}
};
</script>

<style>
.k-ui-docs-section + .k-ui-docs-section {
	margin-top: var(--spacing-12);
}
.k-ui-docs-section .k-headline {
	margin-bottom: var(--spacing-3);
}
.k-ui-docs-section .k-table td {
	padding: 0.375rem var(--table-cell-padding);
	vertical-align: top;
	line-height: 1.5;
}
.k-ui-docs-types {
	display: inline-flex;
	gap: 0.25rem;
}

.k-ui-docs-types code[data-type="boolean"] {
	color: var(--color-purple-800);
	outline-color: var(--color-purple-400);
	background: var(--color-purple-300);
}
.k-ui-docs-types code[data-type="string"] {
	color: var(--color-green-800);
	outline-color: var(--color-green-500);
	background: var(--color-green-300);
}
.k-ui-docs-types code[data-type="number"] {
	color: var(--color-orange-800);
	outline-color: var(--color-orange-500);
	background: var(--color-orange-300);
}
.k-ui-docs-types code[data-type="array"] {
	color: var(--color-aqua-800);
	outline-color: var(--color-aqua-500);
	background: var(--color-aqua-300);
}
.k-ui-docs-types code[data-type="object"] {
	color: var(--color-yellow-800);
	outline-color: var(--color-yellow-500);
	background: var(--color-yellow-300);
}
.k-ui-docs-types code[data-type="func"] {
	color: var(--color-pink-800);
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
</style>
