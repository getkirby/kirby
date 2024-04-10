<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="$helper.isComponent()" script="component">
			<k-text>
				<p>Checks if the coponent is registered globally:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.isComponent(name): boolean</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="component" @input="component = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.isComponent(component)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: component */
export const component = {
	data() {
		return {
			component: "k-box"
		};
	}
};
/** @script-end */

export default {
	mixins: [component]
};
</script>
