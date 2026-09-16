<template>
	<k-lab-examples class="k-scrollable-examples">
		<k-lab-example label="Default">
			<k-text>
				<p>
					When the content is taller than the container, both edges fade to hint
					at hidden content. Scroll to see the fade disappear at the top or
					bottom edge. When the content fits, no fade is shown.
				</p>
			</k-text>
			<!-- @code -->
			<div class="k-scrollable-frame">
				<k-scrollable class="k-scrollable-demo">
					<k-box v-for="n in 24" :key="n" theme="purple" :text="`Item ${n}`" />
				</k-scrollable>
			</div>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="fade: end">
			<k-text>
				<p>
					Fades only the bottom edge. Use this when the top holds sticky content
					(toolbars, table headers) that must not be faded.
				</p>
			</k-text>
			<!-- @code -->
			<div class="k-scrollable-frame">
				<k-scrollable class="k-scrollable-demo" fade="end">
					<k-box v-for="n in 24" :key="n" theme="purple" :text="`Item ${n}`" />
				</k-scrollable>
			</div>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="fade: start">
			<!-- @code -->
			<div class="k-scrollable-frame">
				<k-scrollable class="k-scrollable-demo" fade="start">
					<k-box v-for="n in 24" :key="n" theme="purple" :text="`Item ${n}`" />
				</k-scrollable>
			</div>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="fade: false">
			<!-- @code -->
			<div class="k-scrollable-frame">
				<k-scrollable class="k-scrollable-demo" :fade="false">
					<k-box v-for="n in 24" :key="n" theme="purple" :text="`Item ${n}`" />
				</k-scrollable>
			</div>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="axis: inline">
			<k-text>
				<p>Fades the horizontal scroll edges.</p>
			</k-text>
			<!-- @code -->
			<div class="k-scrollable-frame k-scrollable-frame-inline">
				<k-scrollable
					class="k-scrollable-demo k-scrollable-demo-inline"
					axis="inline"
				>
					<k-box v-for="n in 20" :key="n" theme="purple" :text="`Col ${n}`" />
				</k-scrollable>
			</div>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="In a dialog">
			<k-text>
				<p>
					The dialog body is a <code>k-scrollable</code>. When the content is
					taller than the viewport, the body stays within the frame, scrolls,
					and fades both edges.
				</p>
			</k-text>
			<!-- @code -->
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.dialog.open({
						component: 'k-text-dialog',
						props: {
							size: 'tiny',
							text: text
						}
					})
				"
			>
				Open long dialog
			</k-button>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="In a drawer">
			<k-text>
				<p>
					The drawer body uses <code>fade="end"</code>, so only the bottom edge
					fades, keeping sticky toolbars and table headers at the top sharp.
				</p>
			</k-text>
			<!-- @code -->
			<k-button
				icon="open"
				variant="filled"
				@click="
					$panel.drawer.open({
						component: 'k-text-drawer',
						props: {
							size: 'tiny',
							title: 'Long drawer',
							text: text
						}
					})
				"
			>
				Open long drawer
			</k-button>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		text() {
			return Array.from(
				{ length: 20 },
				(_, index) =>
					`<p>Paragraph ${index + 1} — Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>`
			).join("");
		}
	}
};
</script>

<style>
.k-scrollable-examples .k-text {
	margin-bottom: var(--spacing-3);
	max-width: 40rem;
}
.k-scrollable-frame {
	height: 12rem;
	border: 1px solid var(--color-border);
	border-radius: var(--rounded);
	background: var(--color-white);
	overflow: clip;
}
.k-scrollable-frame-inline {
	height: auto;
}
.k-scrollable-demo {
	display: grid;
	gap: var(--spacing-2);
	padding: var(--spacing-3);
	height: 100%;
}
.k-scrollable-demo-inline {
	display: flex;
	height: auto;
}
.k-scrollable-demo-inline .k-box {
	width: 8rem;
	flex-shrink: 0;
	justify-content: center;
}
</style>
